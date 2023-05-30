<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Model\RedsysAPI;
use App\Repository\DsResponseRepository;
use App\Repository\ResponseErrorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
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
    private const URL_TEST_TRATA             = "https://sis-t.redsys.es:25443/sis/rest/trataPeticionREST";
    private const URL_TEST_INIT        = "https://sis-t.redsys.es:25443/sis/rest/iniciaPeticionREST";
    private const CLAVE_COMERCIO         = "sq7HjrUOBfKmC576ILgskD5srU870gJ7";

    private RedsysAPI $redsysAPI;

    public function __construct(private HttpClientInterface $client,
                                private EntityManagerInterface $entityManager,
                                private ResponseErrorRepository $errorRepository,
                                private DsResponseRepository $dsResponseRepository,
                                private RouterInterface $router,
                                private string $token = '',
                                private string $idOrderMedusa = '')
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
//    #[Route('/iniciarPeticion/{token}/{order}/{amount}/{idOrderMedusa}', name: 'app_redsys_init')]
//    public function initPeticion(string $token, string $order, string $amount, string $idOrderMedusa): Response
//    {
//
//        $this->token                = $token;
//        $this->idOrderMedusa        = $idOrderMedusa;
//
//
//        // Valores de entrada que no hemos cmbiado para ningun ejemplo
//        $fuc            = "999008881"; //TODO en la configuracion
//        $terminal       = "1"; //TODO en la configuracion
//        $moneda         = "978"; //TODO
//        $trans          = RedsysAPI::AUTHORIZATION;
//        $amount         = "20095";
//
//        // Se Rellenan los campos
//        $this->redsysAPI->setParameter("DS_MERCHANT_AMOUNT",$amount);
//        $this->redsysAPI->setParameter("DS_MERCHANT_ORDER",$order);
//        $this->redsysAPI->setParameter("DS_MERCHANT_MERCHANTCODE",$fuc);
//        $this->redsysAPI->setParameter("DS_MERCHANT_CURRENCY",$moneda);
//        $this->redsysAPI->setParameter("DS_MERCHANT_EMV3DS",'{"threeDSInfo": "CardData"}');
//        $this->redsysAPI->setParameter("DS_MERCHANT_TRANSACTIONTYPE",$trans);
//        $this->redsysAPI->setParameter("DS_MERCHANT_TERMINAL",$terminal);
//        $this->redsysAPI->setParameter("DS_MERCHANT_IDOPER", $token);
//
//        $dsSignatureVersion     = 'HMAC_SHA256_V1';
//
//        //diversificación de clave 3DES
//        //OPENSSL_RAW_DATA=1
//
//        $params = $this->redsysAPI->createMerchantParameters();
//        $signature = $this->redsysAPI->createMerchantSignature(self::CLAVE_COMERCIO);
//
//        $petition['Ds_SignatureVersion']        = $dsSignatureVersion;
//        $petition["Ds_MerchantParameters"]      = $params;
//        $petition["Ds_Signature"]               = $signature;
//
//        return $this->json($this->fetchRedSys(json_encode($petition)), Response::HTTP_OK);
//
//    }

    #[Route('/autorizacion/{token}/{order}/{amount}/{idOrderMedusa}', name: 'app_redsys_send_api')]
    public function sendAutorization(string $token, string $order, string $amount, string $idOrderMedusa): Response
    {

        $this->token                = $token;
        $this->idOrderMedusa        = $idOrderMedusa;


        // Valores de entrada que no hemos cmbiado para ningun ejemplo
        $trans          = RedsysAPI::AUTHORIZATION;

        // Se Rellenan los campos
        $this->redsysAPI->setParameter("DS_MERCHANT_AMOUNT",$amount);
        $this->redsysAPI->setParameter("DS_MERCHANT_ORDER",$order);
        $this->redsysAPI->setParameter("DS_MERCHANT_MERCHANTCODE",$_ENV['FUC']);
        $this->redsysAPI->setParameter("DS_MERCHANT_CURRENCY", $_ENV['CURRENCY']);
        $this->redsysAPI->setParameter("DS_MERCHANT_TRANSACTIONTYPE",$trans);
        $this->redsysAPI->setParameter("DS_MERCHANT_TERMINAL",$_ENV['TERMINAL']);
        $this->redsysAPI->setParameter("DS_MERCHANT_IDOPER", $token);
        //$this->redsysAPI->setParameter("DS_MERCHANT_DIRECTPAYMENT", "true");

        $dsSignatureVersion     = 'HMAC_SHA256_V1';

        //diversificación de clave 3DES
        //OPENSSL_RAW_DATA=1

        $params = $this->redsysAPI->createMerchantParameters();
        $signature = $this->redsysAPI->createMerchantSignature(self::CLAVE_COMERCIO);

        $petition['Ds_SignatureVersion']        = $dsSignatureVersion;
        $petition["Ds_MerchantParameters"]      = $params;
        $petition["Ds_Signature"]               = $signature;

        return $this->json($this->fetchRedSys(json_encode($petition)), Response::HTTP_OK);
    }

    private function fetchRedSys($body): Transaction|string
    {

        $response = $this->client->request(
            'POST',
            self::URL_TEST_TRATA,
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

            return $this->responseTransaction($response->getContent());

        } else {

            return '{"error":'. $response->getInfo() .'}';

        }

    }


    /**
     * esta funcion se ejecutará en caso de que la llamada sea exitosa
     * y devolverá error (si la tarjeta tuvo algún problema)
     * o los datos correspondientes al response exitoso de la transacción
     *
     * @param string $responseJson
     * @return string
     */
    private function responseTransaction(string $responseJson): Transaction|string
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
                    ->setIdMedusa($this->idOrderMedusa)
                    ->setTransactionType(RedsysAPI::AUTHORIZATION)
                    ->setAuthorized(str_contains( $codigoRespuesta, '00'));


                //la respuesta puede contener error por tanto se evalua antes de cargar el string
                $transaction->setRespuesta( $this->dsResponseRepository->findOneBy(['codigo' => $codigoRespuesta]));

                $signatureCalculada = $this->redsysAPI->createMerchantSignatureNotif(self::CLAVE_COMERCIO, $params);

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
