<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::DELETE); // Orders created via FrontOffice
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('reference');
        yield DateTimeField::new('createdAt');
        yield ChoiceField::new('status')->setChoices([
            'Pending' => Order::STATUS_PENDING,
            'Confirmed' => Order::STATUS_CONFIRMED,
            'Shipped' => Order::STATUS_SHIPPED,
        ]);
        yield MoneyField::new('total')->setCurrency('EUR');
        yield TextField::new('user.email', 'User');
        
        // For detail view
        yield CollectionField::new('items')
            ->onlyOnDetail()
            ->setEntryType(\App\Form\OrderItemType::class) // We might need a form type for display or just template
            ->setTemplatePath('admin/order_items.html.twig');
    }
}
