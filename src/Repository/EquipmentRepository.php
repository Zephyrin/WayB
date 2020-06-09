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
        int $page,
        int $limit,
        ?string $sort,
        ?string $sortBy,
        ?string $search,
        ?string $validate,
        ?string $askValidate,
        ?string $weight,
        ?string $price,
        ?string $own,
        ?string $wish,
        ?string $others,
        ?string $belongToSubCategories,
        ?string $belongToBrands
    ) {
        $query = $this->createQueryBuilder('e');
        $query = $this->search(
            $query,
            $search,
            $weight,
            $price,
            $own,
            $wish,
            $others,
            $belongToSubCategories,
            $belongToBrands
        );
        return $this->resultCount(
            $query,
            $page,
            $limit,
            false,
            $sort,
            $sortBy,
            $validate,
            $askValidate
        );
    }

    // /**
    //  * @return Equipment[] Returns an array of Equipment objects
    //  */
    public function findByUserOrValidate(
        User $user,
        int $page,
        int $limit,
        ?string $sort,
        ?string $sortBy,
        ?string $search,
        ?string $validate,
        ?string $askValidate,
        ?string $weight,
        ?string $price,
        ?string $own,
        ?string $wish,
        ?string $others,
        ?string $belongToSubCategories,
        ?string $belongToBrands
    ) {
        $query = $this->createQueryBuilder('e')
            ->Where('(e.validate = true OR e.createdBy = :user)')
            ->setParameter('user', $user->getId());
        $query = $this->search(
            $query,
            $search,
            $weight,
            $price,
            $own,
            $wish,
            $others,
            $belongToSubCategories,
            $belongToBrands
        );
        return $this->resultCount(
            $query,
            $page,
            $limit,
            false,
            $sort,
            $sortBy,
            $validate,
            $askValidate
        );
    }

    private function search(
        QueryBuilder $query
        , ?string $search
        , ?string $weight
        , ?string $price
        , ?string $own
        , ?string $wish
        , ?string $others
        , ?string $belongToSubCategories
        , ?string $belongToBrands
    ) {
        if ($search != null) {
            $query = $query->leftJoin('e.brand', 'brand')
                ->leftJoin('e.subCategory', 'sub')
                ->leftJoin('sub.category', 'cat')
                ->andWhere('(LOWER(e.name) LIKE :search OR LOWER(e.description) LIKE :search OR LOWER(brand.name) LIKE :search OR LOWER(sub.name) LIKE :search OR LOWER(cat.name) LIKE :search)')
                ->setParameter('search', '%' . addcslashes(strtolower($search), '%_') . '%');
        }
        if ($others != null) {
            $eqOrSup = $others == 'true' ? '=' : '>';
            $query = $query->andWhere('(select count(chara.id) from App\\Entity\\Characteristic chara where chara.equipment = e.id)' . $eqOrSup . '0');
        }
        if ($belongToSubCategories != null) {
            $array = json_decode($belongToSubCategories);
            $query = $query->andWhere('e.subCategory in (:belongSub)')
                ->setParameter('belongSub', $array);
        }
        if ($belongToBrands != null) {
            $array = json_decode($belongToBrands);
            $query = $query->andWhere('e.brand in (:belongBrand)')
                ->setParameter('belongBrand', $array);
        }
        $query = $this->setLowerGreaterEqual($query, "App\Entity\Characteristic", "equipment", $weight, "weight");
        $query = $this->setLowerGreaterEqual($query, "App\Entity\Characteristic", "equipment", $price, "price");
        $query = $this->setLowerGreaterEqual($query, "App\Entity\Characteristic", "equipment", $own, "own");
        $query = $this->setLowerGreaterEqual($query, "App\Entity\Characteristic", "equipment", $wish, "wish");
        return $query;
    }
}
