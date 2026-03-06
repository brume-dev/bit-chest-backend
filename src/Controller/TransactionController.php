<?php

namespace App\Controller;

use App\Entity\Crypto;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\CryptoRepository;
use App\Repository\PriceRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/api/transaction")]
#[IsGranted("ROLE_USER")]
class TransactionController extends AbstractController
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private CryptoRepository $cryptoRepository,
        private PriceRepository $priceRepository,
        private EntityManagerInterface $em,
    ) {}

    /**
     * GET /transaction
     * Returns all transactions for the authenticated user.
     */
    #[Route("", name: "transaction_list", methods: ["GET"])]
    public function list(): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $transactions = $this->transactionRepository->findBy(["user" => $user]);

        return $this->json([
            "transactions" => array_map(
                fn(Transaction $t) => $this->serialize($t),
                $transactions,
            ),
        ]);
    }

    /**
     * GET /api/transaction/all
     * Admin only — returns all transactions across all users.
     */
    #[Route("/all", name: "transaction_all", methods: ["GET"])]
    #[IsGranted("ROLE_ADMIN")]
    public function all(): JsonResponse
    {
        $transactions = $this->transactionRepository->findAll();

        return $this->json([
            "transactions" => array_map(
                fn(Transaction $t) => $this->serialize($t),
                $transactions,
            ),
        ]);
    }

    /**
     * GET /transaction/:id
     * Returns a single transaction belonging to the authenticated user.
     */
    #[Route("/{id}", name: "transaction_show", methods: ["GET"])]
    public function show(int $id): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $transaction = $this->transactionRepository->find($id);

        if (!$transaction || $transaction->getUser() !== $user) {
            return $this->json(["message" => "Transaction not found."], 404);
        }

        return $this->json(["transaction" => $this->serialize($transaction)]);
    }

    /**
     * POST /transaction
     * Creates a new buy/sell transaction for the authenticated user.
     */
    #[Route("", name: "transaction_create", methods: ["POST"])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $cryptoId = $data["cryptoId"] ?? null;
        $priceId = $data["priceId"] ?? null;
        $amount = $data["amount"] ?? null;
        $type = $data["type"] ?? null;

        if (
            !$cryptoId ||
            !$priceId ||
            $amount === null ||
            !in_array($type, ["buy", "sell"], true)
        ) {
            return $this->json(["message" => "Invalid request body."], 400);
        }

        if ((float) $amount <= 0) {
            return $this->json(
                ["message" => "Amount must be greater than zero."],
                400,
            );
        }

        $crypto = $this->cryptoRepository->find($cryptoId);
        if (!$crypto) {
            return $this->json(["message" => "Crypto not found."], 404);
        }

        $price = $this->priceRepository->find($priceId);
        if (!$price) {
            return $this->json(["message" => "Price not found."], 404);
        }

        if ($price->getCrypto() !== $crypto) {
            return $this->json(
                ["message" => "Price does not match the given crypto."],
                400,
            );
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $totalCost = bcmul((string) $amount, $price->getValue(), 8);

        if ($type === "buy") {
            if (bccomp($user->getBalance(), $totalCost, 2) < 0) {
                return $this->json(["message" => "Insufficient balance."], 400);
            }
            $user->setBalance(bcsub($user->getBalance(), $totalCost, 2));
        } elseif ($type === "sell") {
            $held = $this->getHeldAmount($user, $crypto);
            if (bccomp($held, (string) $amount, 8) < 0) {
                return $this->json(
                    ["message" => "Insufficient holdings."],
                    400,
                );
            }
            $user->setBalance(bcadd($user->getBalance(), $totalCost, 2));
        }

        $transaction = new Transaction();
        $transaction
            ->setUser($user)
            ->setCrypto($crypto)
            ->setPrice($price)
            ->setAmount((string) $amount)
            ->setType($type)
            ->setDate(new \DateTime());

        $this->em->persist($transaction);
        $this->em->flush();

        return $this->json(
            ["transaction" => $this->serialize($transaction)],
            201,
        );
    }

    private function getHeldAmount(
        \App\Entity\User $user,
        \App\Entity\Crypto $crypto,
    ): string {
        $transactions = $this->transactionRepository->findBy([
            "user" => $user,
            "crypto" => $crypto,
        ]);

        $held = "0";
        foreach ($transactions as $t) {
            if ($t->getType() === "buy") {
                $held = bcadd($held, $t->getAmount(), 8);
            } else {
                $held = bcsub($held, $t->getAmount(), 8);
            }
        }

        return $held;
    }

    // -------------------------------------------------------------------------
    // Serialization helper
    // -------------------------------------------------------------------------

    private function serialize(Transaction $t): array
    {
        return [
            "id" => $t->getId(),
            "type" => $t->getType(),
            "amount" => $t->getAmount(),
            "date" => $t->getDate()?->format(\DateTimeInterface::ATOM),
            "crypto" => [
                "id" => $t->getCrypto()->getId(),
                "name" => $t->getCrypto()->getName(),
                "abbreviation" => $t->getCrypto()->getAbbreviation(),
            ],
            "price" => [
                "id" => $t->getPrice()->getId(),
                "value" => $t->getPrice()->getValue(),
                "date" => $t
                    ->getPrice()
                    ->getDate()
                    ?->format(\DateTimeInterface::ATOM),
            ],
        ];
    }
}
