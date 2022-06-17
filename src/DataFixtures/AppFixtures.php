<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\User;

class AppFixtures extends Fixture
{
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $adminUser = new User();
        $adminUser->setEmail("admin@gmail.com");
        $password = $this->hasher->hashPassword( $adminUser, "admin123");
        $adminUser->setPassword($password)->setRoles(["ROLE_ADMIN"]);
        $manager->persist($adminUser);

        $manager->flush();
    }
}
