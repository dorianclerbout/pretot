<?php

namespace App\Repository;

use App\Entity\TeamMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamMember>
 */
class TeamMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamMember::class);
    }

    /**
     * @return TeamMember[]
     */
    public function findVisibleOrdered(): array
    {
        return $this->createQueryBuilder('tm')
            ->where('tm.visible = true')
            ->orderBy('tm.sortOrder', 'ASC')
            ->addOrderBy('tm.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return TeamMember[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('tm')
            ->orderBy('tm.sortOrder', 'ASC')
            ->addOrderBy('tm.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}