<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use App\Validator\Constraints\Password\StrongPassword;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_UUID', fields: ['uuid'])]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse email est déjà utilisée.')]
#[UniqueEntity(fields: ['uuid'], message: 'Cet UUID est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'UUID ne peut pas être vide.', groups: ['registration'])]
    #[Assert\Uuid(message: 'L\'UUID doit être valide.', groups: ['registration'])]
    private ?string $uuid = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Assert\Type(type: 'array', message: 'Les rôles doivent être un tableau.', groups: ['registration'])]
    #[Assert\All([
        new Assert\Type(type: 'string', message: 'Chaque rôle doit être une chaîne de caractères.', groups: ['registration']),
        new Assert\Regex(pattern: '/^ROLE_[A-Z_]+$/', message: 'Le format du rôle est invalide.', groups: ['registration'])
    ])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le mot de passe ne peut pas être vide.', groups: ['password_change'])]
    private ?string $password = null;

    #[StrongPassword(groups: ['registration'])]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 150, unique: true)]
    #[Assert\NotBlank(message: 'L\'email ne peut pas être vide.', groups: ['registration'])]
    #[Assert\Email(message: 'L\'adresse email {{ value }} n\'est pas valide.', groups: ['registration'])]
    #[Assert\Length(
        max: 150,
        maxMessage: 'L\'email ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $email = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le prénom ne peut pas être vide.', groups: ['registration'])]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères.',
        groups: ['registration']
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\-\s]+$/',
        message: 'Le prénom ne peut contenir que des lettres, espaces et tirets.',
        groups: ['registration']
    )]
    private ?string $firstname = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le nom de famille ne peut pas être vide.', groups: ['registration'])]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Le nom de famille doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom de famille ne peut pas dépasser {{ limit }} caractères.',
        groups: ['registration']
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\-\s]+$/',
        message: 'Le nom de famille ne peut contenir que des lettres, espaces et tirets.',
        groups: ['registration']
    )]
    private ?string $lastname = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La date de naissance ne peut pas être vide.', groups: ['registration'])]
    #[Assert\Type(type: '\DateTimeImmutable', message: 'La date de naissance doit être une date valide.', groups: ['registration'])]
    #[Assert\LessThan(
        value: '-18 years',
        message: 'Vous devez être majeur pour vous inscrire.',
        groups: ['registration']
    )]
    private ?\DateTimeImmutable $birthDate = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La date de création ne peut pas être vide.', groups: ['registration'])]
    #[Assert\Type(type: '\DateTimeImmutable', message: 'La date de création doit être une date valide.', groups: ['registration'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La date de mise à jour ne peut pas être vide.', groups: ['registration'])]
    #[Assert\Type(type: '\DateTimeImmutable', message: 'La date de mise à jour doit être une date valide.', groups: ['registration'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->roles = ['ROLE_USER'];
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->uuid;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    // Méthode pour effacer le plainPassword après encodage
    public function erasePlainPassword(): void
    {
        $this->plainPassword = null;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeImmutable $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
