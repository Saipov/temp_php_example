<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use App\Repository\OrganizationRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=OrganizationRepository::class)
 * @ORM\Table(name="organizations")
 * @UniqueEntity(fields={"email"}, errorPath="email", message="The company with the specified email address already
 *                                 exists.")
 * @UniqueEntity(fields={"phone"}, errorPath="phone", message="The company with the specified phone number already
 *                                 exists.")
 */
class Organization
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $site;

    /**
     * Сфера деятельности
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $sphere_activity;

    /**
     * ИНН
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $inn;

    /**
     * КПП
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $cpp;

    /**
     * @var ArrayCollection|PersistentCollection
     * @ORM\OneToMany (targetEntity="OrganizationTag", mappedBy="organization", fetch="LAZY")
     */
    private $tags;

    /**
     * @var ArrayCollection|PersistentCollection
     * @ORM\OneToMany (targetEntity="Project", mappedBy="organization", fetch="EXTRA_LAZY")
     */
    private $projects;

    /**
     * @var string|null
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    private ?string $phone;

    /**
     * @var string|null
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    private ?string $email;

    /**
     * Ответственный
     *
     * @var User|null
     * @ORM\ManyToOne(targetEntity="User", fetch="EXTRA_LAZY")
     */
    private ?User $responsible;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $region;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description;

    /**
     * Для удаления
     *
     * @ORM\Column(type="boolean", nullable=true, options={"default": 0})
     */
    private ?bool $to_remove = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $webHookFirst = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $created_at;

    /**
     * Ответственный
     *
     * @var PersistentCollection|ArrayCollection
     * @ORM\OneToMany(targetEntity="UserRole", mappedBy="organization", fetch="EXTRA_LAZY")
     */
    private $roles;


    /**
     * Organization constructor.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->created_at = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getWebHookFirst(): ?string
    {
        return $this->webHookFirst;
    }

    /**
     * @param string|null $webHookFirst
     */
    public function setWebHookFirst(?string $webHookFirst): void
    {
        $this->webHookFirst = $webHookFirst;
    }

    /**
     * @return string|null
     */
    public function getSphereActivity(): ?string
    {
        return $this->sphere_activity;
    }

    /**
     * @param string|null $sphere_activity
     */
    public function setSphereActivity(?string $sphere_activity): void
    {
        $this->sphere_activity = $sphere_activity;
    }

    /**
     * @return string|null
     */
    public function getInn(): ?string
    {
        return $this->inn;
    }

    /**
     * @param string|null $inn
     */
    public function setInn(?string $inn): void
    {
        $this->inn = $inn;
    }

    /**
     * @return string|null
     */
    public function getCpp(): ?string
    {
        return $this->cpp;
    }

    /**
     * @param string|null $cpp
     */
    public function setCpp(?string $cpp): void
    {
        $this->cpp = $cpp;
    }

    /**
     * @return string|null
     */
    public function getSite(): ?string
    {
        return $this->site;
    }

    /**
     * @param string|null $site
     */
    public function setSite(?string $site): void
    {
        $this->site = $site;
    }

    /**
     * @return ArrayCollection|PersistentCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param OrganizationTag $tag
     *
     * @return void
     */
    public function addTag(OrganizationTag $tag)
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }

    /**
     * @return User|null
     */
    public function getResponsible(): ?User
    {
        return $this->responsible;
    }

    /**
     * @param User|null $responsible
     */
    public function setResponsible(?User $responsible): void
    {
        $this->responsible = $responsible;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param ArrayCollection|PersistentCollection $phone
     */
    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     */
    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return string|null
     */
    public function getRegion(): ?string
    {
        return $this->region;
    }

    /**
     * @param string|null $region
     */
    public function setRegion(?string $region): void
    {
        $this->region = $region;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return bool|null
     */
    public function getToRemove(): ?bool
    {
        return $this->to_remove;
    }

    /**
     * @param bool|null $to_remove
     */
    public function setToRemove(?bool $to_remove): void
    {
        $this->to_remove = $to_remove;
    }

    /**
     * @return ArrayCollection|PersistentCollection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @param ArrayCollection|PersistentCollection $projects
     */
    public function setProjects($projects): void
    {
        $this->projects = $projects;
    }

    /**
     * @return DateTime|integer
     */
    public function getCreatedAt($unixtime = false)
    {
        if ($unixtime and $this->created_at instanceof DateTime) {
            return $this->created_at->getTimestamp();
        }
        return $this->created_at;
    }

    /**
     * @param DateTime|null $created_at
     */
    public function setCreatedAt(?DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * @return ArrayCollection|PersistentCollection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param ArrayCollection|PersistentCollection $roles
     */
    public function setRoles($roles): void
    {
        $this->roles = $roles;
    }


}
