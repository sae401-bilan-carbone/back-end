<?php

namespace App\Repository;

use App\Entity\Activity;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activity>
 */
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
            ->getResult();
    }

    public function getWeeklyStatsByUser(User $user): array
    {
        $connexion = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT 
                DATE_FORMAT(created_at, "%Y-W%u") as week, 
                SUM(co2) as total_co2 
            FROM activity 
            WHERE user_id = :userId 
            GROUP BY week 
            ORDER BY week ASC
        ';

        return $connexion->executeQuery($sql, ['userId' => $user->getId()])->fetchAllAssociative();
    }

//    /**
//     * @return Activity[] Returns an array of Activity objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Activity
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
