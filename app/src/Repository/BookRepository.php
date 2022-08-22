<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function add(Book $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Book $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Book[] Returns an array of Book objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Book
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @return array
     */
    public function getAuthorsCounts(): array {
        return $this->createQueryBuilder('book')
            ->select('book.author', 'COUNT(book.name) as author_count')
            ->groupBy('book.author')
            ->orderBy('author_count', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return Book
     */
    public function getRandomBook(): Book {
        return $this->createQueryBuilder('book')
            ->select('book')
            ->orderBy('RAND()')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $term
     * @param int    $limit
     * @param int    $offset
     * @return array
     */
    public function searchBooks(string $term, ?int $limit = 50, ?int $offset = 0): array {
        $qb = $this->createQueryBuilder('book')
            ->select('book')
            // big oof, should use elastic or something
            ->orWhere('book.name like :term')
            ->orWhere('book.author like :term')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('book.id', 'DESC')
            ->setParameter('term', '%'.$term.'%')
            ->getQuery();

        $paginator = new Paginator($qb, $fetchJoinCollection = false);
        $count = count($paginator);
        $content = new ArrayCollection();
        foreach ($paginator as $book) {
            $content->add($book);
        }

        // #todo object of page
        return ['count' => $count, 'data' => $content];
    }
}
