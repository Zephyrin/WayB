<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use FOS\RestBundle\Request\ParamFetcher;

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

    public function findPagination(
        ParamFetcher $paramFetcher,
        bool $isAmbassador,
        User $user = null
    ) {
        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');
        $pagination = $paramFetcher->get('pagination');
        $sort = $paramFetcher->get('sort');
        $sortBy = $paramFetcher->get('sortBy');
        $search = $paramFetcher->get('search');
        $validate = $paramFetcher->get('validate');
        $askValidate = $paramFetcher->get('askValidate');
        $subCategoryCount = $paramFetcher->get('subCategoryCount');
        if ($isAmbassador)
            return $this->findForAmbassador(
                $page,
                !($pagination == 'true'),
                $limit,
                $sort,
                $sortBy,
                $search,
                $validate,
                $askValidate,
                $subCategoryCount
            );
        return $this->findByUserOrValidate(
            $user,
            $page,
            !($pagination == 'true'),
            $limit,
            $sort,
            $sortBy,
            $search,
            $validate,
            $askValidate,
            $subCategoryCount
        );
    }

    public function findForAmbassador(
        int $page,
        int $limit,
        bool $noPagination,
        string $sort,
        string $sortBy,
        ?string $search,
        ?string $validate,
        ?string $askValidate,
        ?string $subCategoryCount
    ) {
        $query = $this->createQueryBuilder('e');
        $query = $this->search($query, $search);
        if ($sortBy == 'subCategoryCount') {
            $sortBy = 'COUNT(subs)';
            $query = $query->leftJoin('e.subCategories', 'subs');
        }
        $query = $this->setLowerGreaterEqual($query, "App\Entity\SubCategory", "category", $subCategoryCount, "catSubCat");
        return $this->resultCount(
            $query,
            $page,
            $limit,
            $noPagination,
            $sort,
            $sortBy,
            $validate,
            $askValidate
        );
    }

    // /**
    //  * @return Category[] Returns an array of Category objects
    //  */
    public function findByUserOrValidate(
        User $user,
        int $page,
        int $limit,
        bool $noPagination,
        string $sort,
        string $sortBy,
        ?string $search,
        ?string $validate,
        ?string $askValidate,
        ?string $subCategoryCount
    ) {
        $query = $this->createQueryBuilder('e')
            ->Where('(e.validate = true OR e.createdBy = :val)')
            ->setParameter('val', $user->getId());
        $query = $this->search($query, $search);
        if ($sortBy == 'subCategoryCount') {
            $sortBy = 'COUNT(subs)';
            $query = $query->leftJoin('e.subCategories', 'subs');
        }
        $query = $this->setLowerGreaterEqual($query, "App\Entity\SubCategory", "category", $subCategoryCount, "catSubCat");
        return $this->resultCount(
            $query,
            $page,
            $limit,
            $noPagination,
            $sort,
            $sortBy,
            $validate,
            $askValidate
        );
    }

    private function search(QueryBuilder $query, ?string $search)
    {
        if ($search != null) {
            $query = $query->leftJoin('e.subCategories', 'subCat');
            $query = $query->andWhere('(LOWER(e.name) LIKE :search OR LOWER(subCat.name) LIKE :search)')
                ->setParameter('search', '%' . addcslashes(strtolower($search), '%_') . '%');
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
}
