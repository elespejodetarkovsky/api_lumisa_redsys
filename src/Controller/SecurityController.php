<?php

namespace App\Controller;

use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SecurityController extends AbstractController
{

    public function __construct()
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

        return $this->json([
            'user'      => $user->getId(),
            'token'     => $user->getApiTokens()->first()->getToken(),
            'roles'     => $user->getRoles()
        ]);
    }

    #[Route('logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }

    private function tratarToken(Collection $tokens): string|null
    {

        //verificaré si alguno de los token que posee es válido (debería tener uno)
        foreach ($tokens as $token)
        {
            if ($token->isValid())
            {
                return $token;
            }
        }

        //si estoy aquí deberé crear un nuevo token
        $token = new ApiToken();
        
    }
}