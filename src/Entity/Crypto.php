<?php

namespace App\Entity;

use App\Repository\CryptoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CryptoRepository::class)]
class Crypto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["crypto:read"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["crypto:read"])]
    private ?string $name = null;

    #[ORM\Column(length: 10)]
    #[Groups(["crypto:read"])]
    private ?string $abbreviation = null;

    #[
        ORM\OneToMany(
            mappedBy: "crypto",
            targetEntity: Price::class,
            orphanRemoval: true,
        ),
    ]
    #[Groups(["crypto:read"])]
    private Collection $prices;

    public function __construct()
    {
        $this->prices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }
    public function setAbbreviation(string $abbreviation): self
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }
}
