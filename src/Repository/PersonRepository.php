<?php

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Person>
 *
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    public function findAllPersonWithCurrentEmployment(): array
    {
        $query = $this->_em->createQuery('
            SELECT p, e
                FROM App\Entity\Person p
                LEFT JOIN p.employment e WITH e.isCurrent = true
                ORDER BY p.lastName, p.firstName
        ');

        return $query->getResult();
    }

    public function findPersonByCompanyName(string $companyName): array
    {
        $query = $this->_em->createQuery('
            SELECT p, e
            FROM App\Entity\Person p
            JOIN p.employment e
            WHERE e.companyName = :companyName
            ORDER BY p.lastName, p.firstName
        ');
        $query->setParameter('companyName', $companyName);

        return $query->getResult();
    }

    public function findEmploymentsByPersonAndDateRange(Person $person, \DateTimeImmutable $startDate, $endDate = null): array
    {
        $query = $this->_em->createQuery('
            SELECT e
            FROM App\Entity\Employment e
            JOIN e.people p
            WHERE p = :person
            AND e.startDate >= :startDate
            AND (e.endDate <= :endDate OR e.endDate IS NULL)
        ');
        $query->setParameter('person', $person);
        $query->setParameter('startDate', $startDate);
        $query->setParameter('endDate', $endDate);

        return $query->getResult();
    }

//    /**
//     * @return Person[] Returns an array of Person objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Person
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
