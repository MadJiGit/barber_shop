<?php

namespace App\Controller\Admin;

use App\Entity\Procedure;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class ProcedureCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Procedure::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Процедура')
            ->setEntityLabelInPlural('Процедури')
            ->setPageTitle('index', 'Процедури')
            ->setPageTitle('new', 'Добави процедура')
            ->setPageTitle('edit', 'Редактирай процедура')
            ->showEntityActionsInlined(); // Remove batch action checkboxes
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('type', 'Процедура')
                ->setColumns(3),
            IntegerField::new('duration_master', 'Мин (М)')
                ->setColumns(2)
                ->setHelp('Време за мастър в минути'),
            IntegerField::new('duration_junior', 'Мин (Д)')
                ->setColumns(2)
                ->setHelp('Време за джуниър в минути'),
            NumberField::new('price_master', 'Цена (М)')
                ->setColumns(2)
                ->setNumDecimals(2)
                ->setHelp('Цена за мастър в лева'),
            NumberField::new('price_junior', 'Цена (Д)')
                ->setColumns(2)
                ->setNumDecimals(2)
                ->setHelp('Цена за джуниър в лева'),
            BooleanField::new('available', 'Активна')
                ->setColumns(1),
            DateTimeField::new('date_added', 'Добавена на')
                ->onlyOnDetail(),
            DateTimeField::new('date_last_update', 'Последна промяна')
                ->onlyOnDetail(),
        ];
    }
}
