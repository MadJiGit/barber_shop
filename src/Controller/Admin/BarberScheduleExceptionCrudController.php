<?php

namespace App\Controller\Admin;

use App\Entity\BarberScheduleException;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class BarberScheduleExceptionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BarberScheduleException::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Изключение')
            ->setEntityLabelInPlural('Изключения от график')
            ->setSearchFields(['reason'])
            ->setDefaultSort(['date' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            AssociationField::new('barber', 'Барбер'),
            DateField::new('date', 'Дата'),
            BooleanField::new('is_available', 'Работи'),
            TimeField::new('start_time', 'Начален час')->hideOnIndex(),
            TimeField::new('end_time', 'Краен час')->hideOnIndex(),
            ArrayField::new('excluded_slots', 'Изключени слотове')->hideOnIndex(),
            TextField::new('reason', 'Причина')->hideOnIndex(),
            AssociationField::new('created_by', 'Създадено от')->hideOnIndex(),
            DateTimeField::new('created_at', 'Създадено')->onlyOnIndex(),
        ];
    }
}
