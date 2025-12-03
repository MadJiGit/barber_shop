<?php

namespace App\Controller\Admin;

use App\Entity\Procedure;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

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
            ->setSearchFields(['id', 'type'])
            ->setDefaultSort(['date_added' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('type', 'Наименование'),
            MoneyField::new('price_master', 'Цена мастър')->setCurrency('BGN'),
            MoneyField::new('price_junior', 'Цена джуниър')->setCurrency('BGN'),
            IntegerField::new('duration_master', 'Времетраене мастър (мин)'),
            IntegerField::new('duration_junior', 'Времетраене джуниър (мин)'),
            BooleanField::new('available', 'Активна'),
            DateTimeField::new('date_added', 'Създадена')->onlyOnIndex(),
            DateTimeField::new('date_last_update', 'Обновена')->hideOnIndex(),
            DateTimeField::new('date_stopped', 'Спряна')->hideOnIndex(),
        ];
    }
}
