<?php

namespace App\Controller\Admin;

use App\Entity\BarberProcedure;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class BarberProcedureCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BarberProcedure::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Барбер Процедура')
            ->setEntityLabelInPlural('Барбер Процедури')
            ->setDefaultSort(['valid_from' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            AssociationField::new('barber', 'Барбер'),
            AssociationField::new('procedure', 'Процедура'),
            BooleanField::new('can_perform', 'Може да извършва'),
            DateTimeField::new('valid_from', 'Валидна от'),
            DateTimeField::new('valid_until', 'Валидна до')->hideOnIndex(),
        ];
    }
}
