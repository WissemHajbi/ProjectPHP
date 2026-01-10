<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class BookCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Book::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title');
        yield TextField::new('isbn');
        yield AssociationField::new('category');
        yield AssociationField::new('publisher');
        yield AssociationField::new('authors');
        yield MoneyField::new('price')->setCurrency('EUR');
        yield IntegerField::new('stock');
        yield TextEditorField::new('description');
        
        yield TextField::new('coverFile')
            ->setFormType(VichImageType::class)
            ->onlyOnForms()
            ->setLabel('Cover Image');

        yield ImageField::new('coverImage')
            ->setBasePath('/uploads/covers')
            ->onlyOnIndex()
            ->setLabel('Cover');
    }
}
