<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class StatsController extends AbstractController
{
    #[Route('/api/activities/stats', name: 'api_activities_stats', methods: ['GET'])]
    public function getStats(
        #[CurrentUser] ?User $user,
        ActivityRepository $repository
    ): JsonResponse {

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $typeStats = $repository->getStatsByUser($user);
        $weeklyStats = $repository->getWeeklyStatsByUser($user);

        $totalGlobal = 0;
        $byCategory = [
            'shopping' => 0,
            'food' => 0,
            'journey' => 0
        ];

        foreach ($typeStats as $stat) {
            
            $type = $stat['type'];
            $sum = (float) $stat['total_co2'];
            
            if (isset($byCategory[$type])) {
                $byCategory[$type] = $sum;
            }
            $totalGlobal += $sum;
        }

        return $this->json([
            'total_emitted' => $totalGlobal,
            'by_category' => $byCategory,
            'by_week' => $weeklyStats
        ]);
    }
}