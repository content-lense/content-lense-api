<?php

namespace App\Repository;

use App\Entity\AnalysisMicroservice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AnalysisMicroservice>
 *
 * @method AnalysisMicroservice|null find($id, $lockMode = null, $lockVersion = null)
 * @method AnalysisMicroservice|null findOneBy(array $criteria, array $orderBy = null)
 * @method AnalysisMicroservice[]    findAll()
 * @method AnalysisMicroservice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnalysisMicroserviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnalysisMicroservice::class);
    }

    public function save(AnalysisMicroservice $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AnalysisMicroservice $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return AnalysisMicroservice[] Returns an array of AnalysisMicroservice objects
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

//    public function findOneBySomeField($value): ?AnalysisMicroservice
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
