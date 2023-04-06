<?php

namespace App\Entity;

use App\Repository\PhoneTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сущность "Типы телефонных номеров"
 * Например: Домашний, Рабочий
 *
 * @ORM\Entity(repositoryClass=PhoneTypeRepository::class)
 */
class PhoneType
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $phoneType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhoneType(): ?string
    {
        return $this->phoneType;
    }

    public function setPhoneType(string $phoneType): self
    {
        $this->phoneType = $phoneType;

        return $this;
    }
}
