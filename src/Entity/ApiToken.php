<?php

namespace App\Entity;

use App\Repository\ApiTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PrePersist;

#[ORM\Entity(repositoryClass: ApiTokenRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ApiToken
{

    private const PERSONAL_ACCESS_TOKEN_PREFIX = 'rs_lumisa_';


    public const SCOPE_GET = 'ROLE_USER_API_GET';
    public const SCOPE_POST = 'ROLE_USER_API_POST';
    public const SCOPE_ALL = 'ROLE_API_ALL';
    public const SCOPE_ADMIN = 'ROLE_ADMIN';

    public const SCOPES = [
        self::SCOPE_GET => 'User con permisos para realizar get',
        self::SCOPE_POST => 'User con permisos para realizar post',
        self::SCOPE_ALL => 'User con permisos para realizar get/post',
        self::SCOPE_ADMIN => 'acceso total'
    ];


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'apiTokens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $ownedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(length: 74)]
    private string $token;

    #[ORM\Column]
    private array $scopes = [];


    public function __construct(string $tokenType = self::PERSONAL_ACCESS_TOKEN_PREFIX)
    {

        $this->token = $tokenType.bin2hex(random_bytes(32));

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwnedBy(): ?User
    {
        return $this->ownedBy;
    }

    public function setOwnedBy(?User $ownedBy): self
    {
        $this->ownedBy = $ownedBy;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    #[prePersist]
    public function addTimeToToken()
    {

        //le sumaré al token el valor que se encuentre en la TODO configuración
        $now        = new \DateTimeImmutable();
        $this->setExpiresAt($now->modify('+ 2 minute'));

    }
    public function setScopes(array $scopes): self
    {
        $this->scopes = $scopes;

        return $this;
    }

    public function isValid(): bool
    {
        //return $this->expiresAt === null || $this->expiresAt > new \DateTimeImmutable();
        //si es null tambien lo considero falso
        return $this->expiresAt > new \DateTimeImmutable();
    }
}
