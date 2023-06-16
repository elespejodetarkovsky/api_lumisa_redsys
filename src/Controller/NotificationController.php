<?php

namespace App\Controller;

use App\Entity\ConfirmationPayLoad;
use App\Repository\NotificationUrlRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * este controller se encuentra fuera del grupo de seguridad!!
 * no colocar información sensible
 */

#[Route('/notifications')]
class NotificationController extends AbstractController
{

    public function __construct( private NotificationUrlRepository $notificationUrlRepository )
    {
    }

    #[Route('/threeDSMethodNotificacionURL/{threeDSMethodData}', name: 'app_redsys_notification_dsmethod')]
    public function notificacion3DSMethod(Request $request, string $threeDSMethodData): Response
    {
        $threeDSMethodDataJson              = json_decode(base64_decode($request->request->get('threeDSMethodData')), true);


        //realizo una comparación entre el id del parámetro y el del post pasado por la entidad
        //se podría realizar alguna comprobación más tambien
        if ( $threeDSMethodDataJson['threeDSServerTransID'] == $threeDSMethodData )
        {
            return $this->json(['3DSMethod' => 'OK'], Response::HTTP_OK);
        } else {
            return $this->json(['3DSMethod' => 'KAO'], Response::HTTP_OK);
        }

    }

    #[Route('/notificacionURL/{order}', name: 'app_redsys_notification')]
    public function notificacionURL(Request $request, string $order): Response
    {
        //si todo ha ido bien recibiré el parámetro cres para hacer la petición final
        $cres               = $request->request->get('cres');

        //el valor debería ser único
            $notificacionUrl    = $this->notificationUrlRepository->findOneBy(['orderId' => $order]);

            if ( !$notificacionUrl == null )
            {

                //existe en la base de datos el order
                $notificacionUrl->setCres( $cres );

                $emv3DS = array('threeDSInfo' => 'ChallengeResponse', 'protocolVersion' => $notificacionUrl->getProtocolVersion(),
                    'cres' =>  $cres);



                //TODO si todo sale bien se borrará luego
                $this->notificationUrlRepository->save($notificacionUrl, true);

                /*
                 * se devuelve el objeto que se usará para hacer la llamada final de confirmación
                 */

                $confirmation = new ConfirmationPayLoad();

                $confirmation->setAmount($notificacionUrl->getAmount())
                    ->setOrderId($notificacionUrl->getOrderId())
                    ->setIdOper($notificacionUrl->getIdOper())
                    ->setTransactionType('0')
                    ->setEmv3DS($emv3DS);

                $this->redirectToRoute('/api/confirmacion_autorizacion', $confirmation);
                //return $this->json($confirmation, Response::HTTP_OK);
//                return $this->render('confirmation/index.html.twig',
//                [
//                   'confirmationPayLoad' => $confirmation->toJson()
//                ]);

            } else {

                return $this->json(['error' => 'el order solicitado no se encuentra en la base de datos'], Response::HTTP_OK);

            }

    }
}