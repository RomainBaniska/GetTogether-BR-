<?php

namespace App\Document;

use DateTimeInterface;
// use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[MongoDB\Document]
class Users implements PasswordAuthenticatedUserInterface
{
    #[MongoDB\Id]
    public string $id;

    #[MongoDB\Field(type: 'string')]
    private string $firstName = '';

    #[MongoDB\Field(type: 'string')]
    private string $lastName = '';

    #[MongoDB\Field(type: 'string')]
    private string $city;

    #[MongoDB\Field(type: 'date')]
    private ?DateTimeInterface $dateNaissance;

    // #[MongoDB\Field(type: 'string')]
    // public string $email;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "Veuillez entrer un email valide.")]
    public string $email;

    #[MongoDB\Field(type: 'string')]
    public string $password;

    #[MongoDB\Field(type: 'string')]
    #[Assert\EqualTo(propertyPath: 'password', message: 'Les mots de passe ne correspondent pas.')]
    private ?string $passwordConfirmation;

    #[MongoDB\Field(type: 'string')]
    private string $profilPicture;

    #[MongoDB\Field(type: 'string')]
    private string $role;

    #[MongoDB\Field(type: 'string')]
    private string $pseudo;

    #[MongoDB\Field(type: 'collection')]
    private array $tagsByCategory = [];
    // private ArrayCollection $tagsByCategory = [];

    #[MongoDB\Field(type: 'boolean')]
    private bool $filled = false;

    #[MongoDB\Field(type: 'string')]
    private string $userTags;


    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getDateNaissance(): ?DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getPasswordConfirmation(): ?string
    {
        return $this->passwordConfirmation;
    }

    public function getProfilPicture(): string
    {
        return $this->profilPicture;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getPseudo(): string
    {
        return $this->pseudo;
    }

    public function getTagsByCategory(): array
    {
        return $this->tagsByCategory;
    }

    public function setFirstName(string $firstName): Users
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function setLastName(string $lastName): Users
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function setCity(string $city): Users
    {
        $this->city = $city;

        return $this;
    }

    public function setDateNaissance(DateTimeInterface $dateNaissance): Users
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function setEmail(string $email): Users
    {
        $this->email = $email;

        return $this;
    }

    public function setPassword(string $password): Users
    {
        $this->password = $password;

        return $this;
    }

    public function setPasswordConfirmation(?string $passwordConfirmation): self
    {
        $this->passwordConfirmation = $passwordConfirmation;

        return $this;
    }

    public function setProfilPicture(string $profilPicture): Users
    {
        $this->profilPicture = $profilPicture;

        return $this;
    }

    public function setRole(string $role): Users
    {
        $this->role = $role;

        return $this;
    }
    public function setPseudo(string $pseudo): Users
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function setTagsByCategory(array $tagsByCategory): self
    {
        $this->tagsByCategory = $tagsByCategory;
        return $this;
    }

    /**
     * Vérifie si l'utilisateur a rempli les informations de profil.
     *
     * @return bool True si les informations sont remplies, False sinon.
     */
    public function hasFilledProfile(): bool
    {
        // Vérifie si les champs de profil sont remplis
        return $this->filled;
    }

    public function fill(): Users
    {
        $this->filled = true;

        return $this;
    }
    // 
        
    public function getUserTags(): string
    {
        return $this->userTags;
    }

    public function setUserTags(string $userTags): Users
    {
        $this->$userTags = $userTags;

        return $this;
    }
}



















