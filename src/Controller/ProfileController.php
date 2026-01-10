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

#[Route('/profile')]
#[IsGranted('ROLE_ABONNE')]
class ProfileController extends AbstractController
{
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
}
