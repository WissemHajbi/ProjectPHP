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

        $books = $bookRepository->findBySearch($search);

        return $this->render('home/index.html.twig', [
            'books' => $books,
            'categories' => $categoryRepository->findAll(),
            'authors' => $authorRepository->findAll(),
            'publishers' => $publisherRepository->findAll(),
            'search' => $search,
        ]);
    }
    
    #[Route('/book/{id}', name: 'app_book_show')]
    public function show(int $id, BookRepository $bookRepository): Response
    {
        $book = $bookRepository->find($id);
        
        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        return $this->render('home/show.html.twig', [
            'book' => $book,
        ]);
    }
}
