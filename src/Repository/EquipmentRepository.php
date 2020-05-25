<?php

namespace App\Repository;

use App\Entity\Equipment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Equipment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Equipment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Equipment[]    findAll()
 * @method Equipment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipmentRepository extends ServiceEntityRepository
{
    use AbstractRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipment::class);
    }

    // /**
    //  * @return Equipment[] Returns an array of Equipment objects
    //  */
    public function findForAmbassador(
    int $page
    , int $limit
    , string $sort
    , string $sortBy
    , string $search = null
    , string $validate = null
    , string $askValidate = null
    , string $weight = null
    , string $price = null
    , string $own = null
    , string $wish = null)
    {
        $query = $this->createQueryBuilder('e');
        $query = $this->search($query, $search, $weight, $price, $own, $wish);
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
    , string $askValidate = null
    , string $weight = null
    , string $price = null
    , string $own = null
    , string $wish = null)
    {
        $query = $this->createQueryBuilder('e')
            ->Where('e.validate = true')
            ->orWhere('e.createdBy = :val')
            ->setParameter('val', $user->getId()
        );
        $query = $this->search($query, $search, $weight, $price, $own, $wish);
        return $this->resultCount($query
            , $page
            , $limit
            , $sort
            , $sortBy
            , $validate
            , $askValidate
        );
    }

    private function search(
        QueryBuilder $query
        , ?string $search
        , ?string $weight
        , ?string $price
        , ?string $own
        , ?string $wish) {
        if($search != null) {
            $query = $query->leftJoin('e.brand', 'brand')
                ->leftJoin('e.subCategory', 'sub')
                ->leftJoin('sub.category', 'cat')
                ->andWhere('(e.name LIKE :search OR e.description LIKE :search OR brand.name LIKE :search OR sub.name LIKE :search OR cat.name LIKE :search)')
                ->setParameter('search', '%'.addcslashes($search, '%_').'%');
        }
        
        $query = $this->setLowerGreaterEq($query, $weight, 'e.characteristics', 'chara', 'weight');
        $query = $this->setLowerGreaterEq($query, $price, 'e.characteristics', 'chara', 'price');
        $query = $this->setLowerGreaterEq($query, $own, 'e.characteristics', 'chara', 'own');
        $query = $this->setLowerGreaterEq($query, $wish, 'e.characteristics', 'chara', 'wish');
            
        return $query;
    }

    /*
    public function findOneBySomeField($value): ?Equipment
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
