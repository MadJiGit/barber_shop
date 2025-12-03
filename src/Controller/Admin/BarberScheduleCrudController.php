<?php

namespace App\Controller\Admin;

use App\Entity\BarberSchedule;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class BarberScheduleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BarberSchedule::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('График')
            ->setEntityLabelInPlural('Графици')
            ->setDefaultSort(['created_at' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            AssociationField::new('barber', 'Барбер'),
            ArrayField::new('schedule_data', 'Седмичен график'),
            DateTimeField::new('created_at', 'Създаден')->onlyOnIndex(),
            DateTimeField::new('updated_at', 'Обновен')->hideOnIndex(),
        ];
    }
}
