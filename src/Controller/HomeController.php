<?php

namespace App\Controller;

use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use App\Repository\PublisherRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        BookRepository $bookRepository,
        CategoryRepository $categoryRepository,
        AuthorRepository $authorRepository,
        PublisherRepository $publisherRepository
    ): Response {
        $search = [
            'q' => $request->query->get('q'),
            'category' => $request->query->get('category'),
            'author' => $request->query->get('author'),
            'publisher' => $request->query->get('publisher'),
        ];

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 12; // Books per page

        $books = $bookRepository->findBySearch($search);
        $totalBooks = count($books);
        $totalPages = ceil($totalBooks / $limit);
        $offset = ($page - 1) * $limit;

        $paginatedBooks = array_slice($books, $offset, $limit);

        return $this->render('home/index.html.twig', [
            'books' => $paginatedBooks,
            'categories' => $categoryRepository->findAll(),
            'authors' => $authorRepository->findAll(),
            'publishers' => $publisherRepository->findAll(),
            'search' => $search,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalBooks' => $totalBooks,
        ]);
    }
    
    #[Route('/book/{id}', name: 'app_book_show')]
    public function show(int $id, BookRepository $bookRepository): Response
    {
        $book = $bookRepository->find($id);

        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        // Get related books (same category, excluding current book)
        $relatedBooks = $bookRepository->createQueryBuilder('b')
            ->where('b.category = :category')
            ->andWhere('b.id != :currentId')
            ->setParameter('category', $book->getCategory())
            ->setParameter('currentId', $book->getId())
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();

        return $this->render('home/show.html.twig', [
            'book' => $book,
            'relatedBooks' => $relatedBooks,
        ]);
    }
}
