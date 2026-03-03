<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route("/api/user", name: "api_admin_user_")]
#[IsGranted("ROLE_ADMIN")]
class AdminUserController extends AbstractController
{
    #[Route("", name: "list", methods: ["GET"])]
    public function list(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        return $this->json($users, 200, [], ["groups" => "user:read"]);
    }

    #[Route("/{id}", name: "show", methods: ["GET"])]
    public function show(int $id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json(["message" => "Utilisateur introuvable"], 404);
        }

        return $this->json($user, 200, [], ["groups" => "user:read"]);
    }

    #[Route("", name: "create", methods: ["POST"])]
    public function create(
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
            "role",
        ];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->json(["error" => "Missing field: $field"], 400);
            }
        }

        if (
            $em
                ->getRepository(User::class)
                ->findOneBy(["email" => $data["email"]])
        ) {
            return $this->json(["error" => "Email already registered"], 409);
        }

        $user = new User();
        $user
            ->setEmail($data["email"])
            ->setFirstName($data["firstName"])
            ->setLastName($data["lastName"])
            ->setPhoneNumber($data["phoneNumber"])
            ->setBalance("0.00")
            ->setPassword(
                $passwordHasher->hashPassword($user, $data["password"]),
            );

        $user->setRoles(
            $data["role"] === "admin" ? ["ROLE_ADMIN"] : ["ROLE_USER"],
        );

        $em->persist($user);
        $em->flush();

        return $this->json(
            ["user" => $user],
            201,
            [],
            ["groups" => "user:read"],
        );
    }

    #[Route("/{id}", name: "update", methods: ["PATCH"])]
    public function update(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
    ): JsonResponse {
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json(["message" => "Utilisateur introuvable"], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data["firstName"])) {
            $user->setFirstName($data["firstName"]);
        }
        if (isset($data["lastName"])) {
            $user->setLastName($data["lastName"]);
        }
        if (isset($data["email"])) {
            $user->setEmail($data["email"]);
        }
        if (isset($data["phoneNumber"])) {
            $user->setPhoneNumber($data["phoneNumber"]);
        }

        if (isset($data["password"])) {
            $user->setPassword($hasher->hashPassword($user, $data["password"]));
        }

        if (isset($data["role"])) {
            $user->setRoles(
                $data["role"] === "admin" ? ["ROLE_ADMIN"] : ["ROLE_USER"],
            );
        }

        $em->flush();

        return $this->json($user, 200, [], ["groups" => "user:read"]);
    }

    #[Route("/{id}", name: "delete", methods: ["DELETE"])]
    public function delete(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $em,
    ): JsonResponse {
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json(["message" => "Utilisateur introuvable"], 404);
        }

        $userResponse = clone $user;

        $em->remove($user);
        $em->flush();

        return $this->json($userResponse, 200, [], ["groups" => "user:read"]);
    }
}
