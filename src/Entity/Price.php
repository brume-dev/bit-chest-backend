<?php

// Price entity stores price information for cryptocurrencies

namespace App\Entity;

use App\Repository\PriceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PriceRepository::class)]
class Price
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["crypto:read"])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 8)]
    #[Groups(["crypto:read"])]
    private ?string $value = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["crypto:read"])]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: "prices")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Crypto $crypto = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getValue(): ?string
    {
        return $this->value;
    }
    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }
    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }
    public function getCrypto(): ?Crypto
    {
        return $this->crypto;
    }
    public function setCrypto(?Crypto $crypto): self
    {
        $this->crypto = $crypto;
        return $this;
    }
}
