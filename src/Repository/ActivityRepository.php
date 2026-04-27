<?php

namespace App\Repository;

use App\Entity\Activity;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    public function getStatsByUser(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.type, SUM(a.co2) as total_co2')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->groupBy('a.type')
            ->getQuery()
            ->getArrayResult();
    }

    public function getWeeklyStatsByUser(User $user): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT
                CONCAT(YEAR(created_at), '-W', LPAD(WEEK(created_at, 3), 2, '0')) AS week,
                SUM(co2) AS total_co2
            FROM activity
            WHERE user_id = :userId
            GROUP BY week
            ORDER BY week ASC
        ";

        $stmt = $conn->executeQuery($sql, [
            'userId' => $user->getId()
        ]);

        return array_map(function ($row) {
            return [
                'week' => $row['week'],
                'total_co2' => (float) $row['total_co2'],
            ];
        }, $stmt->fetchAllAssociative());
    }

    public function getAverageWeeklyStats(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT
                week_label AS week,
                ROUND(AVG(user_weekly_co2), 2) AS average_co2
            FROM (
                SELECT
                    user_id,
                    CONCAT(YEAR(created_at), '-W', LPAD(WEEK(created_at, 3), 2, '0')) AS week_label,
                    SUM(co2) AS user_weekly_co2
                FROM activity
                GROUP BY user_id, week_label
            ) AS weekly_per_user
            GROUP BY week_label
            ORDER BY week_label ASC
        ";

        $stmt = $conn->executeQuery($sql);

        return array_map(function ($row) {
            return [
                'week' => $row['week'],
                'average_co2' => (float) $row['average_co2'],
            ];
        }, $stmt->fetchAllAssociative());
    }

    public function getAverageTotalEmitted(): float
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT ROUND(AVG(user_total), 2) AS avg_total
            FROM (
                SELECT user_id, SUM(co2) AS user_total
                FROM activity
                GROUP BY user_id
            ) AS user_totals
        ";

        $stmt = $conn->executeQuery($sql);
        $result = $stmt->fetchAssociative();

        return (float) ($result['avg_total'] ?? 0);
    }
}