<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order')]
#[IsGranted('ROLE_ABONNE')]
class OrderController extends AbstractController
{
    #[Route('/checkout', name: 'app_order_checkout')]
    public function checkout(SessionInterface $session, BookRepository $bookRepository): Response
    {
        $cart = $session->get('cart', []);
        
        if (empty($cart)) {
            return $this->redirectToRoute('app_home');
        }

        $cartData = [];
        $total = 0;
        
        foreach ($cart as $id => $quantity) {
            $book = $bookRepository->find($id);
            if ($book) {
                $cartData[] = [
                    'book' => $book,
                    'quantity' => $quantity
                ];
                $total += $book->getPrice() * $quantity;
            }
        }
        
        // Get user address
        $user = $this->getUser();
        $defaultAddress = $user->getAddresses()[0] ?? '';

        return $this->render('order/checkout.html.twig', [
            'items' => $cartData,
            'total' => $total,
            'address' => $defaultAddress
        ]);
    }

    #[Route('/confirm', name: 'app_order_confirm', methods: ['POST'])]
    public function confirm(Request $request, SessionInterface $session, BookRepository $bookRepository, EntityManagerInterface $em): Response
    {
        $cart = $session->get('cart', []);
        if (empty($cart)) {
            return $this->redirectToRoute('app_home');
        }

        $user = $this->getUser();
        $address = $request->request->get('address');
        
        // Create Order
        $order = new Order();
        $order->setUser($user);
        $order->setDeliveryAddress($address);
        $order->setReference(uniqid('ORD-'));
        $order->setStatus(Order::STATUS_CONFIRMED); // Directly confirmed for simplicity
        
        $total = 0;
        
        foreach ($cart as $id => $quantity) {
            $book = $bookRepository->find($id);
            if ($book) {
                $item = new OrderItem();
                $item->setBook($book);
                $item->setBookTitle($book->getTitle());
                $item->setQuantity($quantity);
                $item->setPrice($book->getPrice());
                
                $order->addItem($item);
                $total += $book->getPrice() * $quantity;
                
                // Decrement stock?
                $book->setStock(max(0, $book->getStock() - $quantity));
            }
        }
        
        $order->setTotal($total);
        
        $em->persist($order);
        $em->flush();
        
        $session->remove('cart');

        return $this->render('order/confirmation.html.twig', [
            'order' => $order
        ]);
    }
}
