<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findBySearch(array $criteria): array
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.authors', 'a')
            ->leftJoin('b.publisher', 'p')
            ->leftJoin('b.category', 'c')
            ->addSelect('a', 'p', 'c');

        if (!empty($criteria['q'])) {
            $qb->andWhere('b.title LIKE :q OR b.description LIKE :q OR a.name LIKE :q')
               ->setParameter('q', '%' . $criteria['q'] . '%');
        }

        if (!empty($criteria['category'])) {
            $qb->andWhere('c.id = :cat')
               ->setParameter('cat', $criteria['category']);
        }

        if (!empty($criteria['author'])) {
            $qb->andWhere('a.id = :auth')
               ->setParameter('auth', $criteria['author']);
        }

        if (!empty($criteria['publisher'])) {
            $qb->andWhere('p.id = :pub')
               ->setParameter('pub', $criteria['publisher']);
        }

        return $qb->getQuery()->getResult();
    }
}
