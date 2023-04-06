<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CatalogRepository")
 * @Vich\Uploadable
 */
class Catalog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("main")
     * @Groups("full")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("main")
     * @Groups("full")
     */
    private $title;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_active;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups("main")
     */
    private $is_new;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups("main")
     */
    private $is_sale;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups("full")
     */
    private $anonce;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups("full")
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Categories", inversedBy="catalogs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("main")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Productions", inversedBy="catalogs")
     * @Groups("full")
     */
    private $productions;


    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="catalog_image", fileNameProperty="imageName")
     *
     * @var File|null
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups("full")
     * @Groups("main")
     * @var string|null
     */
    private $imageName;


    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("main")
     * @Groups("full")
     */
    private $packing;

    /**
     * @ORM\Column(type="integer")
     * @Groups("main")
     * @Groups("full")
     */
    private $price;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(?bool $is_active): self
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getIsNew(): ?bool
    {
        return $this->is_new;
    }

    public function setIsNew(?bool $is_new): self
    {
        $this->is_new = $is_new;

        return $this;
    }

    public function getIsSale(): ?bool
    {
        return $this->is_sale;
    }

    public function setIsSale(?bool $is_sale): self
    {
        $this->is_sale = $is_sale;

        return $this;
    }

    public function getAnonce(): ?string
    {
        return $this->anonce;
    }

    public function setAnonce(?string $anonce): self
    {
        $this->anonce = $anonce;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCategory(): ?Categories
    {
        return $this->category;
    }

    public function setCategory(?Categories $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getProductions(): ?Productions
    {
        return $this->productions;
    }

    public function setProductions(?Productions $productions): self
    {
        $this->productions = $productions;

        return $this;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function getPacking(): ?string
    {
        return $this->packing;
    }

    public function setPacking(string $packing): self
    {
        $this->packing = $packing;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }
}
