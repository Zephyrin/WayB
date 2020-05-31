<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;

trait AbstractRepository
{
    public function getPage($page = 1)
    {
        if ($page < 1) {
            $page = 1;
        }

        return floor($page);
    }

    public function getLimit($limit = 20)
    {
        if ($limit < 1 || $limit > 20) {
            $limit = 20;
        }

        return floor($limit);
    }

    public function getOffset($page, $limit)
    {
        $offset = 0;
        if ($page != 0 && $page != 1) {
            $offset = ($page - 1) * $limit;
        }

        return $offset;
    }

    public function setLowerGreaterEq(
        QueryBuilder $query,
        ?string $val,
        string $join,
        string $alias,
        string $param
    ) {
        if ($val !== null) {
            preg_match('/(gt\d+)?(lt\d+)?(eq\d+)?/', $val, $match, PREG_OFFSET_CAPTURE);
            $values = [null, null, null];
            $values = $this->matchIntoTab($match, $values, 1);
            $values = $this->matchIntoTab($match, $values, 2);
            $values = $this->matchIntoTab($match, $values, 3);
            if (!in_array($alias, $query->getAllAliases()))
                $query = $query->leftJoin($join, $alias);
            if ($values[0] !== null) {
                $query->andWhere($alias . '.' . $param . ' > :' . $param . 'GT')
                    ->setParameter($param . 'GT', $values[0]);
            }
            if ($values[1] !== null) {
                $query->andWhere($alias . '.' . $param . ' < :' . $param . 'LT')
                    ->setParameter($param . 'LT', $values[1]);
            }
            if ($values[2] !== null) {
                $query->andWhere($alias . '.' . $param . ' = :' . $param . 'EQ')
                    ->setParameter($param . 'EQ', $values[2]);
            }
        }
        return $query;
    }
    private function matchIntoTab(array $match, array $tab, int $matchIndex)
    {
        if (count($match) > $matchIndex && $match[$matchIndex][1] >= 0) {
            $tab[$matchIndex - 1] = substr($match[$matchIndex][0], 2);
        }
        return $tab;
    }

    public function resultCount(
        QueryBuilder $query,
        int $page,
        int $limit,
        bool $noPagination,
        string $sort,
        string $sortBy,
        ?string $validate,
        ?string $askValidate,
        ?string $countName,
        ?string $parentName,
        ?string $countExp
    ) {
        $page = $this->getPage($page);
        $limit = $this->getLimit($limit);
        $offset = $this->getOffset($page, $limit);
        $lSortBy = 'e.' . $sortBy;

        if ($validate != null) {
            $val = $validate == 'false' ? false : true;
            $query = $query->andWhere('e.validate = :validate')
                ->setParameter('validate', $val);
        }
        if ($askValidate != null) {
            $val = $askValidate == 'false' ? false : true;
            $query = $query->andWhere('e.askValidate = :askValidate')
                ->setParameter('askValidate', $val);
        }
        if ($countExp) {
            
            preg_match('/(l|le|e|g|ge)(\d+)((l|le|e|g|ge)(\d+))?/', $countExp, $match, PREG_UNMATCHED_AS_NULL);
            $sup = null;
            $supE = null;
            $eq = null;
            $lower = null;
            $lowerE = null;
            for ($i = 1; $i <= 3; $i = $i + 2) {
                if ($match[$i] != null && $match[$i + 1] != null) {
                    switch ($match[$i]) {
                        case "l":
                            $lower = $match[$i + 1];
                            break;
                        case "le":
                            $lowerE = $match[$i + 1];
                            break;
                        case "e":
                            $eq = $match[$i + 1];
                            break;
                        case "g":
                            $sup = $match[$i + 1];
                            break;
                        case "ge":
                            $supE = $match[$i + 1];
                            break;
                        default:
                            break;
                    }
                }
            }
            $subQuery = '(select count(s.id) from '.$countName.' s where e.id = s.'.$parentName.')';
            if ($sup != null)
                $query = $query->andWhere($subQuery.' > :sup')
                    ->setParameter('sup', $sup);
            if ($supE != null)
                $query = $query->andWhere($subQuery.' >= :supE')
                    ->setParameter('supE', $supE);
            if ($eq != null)
                $query = $query->andWhere($subQuery.' == :eq')
                    ->setParameter('eq', $eq);
            if ($lower != null)
                $query = $query->andWhere($subQuery.' < :lower')
                    ->setParameter('lower', $lower);
            if ($lowerE != null)
                $query = $query->andWhere($subQuery.' > :lowerE')
                    ->setParameter('lowIerE', $lowerE);
        }

        $queryCount = clone $query;
        $count =  $queryCount
            ->select("count(DISTINCT e.id)")
            ->getQuery()->getSingleScalarResult();
        if ($noPagination) {
            $ret = $query
                ->orderBy($lSortBy, $sort)
                ->getQuery()->getResult();
        } else {
            $ret = $query
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->orderBy($lSortBy, $sort)
                ->getQuery()->getResult();
        }
        return [$ret, $count, count($ret) + $offset, $page, $limit];
    }
}
