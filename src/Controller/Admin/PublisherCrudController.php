<?php

namespace App\Controller\Admin;

use App\Entity\Publisher;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PublisherCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Publisher::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
    }
}
