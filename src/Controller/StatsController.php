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
    #[Route('/activities/stats', name: 'api_activities_stats', methods: ['GET'])]
    public function getStats(
        #[CurrentUser] ?User $user,
        ActivityRepository $repository
    ): JsonResponse {

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $typeStats   = $repository->getStatsByUser($user);
        $weeklyStats = $repository->getWeeklyStatsByUser($user);
        $avgWeekly   = $repository->getAverageWeeklyStats();
        $avgTotal    = $repository->getAverageTotalEmitted();

        $totalGlobal = 0;
        $byCategory  = ['shopping' => 0, 'food' => 0, 'journey' => 0];

        foreach ($typeStats as $stat) {
            $type = $stat['type'];
            $sum  = (float) $stat['total_co2'];
            if (isset($byCategory[$type])) {
                $byCategory[$type] = $sum;
            }
            $totalGlobal += $sum;
        }

        $avgByWeekMap = [];
        foreach ($avgWeekly as $row) {
            $avgByWeekMap[$row['week']] = $row['average_co2'];
        }

        $mergedWeeks = array_map(function ($week) use ($avgByWeekMap) {
            return [
                'week'        => $week['week'],
                'total_co2'   => $week['total_co2'],
                'average_co2' => $avgByWeekMap[$week['week']] ?? null,
            ];
        }, $weeklyStats);

        return $this->json([
            'total_emitted'         => $totalGlobal,
            'average_total_emitted' => $avgTotal,
            'by_category'           => $byCategory,
            'by_week'               => $mergedWeeks,
        ]);
    }
}