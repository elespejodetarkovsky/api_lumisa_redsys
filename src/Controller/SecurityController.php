<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ApiTokenRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SecurityController extends AbstractController
{

    public function __construct(private ApiTokenRepository $apiTokenRepository, private UserRepository $userRepository)
    {
    }

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(#[CurrentUser] User $user = null): Response
    {

        if (!$user)
        {
            return $this->json([
                'error' => 'Login Invalido'
            ], 401);
        }

        /*******************************************************************
         *  se creará un token válido para pasarlo al usuario y tendrá *****
         *  una validez de 2 horas borro los anteriores que pueda tener ****
         *******************************************************************/

        //quitará los tokens caducados que pueda tener el usuario
        foreach ( $user->getInvalidTokenStrings() as $token )
        {
            $this->apiTokenRepository->remove( $token, true);
        }

        $newToken          = $user->setNewTokenInUser();

        //almaceno el nuevo token
        $this->apiTokenRepository->save($newToken, true);

        return $this->json([
            'user'      => $user->getId(),
            'token'     => $newToken->getToken(),
            'roles'     => $user->getRoles()
        ]);
    }

    #[Route('logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }


}