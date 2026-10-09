<?php

namespace App\Repository;

use App\Entity\CarouselSlide;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CarouselSlide>
 */
class CarouselSlideRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CarouselSlide::class);
    }

    /**
     * @return CarouselSlide[]
     */
    public function findVisibleOrdered(): array
    {
        return $this->findBy(['visible' => true], ['sortOrder' => 'ASC', 'id' => 'ASC']);
    }

    /**
     * @return CarouselSlide[]
     */
    public function findAllOrdered(): array
    {
        return $this->findBy([], ['sortOrder' => 'ASC', 'id' => 'ASC']);
    }
}
