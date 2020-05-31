<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    use AbstractRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findForAmbassador(int $page
    , int $limit
    , bool $noPagination
    , string $sort
    , string $sortBy
    , string $search = null
    , string $validate = null
    , string $askValidate = null
    , string $subCategoryCount = null) {
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
            , "App\Entity\SubCategory"
            , "category"
            , $subCategoryCount
        );
    }

    // /**
    //  * @return Category[] Returns an array of Category objects
    //  */
    public function findByUserOrValidate(User $user
    , int $page
    , int $limit
    , bool $noPagination
    , string $sort
    , string $sortBy
    , string $search = null
    , string $validate = null
    , string $askValidate = null
    , string $subCategoryCount = null)
    {
        $query = $this->createQueryBuilder('e')
            ->Where('(e.validate = true OR e.createdBy = :val')
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
            , "App\Entity\SubCategory"
            , "category"
            , $subCategoryCount
            );
    }

    private function search(QueryBuilder $query, ?string $search) {
        if($search != null) {
            $query = $query->andWhere('(e.name LIKE :search OR e.sub_category.name LIKE :search)')
                ->setParameter('search', '%'.addcslashes($search, '%_').'%');
        }
        return $query;
    }
    // /**
    //  * @return Category[] Returns an array of Category objects
    //  */

    public function findById($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }


    /*
    public function findOneBySomeField($value): ?Category
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
