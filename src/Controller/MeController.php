<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class MeController extends AbstractController
{
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(
                ['error' => 'Unauthorized'], 
                401
            );
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/api/me', name: 'api_me_update', methods: ['PATCH'])]
    public function update(
        #[CurrentUser] ?User $user,
        Request $req,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse 
    {
        if (!$user) {
            return $this->json(
                ['error' => 'Unauthorized'], 
                Response::HTTP_UNAUTHORIZED
            );
        }

        $data = json_decode($req->getContent(), true);

        if (!$data) {
            return $this->json(
                ['error' => 'Invalid JSON'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (isset($data['name'])) $user->setName($data['name']);
        if (isset($data['surname'])) $user->setSurname($data['surname']);
        if (isset($data['profilePicture'])) $user->setProfilePicture($data['profilePicture']);
        if (isset($data['locale'])) $user->setLocale($data['locale']);

        $errors = $validator->validate($user);
        if (\count($errors) > 0) {
            return $this->json(
                ['error' => (string) $errors],
                Response::HTTP_BAD_REQUEST
            );
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(
            [
                'message' => 'Profile successfully updated',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName(),
                    'surname' => $user->getSurname(),
                    'profilePicture' => $user->getProfilePicture(),
                    'locale' => $user->getLocale()
                ]
            ]
        );
    }
}