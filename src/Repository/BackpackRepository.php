<?php

namespace App\Repository;

use App\Entity\Backpack;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Backpack|null find($id, $lockMode = null, $lockVersion = null)
 * @method Backpack|null findOneBy(array $criteria, array $orderBy = null)
 * @method Backpack[]    findAll()
 * @method Backpack[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BackpackRepository extends ServiceEntityRepository
{
    use AbstractRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Backpack::class);
    }

    public function findByUser(
        User $user,
        int $page,
        int $limit,
        string $sort,
        string $sortBy,
        ?string $search
    ) {
        $query = $this->createQueryBuilder('e')
            ->Where('e.createdBy = :val')
            ->setParameter('val', $user->getId());
        $query = $this->search($query, $search);
        return $this->resultCount(
            $query,
            $page,
            $limit,
            false,
            $sort,
            $sortBy,
            null,
            null
        );
    }
    private function search(QueryBuilder $query, ?string $search)
    {
        if ($search != null) {
            $query = $query->andWhere('(LOWER(e.name) LIKE :search)')
                ->setParameter('search', '%' . addcslashes(strtolower($search), '%_') . '%');
        }
        return $query;
    }
}
