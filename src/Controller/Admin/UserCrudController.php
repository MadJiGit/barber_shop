<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    private Security $security;
    private UserPasswordHasherInterface $passwordHasher;
    public function __construct(Security $security, UserPasswordHasherInterface $passwordHasher)
    {
        $this->security = $security;
        $this->passwordHasher = $passwordHasher;
    }
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $rolesChoices['Manager'] = 'ROLE_MANAGER';
        $rolesChoices['Receptions'] = 'ROLE_RECEPTIONIST';
        $rolesChoices['Barber Master'] = 'ROLE_BARBER_SENIOR';
        $rolesChoices['Barber Junior'] = 'ROLE_BARBER_JUNIOR';
        $rolesChoices['Client'] = 'ROLE_CLIENT';

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $rolesChoices['Admin'] = 'ROLE_ADMIN';
        }

        $fields = [
            IdField::new('id')->hideOnForm(),
            TextField::new('email'),
            TextField::new('password')
                ->setFormType(PasswordType::class)
                ->onlyOnForms()
                ->setRequired($pageName === Crud::PAGE_NEW),
            TextField::new('first_name'),
            TextField::new('last_name'),
            TextField::new('nick_name'),
            TextField::new('phone'),
        ];

        $currentUserId = $this->getUser()?->getId();
        $editingUser = $this->getContext()?->getEntity()?->getInstance();
        $editingUserId = $editingUser?->getId();

        // Check if editing user is SUPER_ADMIN
        $editingUserIsSuperAdmin = $editingUser && in_array('ROLE_SUPER_ADMIN', $editingUser->getRoles());

        // ADMIN cannot edit SUPER_ADMIN roles
        if ($currentUserId !== $editingUserId) {
            // If ADMIN tries to edit SUPER_ADMIN, don't show roles field
            if (!$editingUserIsSuperAdmin || $this->isGranted('ROLE_SUPER_ADMIN')) {
                $fields[] = ChoiceField::new('roles')
                    ->setChoices($rolesChoices)
                    ->allowMultipleChoices()
                    ->renderExpanded();
            }
        }

        // isActive and isBanned fields - disabled for ADMIN when editing SUPER_ADMIN
        $isDisabled = $editingUserIsSuperAdmin && !$this->isGranted('ROLE_SUPER_ADMIN');

        $fields[] = BooleanField::new('isActive')
            ->setFormTypeOption('disabled', $isDisabled);
        $fields[] = BooleanField::new('isBanned')
            ->setFormTypeOption('disabled', $isDisabled);

        if($this->isGranted('ROLE_SUPER_ADMIN')) {
            $fields[] = TextField::new('confirmationToken')->hideOnForm();
            $fields[] = DateTimeField::new('tokenExpiresAt')->hideOnForm();
        }
        $fields[] = DateTimeField::new('date_added')->onlyOnIndex();

        return $fields;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->displayIf(function ($entity) {
                    // Prevent editing yourself
                    if ($entity->getId() === $this->getUser()->getId()) {
                        return false;
                    }
                    // ADMIN cannot edit SUPER_ADMIN
                    if (!$this->isGranted('ROLE_SUPER_ADMIN') && in_array('ROLE_SUPER_ADMIN', $entity->getRoles())) {
                        return false;
                    }
                    return true;
                });
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->displayIf(function ($entity) {
                    // Prevent deleting yourself
                    if ($entity->getId() === $this->getUser()->getId()) {
                        return false;
                    }
                    // ADMIN cannot delete SUPER_ADMIN
                    if (!$this->isGranted('ROLE_SUPER_ADMIN') && in_array('ROLE_SUPER_ADMIN', $entity->getRoles())) {
                        return false;
                    }
                    return true;
                });
            });
    }

    public function persistEntity($entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User && $entityInstance->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword());
            $entityInstance->setPassword($hashedPassword);
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity($entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            // Get original data before changes
            $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);

            // Check if target user is SUPER_ADMIN
            $isSuperAdmin = in_array('ROLE_SUPER_ADMIN', $originalData['roles'] ?? []);

            // If ADMIN tries to edit SUPER_ADMIN, restore protected fields
            if ($isSuperAdmin && !$this->isGranted('ROLE_SUPER_ADMIN')) {
                // Restore isActive and isBanned from original data
                $entityInstance->setIsActive($originalData['is_active']);
                $entityInstance->setIsBanned($originalData['is_banned']);

                // Restore roles as well
                $entityInstance->setRoles($originalData['roles']);
            }

            // Handle password hashing
            $password = $entityInstance->getPassword();
            if ($password && strlen($password) < 60) { // not hashed yet
                $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $password);
                $entityInstance->setPassword($hashedPassword);
            } else {
                // Restore original password
                $entityInstance->setPassword($originalData['password']);
            }
        }
        parent::updateEntity($entityManager, $entityInstance);
    }
}
