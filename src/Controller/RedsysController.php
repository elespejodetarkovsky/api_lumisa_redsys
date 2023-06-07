<?php

namespace App\Controller;

use App\Entity\AutorizationPayLoad;
use App\Entity\Challenge;
use App\Entity\Emv3DS;
use App\Entity\NotificationUrl;
use App\Entity\Transaction;
use App\Model\RedsysAPI;
use App\Repository\DsResponseRepository;
use App\Repository\NotificationUrlRepository;
use App\Repository\ResponseErrorRepository;
use App\Utils\RESTConstants;
use App\Utils\Util;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
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
                                private NotificationUrlRepository $notificationUrlRepository,
                                private string $token = '')
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
    #[Route('/iniciarPeticion/{token}/{order}/{amount}', name: 'app_redsys_init')]
    public function initPeticion(string $token, string $order, string $amount): Response
    {

        $this->token                = $token;
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
        if( $protocol == '1.0.2' )
        {
            return RESTConstants::$REQUEST_MERCHANT_EMV3DS_PROTOCOLVERSION_102;
        } elseif ( $protocol == '2.1.0')
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

        $petition = '';

        //++++ se usarán en caso de ser challenge
        $this->token                = $autorizationPayLoad->getToken();
        $this->amount               = $autorizationPayLoad->getAmount();
        $this->order                = $autorizationPayLoad->getOrder();
         //++++

        //Realizo el cuerpo de peticion en función de lo que solicite el front

        $petition = $this->autorizationRest($autorizationPayLoad->getOrder(),
            '0',
            $autorizationPayLoad->getAmount(),
            $autorizationPayLoad->getToken(),
            Util::makeMerchantEmv3ds(!($autorizationPayLoad->getDsMethodUrl() == null), $autorizationPayLoad->getDsServerTransId(),
                $this->order, $autorizationPayLoad->getProtocolVersion())
        );


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
            $dsEmv3DS           = $this->redsysAPI->getParameter('Ds_EMV3DS') ?? null; //no existirá en caso de frictionless
            $order              = $this->redsysAPI->getParameter('Ds_Order');

            //si es null Ds_Response y recibo la solicitud de challenge deberé gestionarlo
            //devolveré un objeto challenge

            //realizo el challenge si no existe será frictionless y será el final de la operación
            if ( $dsEmv3DS != null )
            {
                if( $dsEmv3DS['threeDSInfo'] == 'ChallengeRequest' && $codigoRespuesta == null )
                {
                    $challenge = new Challenge();

                    $challenge->setAmount($amount)
                        ->setCurrency($this->getParameter('app.currency'))
                        ->setOrder($order)
                        ->setMerchantCode($this->getParameter('app.fuc'))
                        ->setTerminal($this->getParameter('app.terminal'))
                        ->setOutDsEmv3DS($dsEmv3DS);

                    //guardaré antes de enviar el challenge los datos que requeriré
                    //para enviar la confirmación final con el cres obtenido del banco
                    $notificacionUrl = new NotificationUrl();
                    $notificacionUrl->setAmount($amount)
                        ->setOrderId($order)
                        ->setIdOper($this->token)
                        ->setProtocolVersion($dsEmv3DS['protocolVersion']);

                    //luegcuando reciba el cres lo agregaré y haré la redireccion de exito si fue bien

                    $this->notificationUrlRepository->save($notificacionUrl, true);


                    return $challenge;

                }
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
                    ->setTransactionType(RedsysAPI::AUTHORIZATION)
                    ->setAuthorized(str_contains( $codigoRespuesta, '00'));


                //la respuesta puede contener error por tanto se evalua antes de cargar el string
                $transaction->setRespuesta( $this->dsResponseRepository->findOneBy(['codigo' => $codigoRespuesta]));

                $signatureCalculada = $this->redsysAPI->createMerchantSignatureNotif($this->getParameter('app.clave.comercio'), $params);

                if ($signatureCalculada === $signatureRecibida) {

                    //si estoy aquí ya puedo guardar en la base de datos y el order no podrá repetirse
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();

                    return $transaction;

                } else {
                    return "{'error':'firma no valida'}";
                }

            } elseif ( $codigoRespuesta == '0195' )
            {

                //TODO Redirigir a PSD2?
                return '{"'.$codigoRespuesta.'":"'.$this->dsResponseRepository->findOneBy(['codigo' => $codigoRespuesta]).'"}';

            } else {

                //OTRO ERROR, DE AUTENTICACION, RECHAZO POR CLIENTE ETC
                return '{"'.$codigoRespuesta.'":"'.$this->dsResponseRepository->findOneBy(['codigo' => $codigoRespuesta]).'"}';

            }

        }
    }
    #[Route('/challenge/{acsURL}/{creq}/{MD}/{termUrl}', name: 'app_redsys_challenge')]
    public function challenge(string $acsURL, string $creq, ?string $MD = null, ?string $termUrl = null): Response
    {
        //Reenvío en el formulario el challenge

        $acsURL = urldecode(base64_decode($acsURL));

        $challenge = array('protocol' => $acsURL,
                    'creq' => $creq,
                    'MD' => $MD,
                    'termUrl' => $termUrl);

        return $this->json( $challenge, Response::HTTP_OK );

/*        return $this->render('challenge/index.html.twig', [
            'protocol' => 2,
            'acsURL' => $acsURL,
            'creq' => $creq,
            'MD' => $MD,
            'termUrl' => $termUrl
        ]);*/

    }

    #[Route('/notificacionURL/{order}', name: 'app_redsys_notification')]
    public function notificacionURL(Request $request, string $order): Response
    {
        //si todo ha ido bien recibiré el parámetro cres para hacer la petición final
        $cres               = $request->request->get('cres');

        //el valor debería ser único
        $notificacionUrl    = $this->notificationUrlRepository->findOneBy(['orderId' => $order]);

        $notificacionUrl->setCres( $cres );

        $emv3DS = array('threeDSInfo' => 'ChallengeResponse', 'protocolVersion' => $notificacionUrl->getProtocolVersion(),
            'cres' => $cres);


        $petition = $this->autorizationRest($notificacionUrl->getOrderId(),'0', $notificacionUrl->getAmount(),
                            $notificacionUrl->getIdOper(), $emv3DS);

        //En caso de recibir el objeto y por tanto con la transaccion terminada
        //se borra de la base de datos y se reenvia a la pagina de notificación del front

        $transaction = $this->fetchRedSys(json_encode($petition));

        //armo la salida
        //$authorized = $transaction instanceof Transaction;

        return $this->json($transaction, Response::HTTP_OK);


/*        return $this->render('notificacion/index.html.twig', [
           'orden'          => $order,
           'authorized'     => $authorized,
           'error'          => $authorized ? 'none' : json_decode( $transaction, true)
        ]);*/

    }

}
