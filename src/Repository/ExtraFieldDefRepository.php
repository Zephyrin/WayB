<?php

namespace App\Repository;

use App\Entity\ExtraFieldDef;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ExtraFieldDef|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExtraFieldDef|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExtraFieldDef[]    findAll()
 * @method ExtraFieldDef[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExtraFieldDefRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ExtraFieldDef::class);
    }

    // /**
    //  * @return ExtraFieldDef[] Returns an array of ExtraFieldDef objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ExtraFieldDef
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
