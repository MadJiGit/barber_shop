<?php

namespace App\Controller\Admin;

use App\Entity\BarberScheduleException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
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
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public static function getEntityFqcn(): string
    {
        return BarberScheduleException::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('admin.barber_schedule_exception.singular')
            ->setEntityLabelInPlural('admin.barber_schedule_exception.plural')
            ->setSearchFields(['reason'])
            ->setDefaultSort(['date' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_NEW, Action::INDEX);
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->onlyOnIndex(),
        ];

        // TODO: За глобални затваряния на салона, използвай BusinessHoursException
        $fields[] = AssociationField::new('barber')
            ->setLabel('admin.barber_schedule_exception.barber')
            ->setHelp('⚠️ ВРЕМЕННО: За да затвориш салона за всички барбъри, трябва да създадеш изключение за всеки барбър поотделно. В бъдеще ще използваме BusinessHoursException за глобални затваряния.')
            ->setRequired(true)
            ->setFormTypeOptions([
                'choices' => $this->userRepository->getAllActiveBarbers(),
                'choice_label' => 'email',
                'placeholder' => 'Избери барбър...',
            ]);

        $fields[] = DateField::new('date')
            ->setLabel('admin.barber_schedule_exception.date')
            ->setRequired(true);

        $fields[] = BooleanField::new('is_available')
            ->setLabel('admin.barber_schedule_exception.is_available')
            ->setHelp('admin.barber_schedule_exception.is_available_help');

        $fields[] = TimeField::new('start_time')
            ->setLabel('admin.barber_schedule_exception.start_time')
            ->setHelp('admin.barber_schedule_exception.start_time_help')
            ->hideOnIndex();

        $fields[] = TimeField::new('end_time')
            ->setLabel('admin.barber_schedule_exception.end_time')
            ->setHelp('admin.barber_schedule_exception.end_time_help')
            ->hideOnIndex();

        $fields[] = ArrayField::new('excluded_slots')
            ->setLabel('admin.barber_schedule_exception.excluded_slots')
            ->setHelp('admin.barber_schedule_exception.excluded_slots_help')
            ->hideOnIndex();

        $fields[] = TextField::new('reason')
            ->setLabel('admin.barber_schedule_exception.reason')
            ->setRequired(true)
            ->setHelp('admin.barber_schedule_exception.reason_help');

        if ($pageName === Crud::PAGE_INDEX) {
            $fields[] = AssociationField::new('created_by')
                ->setLabel('admin.barber_schedule_exception.created_by');
            $fields[] = DateTimeField::new('created_at')
                ->setLabel('admin.barber_schedule_exception.created_at');
        }

        return $fields;
    }

    public function createEntity(string $entityFqcn)
    {
        $exception = new BarberScheduleException();

        $currentUser = $this->getUser();
        if ($currentUser) {
            $exception->setCreatedBy($currentUser);
        }

        return $exception;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof BarberScheduleException) {
            $currentUser = $this->getUser();
            if ($currentUser && !$entityInstance->getCreatedBy()) {
                $entityInstance->setCreatedBy($currentUser);
            }
        }

        parent::persistEntity($entityManager, $entityInstance);
    }
}
