<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Потребител')
            ->setEntityLabelInPlural('Потребители')
            ->setSearchFields(['id', 'email', 'first_name', 'last_name', 'nick_name', 'phone'])
            ->setDefaultSort(['date_added' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            EmailField::new('email', 'Имейл'),
            TextField::new('first_name', 'Име'),
            TextField::new('last_name', 'Фамилия'),
            TextField::new('nick_name', 'Прякор')->hideOnIndex(),
            TelephoneField::new('phone', 'Телефон'),
            ArrayField::new('roles', 'Роли'),
            BooleanField::new('is_active', 'Активен'),
            BooleanField::new('is_banned', 'Блокиран')->hideOnIndex(),
            DateTimeField::new('date_added', 'Създаден')->onlyOnIndex(),
            DateTimeField::new('date_last_update', 'Обновен')->hideOnIndex(),
            DateTimeField::new('date_banned', 'Блокиран на')->hideOnIndex(),
        ];
    }
}
