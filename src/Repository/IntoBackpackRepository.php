<?php

namespace App\Repository;

use App\Entity\IntoBackpack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IntoBackpack|null find($id, $lockMode = null, $lockVersion = null)
 * @method IntoBackpack|null findOneBy(array $criteria, array $orderBy = null)
 * @method IntoBackpack[]    findAll()
 * @method IntoBackpack[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IntoBackpackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IntoBackpack::class);
    }

    // /**
    //  * @return IntoBackpack[] Returns an array of IntoBackpack objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?IntoBackpack
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
