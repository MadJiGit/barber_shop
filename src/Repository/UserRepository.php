<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function getAllRolesByRolesName(string $role): array
    {
        //        dd($role);

        if (true) {
            return $this->createQueryBuilder('u')
                ->select('u.id',
                    'u.email',
                    'u.roles',
                    'u.first_name',
                    'u.last_name',
                    'u.nick_name',
                    'u.phone',
                    'u.date_added',
                    'u.date_banned',
                    'u.date_last_update')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%'.$role.'%')
            ->getQuery()
            ->getArrayResult();
        } else {
            $qb = $this->createQueryBuilder('u');
            $qb->andWhere(
                $qb->expr()->like('u.roles', ':role')
            )
                ->setParameter('role', '%'.$role.'%')
                ->getQuery()
                ->getArrayResult();

            return $qb;
        }
    }

    public function getWithNoRole(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u.id',
                'u.email',
                'u.roles',
                'u.first_name',
                'u.last_name',
                'u.nick_name',
                'u.phone',
                'u.date_added',
                'u.date_banned',
                'u.date_last_update')
            ->getQuery()
            ->getArrayResult();
    }

    public function getAllBarbers(): array
    {
        $barber = 'ROLE_BARBER';

        return $this->createQueryBuilder('u')
            ->where('u.roles like :role')
            ->setParameter('role', '%'.$barber.'%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all barbers sorted by seniority (SENIOR -> BARBER -> JUNIOR)
     *
     * @return User[]
     */
    public function getAllBarbersSortedBySeniority(): array
    {
        $barbers = $this->getAllBarbers();

        // Sort barbers by role hierarchy
        usort($barbers, function($a, $b) {
            $roleA = $this->getBarberSeniorityLevel($a);
            $roleB = $this->getBarberSeniorityLevel($b);
            return $roleA - $roleB;
        });

        return $barbers;
    }

    /**
     * Get seniority level for sorting (lower number = higher seniority)
     */
    private function getBarberSeniorityLevel(User $barber): int
    {
        $roles = $barber->getRoles();

        if (in_array('ROLE_BARBER_SENIOR', $roles)) {
            return 1; // Highest seniority
        }
        if (in_array('ROLE_BARBER', $roles)) {
            return 2; // Middle seniority
        }
        if (in_array('ROLE_BARBER_JUNIOR', $roles)) {
            return 3; // Lowest seniority
        }

        return 4; // Unknown/other
    }

    public function getAllClients(): array
    {
        $role = 'ROLE_CLIENT';

        if (true) {
            return $this->createQueryBuilder('u')
                ->select('u.id',
                    'u.email',
                    'u.roles',
                    'u.first_name',
                    'u.last_name',
                    'u.nick_name',
                    'u.phone',
                    'u.date_added',
                    'u.date_banned',
                    'u.date_last_update')
                ->andWhere('u.roles LIKE :role')
                ->setParameter('role', '%'.$role.'%')
                ->getQuery()
                ->getArrayResult();
        } else {
            $qb = $this->createQueryBuilder('u');
            $qb->andWhere(
                $qb->expr()->like('u.roles', ':role')
            )
                ->setParameter('role', '%'.$role.'%')
                ->getQuery()
                ->getArrayResult();

            return $qb;
        }
    }

    public function isUserIsAdmin($id): bool
    {
        $user = $this->findOneById($id);

        return in_array('ROLE_ADMIN', $user->getRoles());
    }

    public function isUserIsSuperAdmin($id): bool
    {
        $user = $this->findOneById($id);

        return in_array('ROLE_SUPER_ADMIN', $user->getRoles());
    }

    public function findOneByEmail($email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneById($id): User
    {
//        echo '<pre>'.var_export($id, true).'</pre>';
//        exit;



        if (true) {
            return $this->createQueryBuilder('u')
                ->andWhere('u.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult();
        //                ->getFirstResult();
        } else {
            //                    echo '<pre>'.var_export($id, true).'</pre>';
            //                    exit;

            $a = $this->createQueryBuilder('c')
                ->select('u')
                ->from(User::class, 'u')
                ->innerJoin('u.barber', 'ub')
                ->andWhere('u.id = :id')
                ->setParameter('id', $id)
                ->addSelect('ub')
                ->getQuery();
            //                ->getOneOrNullResult();

            return $a->getOneOrNullResult();
        }
    }

    /**
     * Find all appointments for a given user ID
     * Includes past, future, and cancelled appointments
     *
     * @param int $userId
     * @return array
     */
    public function findAppointmentsByUserId(int $userId): array
    {
        $em = $this->getEntityManager();

        return $em->createQueryBuilder()
            ->select('a, b, c, p')
            ->from('App\Entity\Appointments', 'a')
            ->leftJoin('a.barber', 'b')
            ->leftJoin('a.client', 'c')
            ->leftJoin('a.procedure_type', 'p')
            ->where('c.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('a.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
