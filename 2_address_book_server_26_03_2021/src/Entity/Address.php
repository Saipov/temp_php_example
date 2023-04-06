<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сущность "Адрес"
 *
 * @ORM\Entity(repositoryClass=AddressRepository::class)
 */
class Address
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $addressLine;

    /**
     * @ORM\ManyToMany(targetEntity=AddressBook::class, mappedBy="addresses")
     */
    private $addressBooks;

    /**
     * @ORM\ManyToOne(targetEntity=AddressType::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $addressType;

    public function __construct()
    {
        $this->addressBooks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddressLine(): ?string
    {
        return $this->addressLine;
    }

    public function setAddressLine(string $addressLine): self
    {
        $this->addressLine = $addressLine;

        return $this;
    }

    public function getAddressType(): ?AddressType
    {
        return $this->addressType;
    }

    public function setAddressType(?AddressType $addressType): self
    {
        $this->addressType = $addressType;

        return $this;
    }
}
