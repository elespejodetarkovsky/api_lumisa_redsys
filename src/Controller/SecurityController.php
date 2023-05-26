<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SecurityController extends AbstractController
{

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(#[CurrentUser] User $user = null): Response
    {

        if (!$user)
        {
            return $this->json([
                'error' => 'Login Invalido'
            ], 401);
        }

        return $this->json([
            'user'      => $user->getId(),
            'token'     => $user->getApiTokens()->first()->getToken()
        ]);
    }

    #[Route('logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }
}