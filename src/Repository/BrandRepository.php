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
    , ?string $search
    , ?string $validate
    , ?string $askValidate
    , bool $noPagination) {
        $query = $this->createQueryBuilder('e');
        $query = $this->search($query, $search);
        return $this->resultCount($query
            , $page
            , $limit
            , $noPagination
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
    , ?string $search
    , ?string $validate
    , ?string $askValidate
    , bool $noPagination)
    {
        $query = $this->createQueryBuilder('e')
            ->Where('(e.validate = true OR e.createdBy = :val)')
            ->setParameter('val', $user->getId());
        $query = $this->search($query, $search);
        return $this->resultCount($query
            , $page
            , $limit
            , $noPagination
            , $sort
            , $sortBy
            , $validate
            , $askValidate
            );
    }

    private function search(QueryBuilder $query, ?string $search) {
        if($search != null) {
            $query = $query->andWhere('(LOWER(e.name) LIKE :search OR LOWER(e.uri) LIKE :search)')
                ->setParameter('search', '%'.addcslashes(strtolower($search), '%_').'%');
        }
        return $query;
    }
}
