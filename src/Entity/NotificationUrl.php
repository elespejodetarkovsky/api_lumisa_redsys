<?php

namespace App\Entity;

use App\Repository\NotificationUrlRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationUrlRepository::class)]
class NotificationUrl
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $orderId = null;

    #[ORM\Column(length: 20)]
    private ?string $amount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cres = null;

    #[ORM\Column(length: 255)]
    private ?string $idOper = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): self
    {
        $this->orderId = $orderId;

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

    public function getCres(): ?string
    {
        return $this->cres;
    }

    public function setCres(?string $cres): self
    {
        $this->cres = $cres;

        return $this;
    }

    public function getIdOper(): ?string
    {
        return $this->idOper;
    }

    public function setIdOper(string $idOper): self
    {
        $this->idOper = $idOper;

        return $this;
    }
}
