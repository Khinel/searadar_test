<?php

namespace App\Controller;

use App\Dto\CreateBookDto;
use App\Dto\SearchBookDto;
use App\Dto\UpdateBookDto;
use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class BookController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer) {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/book/get/{id}/", methods={"GET"}, name="app_book_get")
     * @param BookRepository $bookRepo
     * @param int            $id
     * @return Response
     */
    public function bookGet(BookRepository $bookRepo, int $id): Response
    {
        $book = $bookRepo->find($id);

        if (!$book) {
            return new JsonResponse(['status' => 'not_found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['status' => 'ok', 'data' => [
            'id' => $book->getId(),
            'name' => $book->getName(),
            'author' => $book->getAuthor()
        ]]);
    }

    /**
     * @Route("/book/create/", methods={"POST"}, name="app_book_create")
     * @param ManagerRegistry $doctrine
     * @param Request         $request
     * @return Response
     */
    public function bookCreate(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();

        /** @var CreateBookDto $dto */
        $dto = $this->serializer->deserialize($request->getContent(), CreateBookDto::class, 'json');

        $book = new Book($dto->getName(), $dto->getAuthor());
        $entityManager->persist($book);
        $entityManager->flush();

        return new JsonResponse(['status' => 'ok', 'data' => [
            'id' => $book->getId()
        ]]);
    }

    /**
     * @Route("/book/update/{id}/", methods={"PATCH"}, name="app_book_update")
     * @param ManagerRegistry $doctrine
     * @param BookRepository  $bookRepo
     * @param int             $id
     * @param Request         $request
     * @return Response
     */
    public function bookUpdate(ManagerRegistry $doctrine, BookRepository $bookRepo, Request $request, int $id): Response
    {
        $entityManager = $doctrine->getManager();

        /** @var UpdateBookDto $dto */
        $dto = $this->serializer->deserialize($request->getContent(), UpdateBookDto::class, 'json');

        $book = $bookRepo->find($id);

        if (!$book) {
            return new JsonResponse(['status' => 'not_found'], Response::HTTP_NOT_FOUND);
        }

        $book->setAuthor($dto->getAuthor());
        $book->setName($dto->getName());
        $entityManager->persist($book);
        $entityManager->flush();

        return new JsonResponse(['status' => 'ok', 'data' => [
            'id' => $book->getId(),
            'name' => $book->getName(),
            'author' => $book->getAuthor()
        ]]);
    }

    /**
     * @Route("/book/delete/{id}/", methods={"DELETE"}, name="app_book_delete")
     * @param ManagerRegistry $doctrine
     * @param BookRepository  $bookRepo
     * @param int             $id
     * @return Response
     */
    public function bookDelete(ManagerRegistry $doctrine, BookRepository $bookRepo, int $id): Response
    {
        $book = $bookRepo->find($id);

        if (!$book) {
            return new JsonResponse(['status' => 'not_found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager = $doctrine->getManager();
        $entityManager->remove($book);
        $entityManager->flush();

        return new JsonResponse(['status' => 'ok']);
    }

    /**
     * @Route("/book/author_counts/", methods={"GET"}, name="app_book_author_counts")
     * @param BookRepository $bookRepo
     * @return Response
     */
    public function authorsCount(BookRepository $bookRepo): Response
    {
        $authorsCounts = $bookRepo->getAuthorsCounts();

        return new JsonResponse(['status' => 'ok', 'authors_counts' => $authorsCounts]);
    }

    /**
     * @Route("/book/random/", methods={"GET"}, name="app_book_random")
     * @param BookRepository $bookRepo
     * @return Response
     */
    public function randomBook(BookRepository $bookRepo): Response
    {
        $randomBook = $bookRepo->getRandomBook();

        return new JsonResponse(['status' => 'ok', 'data' => [
            'id' => $randomBook->getId(),
            'name' => $randomBook->getName(),
            'author' => $randomBook->getAuthor()
        ]]);
    }

    /**
     * @Route("/book/search/", methods={"POST"}, name="app_book_search")
     * @param BookRepository $bookRepo
     * @return Response
     */
    public function searchBook(BookRepository $bookRepo, Request $request): Response
    {
        /** @var SearchBookDto $dto */
        $dto = $this->serializer->deserialize($request->getContent(), SearchBookDto::class, 'json');
        if (!$dto->getTerm()) {
            return new JsonResponse(['status' => 'no term']);
        }

        $booksSeach = $bookRepo->searchBooks($dto->getTerm(), $dto->getLimit(), $dto->getOffset());

        $result = [];
        foreach ($booksSeach['data'] as $book) {
            $result[] = [
                'id' => $book->getId(),
                'name' => $book->getName(),
                'author' => $book->getAuthor()
            ];
        }

        return new JsonResponse(['status' => 'ok', 'data' => ['count' => $booksSeach['count'], 'books' =>$result]]);
    }
}
