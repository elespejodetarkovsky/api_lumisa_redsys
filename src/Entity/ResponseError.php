<?php

namespace App\Entity;

use App\Repository\ResponseErrorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResponseErrorRepository::class)]
class ResponseError
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 4, nullable: true)]
    private ?string $codigo = null;

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $sisoxxx = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descripcion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(?string $codigo): self
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getSisoxxx(): ?string
    {
        return $this->sisoxxx;
    }

    public function setSisoxxx(?string $sisoxxx): self
    {
        $this->sisoxxx = $sisoxxx;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function __toString(): string
    {
        return $this->descripcion;
    }
}
