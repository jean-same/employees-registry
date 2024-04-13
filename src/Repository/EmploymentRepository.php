<?php

namespace App\Repository;

use App\Entity\Employment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employment>
 *
 * @method Employment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employment[]    findAll()
 * @method Employment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmploymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employment::class);
    }

//    /**
//     * @return Employment[] Returns an array of Employment objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Employment
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
