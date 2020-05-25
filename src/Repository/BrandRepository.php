<?php

namespace App\Repository;

use App\Entity\Brand;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Brand|null find($id, $lockMode = null, $lockVersion = null)
 * @method Brand|null findOneBy(array $criteria, array $orderBy = null)
 * @method Brand[]    findAll()
 * @method Brand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BrandRepository extends ServiceEntityRepository
{
    use AbstractRepository;
 
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Brand::class);
    }

    public function findForAmbassador(int $page
    , int $limit
    , string $sort
    , string $sortBy
    , string $search = null
    , string $validate = null
    , string $askValidate = null) {
        $query = $this->createQueryBuilder('e');
        $query = $this->search($query, $search);
        return $this->resultCount($query
            , $page
            , $limit
            , $sort
            , $sortBy
            , $validate
            , $askValidate
        );
    }
    // /**
    //  * @return Equipment[] Returns an array of Equipment objects
    //  */
    public function findByUserOrValidate(User $user
    , int $page
    , int $limit
    , string $sort
    , string $sortBy
    , string $search = null
    , string $validate = null
    , string $askValidate = null)
    {
        $query = $this->createQueryBuilder('e')
            ->Where('(e.validate = true OR e.createdBy = :val')
            ->setParameter('val', $user->getId());
        $query = $this->search($query, $search);
        return $this->resultCount($query
            , $page
            , $limit
            , $sort
            , $sortBy
            , $validate
            , $askValidate
            );
    }

    private function search(QueryBuilder $query, ?string $search) {
        if($search != null) {
            $query = $query->andWhere('(e.name LIKE :search OR e.uri LIKE :search)')
                ->setParameter('search', '%'.addcslashes($search, '%_').'%');
        }
        return $query;
    }
    // /**
    //  * @return Brand[] Returns an array of Brand objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Brand
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
