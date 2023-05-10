<?php

namespace App\Entity;

use App\Repository\DsResponseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DsResponseRepository::class)]
class DsResponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 4)]
    private ?string $codigo = null;

    #[ORM\Column(length: 255)]
    private ?string $significado = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): self
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getSignificado(): ?string
    {
        return $this->significado;
    }

    public function setSignificado(string $significado): self
    {
        $this->significado = $significado;

        return $this;
    }

    public function __toString(): string
    {
        return $this->significado;
    }
}
