<?php

namespace App\Entity;

use App\Repository\PhoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сущность "Телефонные номера"
 *
 * @ORM\Entity(repositoryClass=PhoneRepository::class)
 */
class Phone
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $phoneNumber;

    /**
     * @ORM\ManyToMany(targetEntity=AddressBook::class, mappedBy="phones")
     */
    private $addressBooks;

    /**
     * @ORM\ManyToOne(targetEntity=PhoneType::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $phoneType;

    public function __construct()
    {
        $this->addressBooks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getPhoneType(): ?PhoneType
    {
        return $this->phoneType;
    }

    public function setPhoneType(?PhoneType $phoneType): self
    {
        $this->phoneType = $phoneType;

        return $this;
    }
}
