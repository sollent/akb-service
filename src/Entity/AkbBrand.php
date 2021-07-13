<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class AkbBrand
 *
 * @ORM\Entity
 */
class AkbBrand extends AbstractEntity
{
    /**
     * @ORM\Column(type="string")
     */
    private string $title;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AkbCategory")
     */
    protected AkbCategory $category;

    public function __toString(): string
    {
        return $this->title;
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
     *
     * @return AkbBrand
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return AkbCategory
     */
    public function getCategory(): AkbCategory
    {
        return $this->category;
    }

    /**
     * @param AkbCategory $category
     * @return AkbBrand
     */
    public function setCategory(AkbCategory $category): AkbBrand
    {
        $this->category = $category;
        return $this;
    }
}
