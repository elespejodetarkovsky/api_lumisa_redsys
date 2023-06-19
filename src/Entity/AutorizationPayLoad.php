<?php

namespace App\Entity;


use App\Repository\AutorizationPayLoadRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AutorizationPayLoadRepository::class)]
#[UniqueEntity(['token'])]
class AutorizationPayLoad
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $token;

    #[ORM\Column(length: 20)]
    private string $amount;

    #[ORM\Column(length: 255)]
    private string $orderId;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dsServerTransId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $protocolVersion = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dsMethodUrl = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $order
     * @return AutorizationPayLoad
     */
    public function setOrderId(string $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }


    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return AutorizationPayLoad
     */
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     * @return AutorizationPayLoad
     */
    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getDsServerTransId(): ?string
    {
        return $this->dsServerTransId;
    }

    /**
     * @param string $dsServerTransId
     * @return AutorizationPayLoad
     */
    public function setDsServerTransId(?string $dsServerTransId): self
    {
        $this->dsServerTransId = $dsServerTransId;

        return $this;
    }

    /**
     * @return string
     */
    public function getProtocolVersion(): ?string
    {
        return $this->protocolVersion;
    }

    /**
     * @param string $protocolVersion
     * @return AutorizationPayLoad
     */
    public function setProtocolVersion(?string $protocolVersion): self
    {
        $this->protocolVersion = $protocolVersion;

        return $this;
    }

    /**
     * @return string
     */
    public function getDsMethodUrl(): ?string
    {
        return $this->dsMethodUrl;
    }

    /**
     * @param string $dsMethodUrl
     * @return AutorizationPayLoad
     */
    public function setDsMethodUrl(?string $dsMethodUrl = null): self
    {
        $this->dsMethodUrl = $dsMethodUrl;

        return $this;
    }




}