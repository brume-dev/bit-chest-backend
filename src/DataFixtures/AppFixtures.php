<?php

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
        // 1. Création de l'Admin
        $admin = new User();
        $admin->setEmail("admin@bitchest.com");
        $admin->setRoles(["ROLE_ADMIN"]);
        $admin->setFirstName("Admin");
        $admin->setLastName("System");
        $admin->setPhoneNumber("0600000000");
        // Le mot de passe sera "password"
        $password = $this->hasher->hashPassword($admin, "password");
        $admin->setPassword($password);
        $admin->setBalance(0);
        $manager->persist($admin);

        // 2. Création d'un User classique
        $user = new User();
        $user->setEmail("user@bitchest.com");
        $user->setFirstName("John");
        $user->setLastName("Doe");
        $user->setPhoneNumber("0612345678");
        $user->setPassword($password); // Même mot de passe "password"
        $user->setBalance(500);
        $manager->persist($user);

        // 3. Création des Cryptos et des Prix
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

            // Générer 30 jours d'historique de prix
            // On part d'il y a 30 jours jusqu'à aujourd'hui
            $currentPrice = $data["base_price"];

            for ($i = 30; $i >= 0; $i--) {
                $price = new Price();
                $price->setCrypto($crypto);

                // On simule une date (aujourd'hui moins $i jours)
                $date = new \DateTime();
                $date->modify("- $i days");
                $price->setDate($date);

                // On fait varier le prix un peu au hasard (+/- 5%)
                $variation = rand(-50, 50) / 1000; // entre -0.05 et +0.05
                $currentPrice = $currentPrice * (1 + $variation);

                $price->setValue((string) $currentPrice); // Convertir en string pour le type DECIMAL

                $manager->persist($price);
            }
        }

        $manager->flush();
    }
}
