<?php

namespace App\Repository;

use App\Entity\AdviceArticle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdviceArticle>
 */
class AdviceArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdviceArticle::class);
    }

    /**
     * @return AdviceArticle[]
     */
    public function findVisibleOrdered(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.visible = true')
            ->orderBy('a.sortOrder', 'ASC')
            ->addOrderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return AdviceArticle[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.sortOrder', 'ASC')
            ->addOrderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneVisibleBySlug(string $slug): ?AdviceArticle
    {
        return $this->createQueryBuilder('a')
            ->where('a.visible = true')
            ->andWhere('a.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
