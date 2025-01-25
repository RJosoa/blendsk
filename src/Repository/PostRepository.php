<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findByCategory(?int $categoryId = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.likes', 'l')
            ->addSelect('c')
            ->addSelect('COUNT(l) as HIDDEN likesCount')
            ->groupBy('p.id');

        if ($categoryId != null) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        return $qb->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUserFavorites(int $userId)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.favourites', 'f')
            ->where('f.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUserLikes(int $userId)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.likes', 'l')
            ->where('l.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
