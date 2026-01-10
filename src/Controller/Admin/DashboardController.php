<?php

namespace App\Controller\Admin;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Category;
use App\Entity\Order;
use App\Entity\Publisher;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_AGENT')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function index(): Response
    {
        $bookCount = $this->entityManager->getRepository(Book::class)->count([]);
        $userCount = $this->entityManager->getRepository(User::class)->count([]);
        $orderCount = $this->entityManager->getRepository(Order::class)->count([]);
        $totalRevenue = $this->entityManager->getRepository(Order::class)->createQueryBuilder('o')
            ->select('SUM(o.total)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        return $this->render('admin/my_dashboard.html.twig', [
            'book_count' => $bookCount,
            'user_count' => $userCount,
            'order_count' => $orderCount,
            'total_revenue' => $totalRevenue,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('MyBookstore BackOffice');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToUrl('Back to Site', 'fas fa-arrow-left', '/')->setCssClass('btn btn-outline-secondary');

        yield MenuItem::section('Catalogue');
        yield MenuItem::linkToCrud('Books', 'fas fa-book', Book::class);
        yield MenuItem::linkToCrud('Authors', 'fas fa-pen-nib', Author::class);
        yield MenuItem::linkToCrud('Publishers', 'fas fa-building', Publisher::class);
        yield MenuItem::linkToCrud('Categories', 'fas fa-tags', Category::class);

        yield MenuItem::section('Sales');
        yield MenuItem::linkToCrud('Orders', 'fas fa-shopping-cart', Order::class);

        yield MenuItem::section('System')->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Users', 'fas fa-users', User::class)->setPermission('ROLE_ADMIN');
    }
}
