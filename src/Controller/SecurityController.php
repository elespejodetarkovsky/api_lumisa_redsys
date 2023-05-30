<?php

namespace App\Controller;

use App\Entity\ApiToken;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SecurityController extends AbstractController
{

    public function __construct(private UserRepository $userRepository)
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
            $this->tratarUserByToken($user)
        ]);
    }

    #[Route('logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }

    private function tratarUserByToken(User $user): array
    {

        //verificaré si alguno de los token que posee es válido (debería tener uno)
        foreach ($user->getApiTokens() as $token)
        {
            if ($token->isValid())
            {
                return ['user' => $user->getId(), 'token' => $token->getToken(), 'roles' => $user->getRoles()];
            }
        }

        //si estoy aquí deberé crear un nuevo token
        $user->addApiToken(new ApiToken());

        $this->userRepository->save($user);

        return ['user' => $user->getId(), 'token' => $token->getToken(), 'roles' => $user->getRoles()];


    }
}