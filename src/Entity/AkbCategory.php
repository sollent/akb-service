<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class AkbCategory
 *
 * @ORM\Entity
 */
class AkbCategory extends AbstractEntity
{
    /**
     * @ORM\Column(type="string")
     */
    private string $title;

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
     * @return AkbCategory
     */
    public function setTitle(string $title): AkbCategory
    {
        $this->title = $title;
        return $this;
    }
}
