<?php

namespace App\Entity;

use App\Repository\NotificationUrlRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: NotificationUrlRepository::class)]
#[UniqueEntity(['idOper', 'orderId'])]
class NotificationUrl
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $orderId = null;

    #[ORM\Column(length: 20)]
    private ?string $amount = null;
    #[ORM\Column(length: 255, unique: true)]
    private ?string $idOper = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\Column(length: 20)]
    private ?string $protocolVersion = null;




    public function __construct()
    {
        $this->createAt = new \DateTimeImmutable();
    }

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

    public function getIdOper(): ?string
    {
        return $this->idOper;
    }

    public function setIdOper(string $idOper): self
    {
        $this->idOper = $idOper;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getProtocolVersion(): ?string
    {
        return $this->protocolVersion;
    }

    public function setProtocolVersion(string $protocolVersion): self
    {
        $this->protocolVersion = $protocolVersion;

        return $this;
    }
}
