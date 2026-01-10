<?php

namespace App\Controller;

use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

#[Route('/profile')]
#[IsGranted('ROLE_ABONNE')]
class ProfileController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private RequestStack $requestStack
    ) {
    }
    #[Route('/', name: 'app_profile')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        $defaultAddress = $user->getAddresses()[0] ?? '';

        $form = $this->createFormBuilder($user)
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('phone', TextType::class, ['required' => false])
            ->add('address', TextareaType::class, [
                'mapped' => false, 
                'data' => $defaultAddress, 
                'required'=>false,
                'label' => 'Primary Address'
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address = $form->get('address')->getData();
            if ($address) {
                $user->setAddresses([$address]);
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Profile updated.');
            
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/orders', name: 'app_orders')]
    public function orders(): Response
    {
        $user = $this->getUser();
        $orders = $user->getOrders();

        return $this->render('profile/orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/change-password', name: 'app_change_password', methods: ['POST'])]
    public function changePassword(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $currentPassword = $request->request->get('current_password');
        $newPassword = $request->request->get('new_password')['first'] ?? null;
        $confirmPassword = $request->request->get('new_password')['second'] ?? null;

        // Verify current password
        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            $this->addFlash('error', 'Current password is incorrect.');
            return $this->redirectToRoute('app_profile');
        }

        // Check if new passwords match
        if ($newPassword !== $confirmPassword) {
            $this->addFlash('error', 'New passwords do not match.');
            return $this->redirectToRoute('app_profile');
        }

        // Hash and set new password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        // Store password change timestamp in session
        $session = $this->requestStack->getSession();
        $session->set('password_changed_at', (new \DateTime())->format('Y-m-d H:i:s'));

        $entityManager->flush();

        $this->addFlash('success', 'Password changed successfully.');
        return $this->redirectToRoute('app_profile');
    }

    #[Route('/delete-account', name: 'app_delete_account', methods: ['POST'])]
    public function deleteAccount(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Verify password for account deletion
        $password = $request->request->get('delete_password');
        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            $this->addFlash('error', 'Password is incorrect. Account deletion cancelled.');
            return $this->redirectToRoute('app_profile');
        }

        // Clear session cart
        $session = $this->requestStack->getSession();
        $session->remove('cart');

        // Logout user
        $this->container->get('security.token_storage')->setToken(null);
        $session->invalidate();

        // Delete user (this will cascade delete orders and order items due to foreign keys)
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Your account has been permanently deleted.');

        return $this->redirectToRoute('app_home');
    }
}
