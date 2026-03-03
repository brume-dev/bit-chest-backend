<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/api")]
class AuthController extends AbstractController
{
    #[Route("/auth/register", name: "api_register", methods: ["POST"])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $required = [
            "email",
            "password",
            "firstName",
            "lastName",
            "phoneNumber",
        ];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->json(
                    ["error" => "Missing field: $field"],
                    Response::HTTP_BAD_REQUEST,
                );
            }
        }

        if (
            $em
                ->getRepository(User::class)
                ->findOneBy(["email" => $data["email"]])
        ) {
            return $this->json(
                ["error" => "Email already registered"],
                Response::HTTP_CONFLICT,
            );
        }

        $user = new User();
        $user
            ->setEmail($data["email"])
            ->setFirstName($data["firstName"])
            ->setLastName($data["lastName"])
            ->setPhoneNumber($data["phoneNumber"])
            ->setBalance("0.00")
            ->setRoles(["ROLE_USER"])
            ->setPassword(
                $passwordHasher->hashPassword($user, $data["password"]),
            );

        $em->persist($user);
        $em->flush();

        return $this->json(
            [
                "message" => "User registered successfully",
                "user" => [
                    "id" => $user->getId(),
                    "email" => $user->getEmail(),
                    "firstName" => $user->getFirstName(),
                    "lastName" => $user->getLastName(),
                    "phoneNumber" => $user->getPhoneNumber(),
                    "balance" => $user->getBalance(),
                ],
            ],
            Response::HTTP_CREATED,
        );
    }

    #[Route("/auth/login", name: "api_login", methods: ["POST"])]
    public function login(): JsonResponse
    {
        // Intercepted by the security firewall — never actually called
        throw new \LogicException("This should never be reached.");
    }

    #[Route("/auth/me", name: "api_me", methods: ["GET"])]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            "id" => $user->getId(),
            "email" => $user->getEmail(),
            "firstName" => $user->getFirstName(),
            "lastName" => $user->getLastName(),
            "phoneNumber" => $user->getPhoneNumber(),
            "balance" => $user->getBalance(),
            "roles" => $user->getRoles(),
        ]);
    }

    #[Route("/auth/me", name: "api_update_me", methods: ["PATCH"])]
    public function updateMe(
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        if (isset($data["firstName"])) {
            $user->setFirstName($data["firstName"]);
        }
        if (isset($data["lastName"])) {
            $user->setLastName($data["lastName"]);
        }
        if (isset($data["phoneNumber"])) {
            $user->setPhoneNumber($data["phoneNumber"]);
        }

        $em->flush();

        return $this->json([
            "id" => $user->getId(),
            "email" => $user->getEmail(),
            "firstName" => $user->getFirstName(),
            "lastName" => $user->getLastName(),
            "phoneNumber" => $user->getPhoneNumber(),
            "balance" => $user->getBalance(),
            "roles" => $user->getRoles(),
        ]);
    }
}
