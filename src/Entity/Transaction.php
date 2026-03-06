<?php

// Transaction entity represents a user's transaction record

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 8)]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: "transactions")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Crypto $crypto = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Price $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getType(): ?string
    {
        return $this->type;
    }
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }
    public function getAmount(): ?string
    {
        return $this->amount;
    }
    public function setAmount(string $amount): self
    {
        $this->amount = $amount;
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
    public function getUser(): ?User
    {
        return $this->user;
    }
    public function setUser(?User $user): self
    {
        $this->user = $user;
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
    public function getPrice(): ?Price
    {
        return $this->price;
    }
    public function setPrice(?Price $price): self
    {
        $this->price = $price;
        return $this;
    }
}
