<?php
namespace App\EventListener;

use App\Entity\User;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserListener
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHarsher)
    {
        $this->passwordHasher = $passwordHarsher;
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof User) {
            return;
        }

        $entity->setPassword(
            $this->passwordHasher->hashPassword($entity, $entity->getPassword())
        );

    }
}
