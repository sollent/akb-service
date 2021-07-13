<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class AkbEntity
 *
 * @ORM\Entity
 */
class AkbEntity extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AkbCategory")
     */
    private ?AkbCategory $category = null;

    /**
     * @ORM\Column(type="string")
     */
    private string $title;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $discountPrice = null;

    /**
     * @ORM\Column(type="integer")
     */
    private int $price;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $shortDescription = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $photoPath = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $imageUrlPath = null;

    public function __toString(): string
    {
        return $this->title;
    }

    /**
     * @return AkbCategory|null
     */
    public function getCategory(): ?AkbCategory
    {
        return $this->category;
    }

    /**
     * @param AkbCategory|null $category
     * @return AkbEntity
     */
    public function setCategory(?AkbCategory $category): AkbEntity
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return AkbEntity
     */
    public function setTitle(string $title): AkbEntity
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getDiscountPrice(): ?int
    {
        return $this->discountPrice;
    }

    /**
     * @param int|null $discountPrice
     * @return AkbEntity
     */
    public function setDiscountPrice(?int $discountPrice): AkbEntity
    {
        $this->discountPrice = $discountPrice;
        return $this;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     * @return AkbEntity
     */
    public function setPrice(int $price): AkbEntity
    {
        $this->price = $price;
        return $this;
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
     * @return AkbEntity
     */
    public function setDescription(?string $description): AkbEntity
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhotoPath(): ?string
    {
        return $this->photoPath;
    }

    /**
     * @param string|null $photoPath
     * @return AkbEntity
     */
    public function setPhotoPath(?string $photoPath): AkbEntity
    {
        $this->photoPath = $photoPath;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    /**
     * @param string|null $shortDescription
     *
     * @return AkbEntity
     */
    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageUrlPath(): ?string
    {
        return $this->imageUrlPath;
    }

    /**
     * @param string|null $imageUrlPath
     */
    public function setImageUrlPath(?string $imageUrlPath): void
    {
        $this->imageUrlPath = $imageUrlPath;
    }
}
