<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users')
            ->setPageTitle(Crud::PAGE_INDEX, 'Manage Users');
    }

    public function configureActions(Actions $actions): Actions
    {
        $resetPassword = Action::new('resetPassword', 'Reset Password', 'fa fa-key')
            ->linkToCrudAction('resetPassword')
            ->setCssClass('btn btn-warning');

        return $actions
            ->add(Crud::PAGE_INDEX, $resetPassword)
            ->setPermission('resetPassword', 'ROLE_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        yield EmailField::new('email');
        yield TextField::new('firstname');
        yield TextField::new('lastname');
        yield ChoiceField::new('roles')
            ->setChoices([
                'Subscriber' => 'ROLE_ABONNE',
                'Agent' => 'ROLE_AGENT',
                'Admin' => 'ROLE_ADMIN',
            ])
            ->allowMultipleChoices();
        yield TextField::new('phone');
    }

    public function resetPassword(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $user = $context->getEntity()->getInstance();
        
        // Generate a random password
        $newPassword = bin2hex(random_bytes(4)); // 8 chars
        $hashed = $this->userPasswordHasher->hashPassword($user, $newPassword);
        
        $user->setPassword($hashed);
        
        $this->container->get('doctrine')->getManager()->flush();

        $this->addFlash('success', sprintf('Password reset for %s. New password: %s', $user->getEmail(), $newPassword));

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}
