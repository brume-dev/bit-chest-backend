<?php

// AppFixtures loads initial data into the database for testing

namespace App\DataFixtures;

use App\Entity\Crypto;
use App\Entity\Price;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Admin
        $admin = new User();
        $admin->setEmail("admin@bitchest.com");
        $admin->setRoles(["ROLE_ADMIN"]);
        $admin->setFirstName("Admin");
        $admin->setLastName("System");
        $admin->setPhoneNumber("0600000000");
        $admin->setPassword($this->hasher->hashPassword($admin, "password"));
        $admin->setBalance("0.00");
        $manager->persist($admin);

        // 2. User
        $user = new User();
        $user->setEmail("user@bitchest.com");
        $user->setRoles(["ROLE_USER"]);
        $user->setFirstName("John");
        $user->setLastName("Doe");
        $user->setPhoneNumber("0612345678");
        $user->setPassword($this->hasher->hashPassword($user, "password"));
        $user->setBalance("500.00");
        $manager->persist($user);

        // 3. Cryptos + prix
        $cryptosData = [
            ["name" => "Bitcoin", "abbr" => "BTC", "base_price" => 30000],
            ["name" => "Ethereum", "abbr" => "ETH", "base_price" => 1800],
            ["name" => "Ripple", "abbr" => "XRP", "base_price" => 0.5],
            ["name" => "Litecoin", "abbr" => "LTC", "base_price" => 90],
            ["name" => "Cardano", "abbr" => "ADA", "base_price" => 0.4],
        ];

        foreach ($cryptosData as $data) {
            $crypto = new Crypto();
            $crypto->setName($data["name"]);
            $crypto->setAbbreviation($data["abbr"]);
            $manager->persist($crypto);

            $currentPrice = $data["base_price"];

            for ($i = 30; $i >= 0; $i--) {
                $price = new Price();
                $price->setCrypto($crypto);

                $date = new \DateTime();
                $date->modify("-$i days");
                $price->setDate($date);

                $variation = rand(-50, 50) / 1000;
                $currentPrice = $currentPrice * (1 + $variation);

                $price->setValue(number_format($currentPrice, 8, ".", ""));
                $manager->persist($price);
            }
        }

        $manager->flush();
    }
}
