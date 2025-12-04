<?php

namespace App\Controller\Admin;

use App\Entity\Appointments;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class AppointmentsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Appointments::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Записване')
            ->setEntityLabelInPlural('Записвания')
            ->setSearchFields(['id', 'status', 'notes'])
            ->setDefaultSort(['date' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            AssociationField::new('client', 'Клиент'),
            AssociationField::new('barber', 'Барбер'),
            AssociationField::new('procedure_type', 'Процедура'),
            DateTimeField::new('date', 'Дата и час'),
            IntegerField::new('duration', 'Продължителност (мин)'),
            ChoiceField::new('status', 'Статус')
                ->setChoices([
                    'Чакащо' => 'pending',
                    'Потвърдено' => 'confirmed',
                    'Завършено' => 'completed',
                    'Отказано' => 'cancelled',
                    'Пропуснато' => 'no_show',
                ]),
            TextareaField::new('notes', 'Бележки')->hideOnIndex(),
            TextareaField::new('cancellation_reason', 'Причина за отказ')->hideOnIndex(),
            DateTimeField::new('date_added', 'Създадено')->onlyOnIndex(),
            DateTimeField::new('date_last_update', 'Обновено')->hideOnIndex(),
            DateTimeField::new('date_canceled', 'Отказано на')->hideOnIndex(),
        ];
    }
}
