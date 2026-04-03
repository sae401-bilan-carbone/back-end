<?php
namespace App\Security;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
  public function __construct(private JWTTokenManagerInterface $jwtManager) {}

  public function onAuthenticationSuccess(
    Request $req,
    TokenInterface $token
  ): Response {

    /** @var User $user */
    $user = $token->getUser();

    $jwt = $this->jwtManager->create($user);

    return new JsonResponse(
      [
        'token' => $jwt,
        'user' => [
          'id' => $user->getId(),
          'email' => $user->getEmail(),
          'roles' => $user->getRoles()
        ]
      ]
    );
  }
}