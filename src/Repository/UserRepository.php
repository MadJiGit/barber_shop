<?php

namespace App\Repository;

use App\Entity\Roles;
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

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

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
        $barber = Roles::BARBER->value;

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
            ->where('u.roles like :role')
            ->setParameter('role', '%'.$barber.'%')
            ->getQuery()
            ->getArrayResult();
    }

    public function getAllClients(): array
    {
        $role = Roles::CLIENT->value;

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

        return in_array(Roles::ADMIN, $user->getRoles());
    }

    public function isUserIsSuperAdmin($id): bool
    {
        $user = $this->findOneById($id);

        return in_array(Roles::SUPER_ADMIN, $user->getRoles());
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

            if (true) {
                echo '<pre>'.var_export($a->getSQL(), true).'</pre>';
                exit;
            } else {
                //                        return $a->getFirstResult();
                return $a->getOneOrNullResult();
            }
        }
    }
}
