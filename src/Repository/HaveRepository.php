<?php

namespace App\Repository;

use App\Entity\Have;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Have|null find($id, $lockMode = null, $lockVersion = null)
 * @method Have|null findOneBy(array $criteria, array $orderBy = null)
 * @method Have[]    findAll()
 * @method Have[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HaveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Have::class);
    }

    public function findAllOfUser(User $user) {
        return $this->createQueryBuilder('h')
            ->Where('h.user = :val')
            ->setParameter('val', $user->getId())
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByIdAndCreatedBy(int $id, User $user) {
        return $this->createQueryBuilder('e')
            ->Where('e.id = :id')
            ->andWhere('e.createdBy = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }
    // /**
    //  * @return Have[] Returns an array of Have objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Have
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
