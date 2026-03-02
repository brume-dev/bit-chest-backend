<?php
namespace App\Controller;

use App\Repository\CryptoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/crypto", name: "api_crypto_")]
class CryptoController extends AbstractController
{
    #[Route("", name: "list", methods: ["GET"])]
    public function list(CryptoRepository $cryptoRepository): JsonResponse
    {
        $cryptos = $cryptoRepository->findAll();
        return $this->json($cryptos, 200, [], ["groups" => "crypto:read"]);
    }

    #[Route("/{id}", name: "show", methods: ["GET"])]
    public function show(
        int $id,
        CryptoRepository $cryptoRepository,
    ): JsonResponse {
        $crypto = $cryptoRepository->find($id);

        if (!$crypto) {
            return $this->json(["message" => "Crypto non trouvée"], 404);
        }

        return $this->json($crypto, 200, [], ["groups" => "crypto:read"]);
    }
}
