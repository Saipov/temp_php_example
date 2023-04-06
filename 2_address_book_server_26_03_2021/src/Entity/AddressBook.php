<?php

namespace App\Entity;

use App\Repository\AddressBookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AddressBookRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class AddressBook
{
    /**
     * Hook SoftDeleteable behavior
     * updates deletedAt field
     */
    use SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Имя
     *
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank(message="addressbook.first_name.not_blank")
     * @Assert\Length(
     *      min = 2,
     *      max = 20,
     *      minMessage = "",
     *      maxMessage = ""
     * )
     */
    private $firstName;

    /**
     * Фамилия
     *
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank(message="addressbook.last_name.not_blank")
     */
    private $lastName;

    /**
     * Отчество
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $middleName;

    /**
     * Почта
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Assert\Email(message="addressbook.email.not_valid")
     */
    private $email;

    /**
     * День рождения
     *
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date(message="addressbook.date.not_valid")
     */
    private $dob;

    /**
     * Примечание
     *
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "addressbook.desc.max_lenght"
     * )
     */
    private $description;

    /**
     * Телефоны
     *
     * @ORM\ManyToMany(targetEntity=Phone::class, inversedBy="addressBooks", cascade={"persist"})
     */
    private $phones;

    /**
     * Адреса
     *
     * @ORM\ManyToMany(targetEntity=Address::class, inversedBy="addressBooks", cascade={"persist"})
     */
    private $addresses;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    public function __construct()
    {
        $this->phones = new ArrayCollection();
        $this->addresses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function setMiddleName(?string $middleName): self
    {
        $this->middleName = $middleName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getDob(): ?\DateTimeInterface
    {
        return $this->dob;
    }

    public function setDob(?\DateTimeInterface $dob): self
    {
        $this->dob = $dob;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Phone[]
     */
    public function getPhones(): Collection
    {
        return $this->phones;
    }

    public function addPhone(Phone $phone): self
    {
        if (!$this->phones->contains($phone)) {
            $this->phones[] = $phone;
        }

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        $this->phones->removeElement($phone);

        return $this;
    }

    /**
     * @return Collection|Address[]
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses[] = $address;
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        $this->addresses->removeElement($address);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
