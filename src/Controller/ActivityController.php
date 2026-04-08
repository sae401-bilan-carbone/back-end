<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\User;
use App\Service\CarbonCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ActivityController extends AbstractController
{
    #[Route('/api/activities', name: 'api_activity_create', methods: ['POST'])]
    public function create(
        Request $request,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em,
        CarbonCalculatorService $calculator
    ): JsonResponse {

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['type'], $data['data'])) {
            return $this->json(
                ['error' => 'Format invalide. Les champs type et data sont obligatoires.'], 
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $activity = new Activity();
            $activity->setUser($user);
            $activity->setType($data['type']);
            $activity->setData($data['data']);
            $activity->setCreatedAt(new \DateTime());

            $co2Value = $calculator->calculate($data['type'], $data['data']);
            $activity->setCo2((float) $co2Value);

            $em->persist($activity);
            $em->flush();

            return $this->json(
                [
                    'status' => 'success',
                    'message' => 'Activité enregistrée avec succès',
                    'id' => $activity->getId(),
                    'calculated_co2' => $activity->getCo2(),
                    'type' => $activity->getType()
                ], 
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            return $this->json(
                [
                    'error' => 'Une erreur est survenue lors du calcul.',
                    'details' => $e->getMessage()
                ], 
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/api/activities', name: 'api_activity_list', methods: ['GET'])]
    public function list(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $activities = [];
        foreach ($user->getActivities() as $activity) {
            $activities[] = [
                'id' => $activity->getId(),
                'type' => $activity->getType(),
                'data' => $activity->getData(),
                'co2' => $activity->getCo2(),
                'createdAt' => $activity->getCreatedAt()?->format('c'),
            ];
        }

        return $this->json($activities);
    }
}