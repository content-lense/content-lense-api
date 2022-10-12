<?php

namespace App\Repository;

use App\Entity\ArticleAnalysisResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleAnalysisResult>
 *
 * @method ArticleAnalysisResult|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArticleAnalysisResult|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArticleAnalysisResult[]    findAll()
 * @method ArticleAnalysisResult[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleAnalysisResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleAnalysisResult::class);
    }

    public function save(ArticleAnalysisResult $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ArticleAnalysisResult $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ArticleAnalysisResult[] Returns an array of ArticleAnalysisResult objects
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

//    public function findOneBySomeField($value): ?ArticleAnalysisResult
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
