<?php

namespace App\Controller;

use App\Entity\AutorizationPayLoad;
use App\Entity\Challenge;
use App\Entity\Emv3DS;
use App\Entity\Transaction;
use App\Model\RedsysAPI;
use App\Repository\DsResponseRepository;
use App\Repository\ResponseErrorRepository;
use App\Utils\RESTConstants;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function Symfony\Component\String\u;

/**
 * Access control en firewall config
 */
#[Route('/api')]
class RedsysController extends AbstractController
{

    private RedsysAPI $redsysAPI;
    private string $amount;
    private string $order;

    public function __construct(private HttpClientInterface $client,
                                private EntityManagerInterface $entityManager,
                                private ResponseErrorRepository $errorRepository,
                                private DsResponseRepository $dsResponseRepository,
                                private RouterInterface $router,
                                private string $token = '',
                                private string $idCarrito = '')
    {
        $this->redsysAPI = new RedsysAPI();
    }


    #[Route('/', name: 'index_api')]
    public function index(): Response
    {

        $allRoutes          = $this->router->getRouteCollection()->all();

        $routesApi          = array();


        foreach ($allRoutes as $name => $route) {

            if(u($route->getPath())->containsAny('api'))
            {
                $routesApi[] = $route->getPath();
            }

        }

        return $this->render('api/index.html.twig',
        [
            'routes' => $routesApi
        ]);

    }

    #[Route('/test', name: 'test_api')]
    public function test(): Response
    {

        return $this->render('api/test.html.twig');

    }

    #[Route('/get_peticion_by_id_medusa/{id}', name: 'get_id_medusa_api')]
    public function getMedusaIdObject(string $id): Response
    {

        //realizo la busqueda del objeto
        $transaction = $this->entityManager->getRepository(Transaction::class)->findOneByIdMedusa($id);

        return $this->json($transaction, Response::HTTP_OK);

    }

    #[Route('/get_peticion_by_token/{token}', name: 'get_by_token_api')]
    public function getTransactionByToken(string $token): Response
    {

        //realizo la busqueda del objeto
        $transaction = $this->entityManager->getRepository(Transaction::class)->findOneByIdToken($token);

        return $this->json($transaction, Response::HTTP_OK);

    }


    //TODO se usará en caso de iniciar la petición con autenticacion
    #[Route('/iniciarPeticion/{token}/{order}/{amount}/{idCarrito}', name: 'app_redsys_init')]
    public function initPeticion(string $token, string $order, string $amount, string $idCarrito): Response
    {

        $this->token                = $token;
        $this->idCarrito            = $idCarrito;
        $this->amount               = $amount;
        $this->order                = $order;


        // Se Rellenan los campos
        $this->redsysAPI->setParameter("DS_MERCHANT_AMOUNT",$amount);
        $this->redsysAPI->setParameter("DS_MERCHANT_ORDER",$order);
        $this->redsysAPI->setParameter("DS_MERCHANT_MERCHANTCODE", $this->getParameter('app.fuc'));
        $this->redsysAPI->setParameter("DS_MERCHANT_CURRENCY", $this->getParameter('app.currency'));
        $this->redsysAPI->setParameter("DS_MERCHANT_TRANSACTIONTYPE",RedsysAPI::AUTHORIZATION);
        $this->redsysAPI->setParameter("DS_MERCHANT_EMV3DS",'{"threeDSInfo": "CardData"}');
        $this->redsysAPI->setParameter("DS_MERCHANT_TERMINAL",$this->getParameter('app.terminal'));
        $this->redsysAPI->setParameter("DS_MERCHANT_IDOPER", $token);

        $dsSignatureVersion     = 'HMAC_SHA256_V1';

        //diversificación de clave 3DES
        //OPENSSL_RAW_DATA=1

        $params = $this->redsysAPI->createMerchantParameters();
        $signature = $this->redsysAPI->createMerchantSignature($this->getParameter('app.clave.comercio'));

        $petition['Ds_SignatureVersion']        = $dsSignatureVersion;
        $petition["Ds_MerchantParameters"]      = $params;
        $petition["Ds_Signature"]               = $signature;

        return $this->json($this->fetchRedSys(json_encode($petition), true), Response::HTTP_OK);

    }

    private function getProtocoloVersion (string $protocol): string
    {
        if( $protocol == '1.0' )
        {
            return RESTConstants::$REQUEST_MERCHANT_EMV3DS_PROTOCOLVERSION_102;
        } elseif ( $protocol == '2.1')
        {
            return RESTConstants::$REQUEST_MERCHANT_EMV3DS_PROTOCOLVERSION_210;
        } else {
            return RESTConstants::$REQUEST_MERCHANT_EMV3DS_PROTOCOLVERSION_220;
        }

    }

    #[Route('/autorizacion', name: 'app_redsys_send_api', methods: 'post')]
    public function sendAutorization(#[MapRequestPayload] AutorizationPayLoad $autorizationPayLoad): Response
    {

        //TODO evaluar pago inseguro
        /*** protocolVersion 2 corresponde a 2.1.0 o 2.2.0 ***/
        /*+* si recibo null en la url paso threeSDCompInd = N ***/
        /*** dependiendo de si se recibe response o no se continua ***/

        dd($autorizationPayLoad);

        $petition = '';

        //Realizo el cuerpo de peticion en función de lo que solicite el front

            if( $autorizationPayLoad->getDsMethodUrl() == null )
            {

                $petition = $this->autorizationRest($autorizationPayLoad->getOrder(),
                    '0',
                    $autorizationPayLoad->getAmount(),
                    $autorizationPayLoad->getToken(),
                    array(RESTConstants::$REQUEST_MERCHANT_EMV3DS_THREEDSINFO => RESTConstants::$REQUEST_MERCHANT_EMV3DS_AUTHENTICACIONDATA,
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_PROTOCOLVERSION => $this->getProtocoloVersion($autorizationPayLoad->getProtocolVersion() ?? RESTConstants::$REQUEST_MERCHANT_EMV3DS_PROTOCOLVERSION_210),
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_ACCEPT_HEADER => RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_ACCEPT_HEADER_VALUE,
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_USER_AGENT => RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_USER_AGENT_VALUE,
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_JAVA_ENABLE => 'false',
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_LANGUAGE => 'ES-es',
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_COLORDEPTH => '24',
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_SCREEN_HEIGHT => '1250',
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_SCREEN_WIDTH => '1320',
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_TZ => '52',
                        "threeDSServerTransID" => $autorizationPayLoad->getDsServerTransId(),
                        "threeDSCompInd" => "N")
                );

            } else {

                $petition = $this->autorizationRest($autorizationPayLoad->getOrder(),
                    '0',
                    $autorizationPayLoad->getAmount(),
                    $autorizationPayLoad->getToken(),
                    array(RESTConstants::$REQUEST_MERCHANT_EMV3DS_THREEDSINFO => RESTConstants::$REQUEST_MERCHANT_EMV3DS_AUTHENTICACIONDATA,
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_PROTOCOLVERSION => $this->getProtocoloVersion($protocolVersion ?? RESTConstants::$REQUEST_MERCHANT_EMV3DS_PROTOCOLVERSION_210),
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_ACCEPT_HEADER => RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_ACCEPT_HEADER_VALUE,
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_USER_AGENT => RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_USER_AGENT_VALUE,
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_JAVA_ENABLE => 'false',
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_LANGUAGE => 'ES-es',
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_COLORDEPTH => '24',
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_SCREEN_HEIGHT => '1250',
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_SCREEN_WIDTH => '1320',
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_TZ => '52',
                        "threeDSServerTransID" => $autorizationPayLoad->getDsServerTransId(),
                        RESTConstants::$REQUEST_MERCHANT_EMV3DS_NOTIFICATIONURL => $autorizationPayLoad->getDsMethodUrl(),
                        "threeDSCompInd" => "Y")
                );


            }


        return $this->json($this->fetchRedSys(json_encode($petition)), Response::HTTP_OK);

    }

    private function fetchRedSys($body, bool $init = false): Transaction|Emv3DS|Challenge|string
    {

        $response = $this->client->request(
            'POST',
            $init ? $this->getParameter('app.url.inicia') : $this->getParameter('app.url.trata'),
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Content-length' => strlen($body)
                ],
                'body' => $body
            ]
        );


        if ($response->getStatusCode() == 200)
        {

            return $init ? $this->responseInit($response->getContent()) : $this->responseTransaction($response->getContent());

        } else {

            return '{"error":'. $response->getContent() .'}';

        }

    }

    /**
     * Respuesta de iniciar Peticion EMV3DS
     * @param string $responseJson
     * @return \App\Entity\ResponseError
     */
    private function responseInit(string $responseJson)
    {

        $arrayResponde = json_decode($responseJson, true);

        if (array_key_exists('errorCode', $arrayResponde))
        {

            return $this->errorRepository->findOneBy(['sisoxxx' => $arrayResponde['errorCode']]);

        } else {

            $version = $arrayResponde["Ds_SignatureVersion"];
            $params = $arrayResponde["Ds_MerchantParameters"];
            $signatureRecibida = $arrayResponde["Ds_Signature"];

            //obtengo los datos de forma separada
            $decode             = $this->redsysAPI->decodeMerchantParameters($params);

            $emv3ds             = $this->redsysAPI->getParameter('Ds_EMV3DS');
            $cardPSD2           = $this->redsysAPI->getParameter('Ds_Card_PSD2');

            //recibirá en la respuesta el parámetro ds_emv3ds que será serializado para la respuesta
            //y decidir en función de la evaluación del riesgo y limites de la entidad bancaria del cliente
            //TODO EVALUAR FIRMA

            $objEmv3ds = new Emv3DS();

            $threeDSMethodURL = $emv3ds['threeDSMethodURL'] ?? null;

            $objEmv3ds->setProtocolVersion($emv3ds['protocolVersion'])
                ->setThreeDSInfo($emv3ds['threeDSInfo'])
                ->setThreeDServerTransID($emv3ds['threeDSServerTransID'])
                ->setThreeDSMethodURL($threeDSMethodURL)
                ->setCardPSD2($cardPSD2);

            return $objEmv3ds;


        }

    }

    /**
     * Una vez evaluado el riesgo y la respuesta se hará la autorización final con o sin challenge
     * @param string $order
     * @param string $transactionType
     * @param string $amount
     * @param string $idOper
     * @param array $emv3ds
     * @return void
     */
    private function autorizationRest(string $order, string $transactionType,
                                      string $amount, string $idOper, array $emv3ds): array
    {

        //limpio los parametros
        $this->redsysAPI = new RedsysAPI();

        $this->redsysAPI->setParameter("DS_MERCHANT_ORDER",$order);
        $this->redsysAPI->setParameter("DS_MERCHANT_MERCHANTCODE", $this->getParameter('app.fuc'));
        $this->redsysAPI->setParameter("DS_MERCHANT_TERMINAL",$this->getParameter('app.terminal'));
        $this->redsysAPI->setParameter("DS_MERCHANT_CURRENCY", $this->getParameter('app.currency'));
        $this->redsysAPI->setParameter("DS_MERCHANT_TRANSACTIONTYPE", $transactionType);
        $this->redsysAPI->setParameter("DS_MERCHANT_AMOUNT",$amount);
        $this->redsysAPI->setParameter("DS_MERCHANT_IDOPER", $idOper);
        $this->redsysAPI->setParameter("DS_MERCHANT_EMV3DS", json_encode($emv3ds));


        $dsSignatureVersion     = 'HMAC_SHA256_V1';

        //diversificación de clave 3DES
        //OPENSSL_RAW_DATA=1

        $params = $this->redsysAPI->createMerchantParameters();
        $signature = $this->redsysAPI->createMerchantSignature($this->getParameter('app.clave.comercio'));

        $petition['Ds_SignatureVersion']        = $dsSignatureVersion;
        $petition["Ds_MerchantParameters"]      = $params;
        $petition["Ds_Signature"]               = $signature;

        //dd($this->redsysAPI->decodeMerchantParameters($params));
        //dd(json_encode($petition));
        //return $this->json($this->fetchRedSys(json_encode($petition)), Response::HTTP_OK);

        return $petition;

    }


    /**
     * esta funcion se ejecutará en caso de que la llamada sea exitosa
     * y devolverá error (si la tarjeta tuvo algún problema)
     * o los datos correspondientes al response exitoso de la transacción
     *
     * @param string $responseJson
     * @return string
     */
    private function responseTransaction(string $responseJson): Transaction|Challenge|string
    {
        $arrayResponde = json_decode($responseJson, true);

        if (array_key_exists('errorCode', $arrayResponde))
        {

            return $this->errorRepository->findOneBy(['sisoxxx' => $arrayResponde['errorCode']]);

        } else {

            $version = $arrayResponde["Ds_SignatureVersion"];
            $params = $arrayResponde["Ds_MerchantParameters"];
            $signatureRecibida = $arrayResponde["Ds_Signature"];

            //obtengo los datos de forma separada
            $decode             = $this->redsysAPI->decodeMerchantParameters($params);

            $codigoRespuesta    = $this->redsysAPI->getParameter('Ds_Response');
            $cardNumber         = $this->redsysAPI->getParameter('Ds_CardNumber');
            $amount             = $this->redsysAPI->getParameter('Ds_Amount');
            $currency           = $this->redsysAPI->getParameter('Ds_Currency');
            $dsEmv3DS           = $this->redsysAPI->getParameter('Ds_EMV3DS');
            $order              = $this->redsysAPI->getParameter('Ds_Order');

            //si es null Ds_Response necesitará hacer un challenge
            //y se buscará acsURL y creq para devolverlos por json
            if( $codigoRespuesta == null )
            {
                //podría ser un challenge armo la entidad con la respuesta
                $challenge = new Challenge();

                $challenge->setAmount($amount)
                    ->setCurrency($this->getParameter('app.currency'))
                    ->setOrder($order)
                    ->setMerchantCode($this->getParameter('app.fuc'))
                    ->setTerminal($this->getParameter('app.terminal'))
                    ->setOutDsEmv3DS($dsEmv3DS);

                return $challenge;
            }

            //será 0000 a 0099
            if ( str_starts_with( $codigoRespuesta, '00' ))
            {

                //la operacion ha sido correcta y se realiza el pago
                //instancio la clase...
                $transaction = new Transaction();
                $transaction->setIdOrder($this->redsysAPI->getParameter('Ds_Order'))
                    ->setCantidad($amount)
                    ->setEstado($codigoRespuesta)
                    ->setCountry($this->redsysAPI->getParameter('Ds_Card_Country'))
                    ->setToken($this->token)
                    ->setCardNumber($cardNumber)
                    ->setIdMedusa($this->idCarrito)
                    ->setTransactionType(RedsysAPI::AUTHORIZATION)
                    ->setAuthorized(str_contains( $codigoRespuesta, '00'));


                //la respuesta puede contener error por tanto se evalua antes de cargar el string
                $transaction->setRespuesta( $this->dsResponseRepository->findOneBy(['codigo' => $codigoRespuesta]));

                $signatureCalculada = $this->redsysAPI->createMerchantSignatureNotif($this->getParameter('app.clave.comercio'), $params);

                if ($signatureCalculada === $signatureRecibida) {

                    //si estoy aquí ya puedo guardar en la base de datos
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();

                    return $transaction;

                } else {
                    return "{'error':'firma no valida'}";
                }

            } elseif ( $codigoRespuesta == '0195' )
            {

                //TODO Redirigir a PSD2
                return $codigoRespuesta;

            } else {

                //TODO ERROR EN LA OPERACION
                return $codigoRespuesta;

            }

        }
    }


}
