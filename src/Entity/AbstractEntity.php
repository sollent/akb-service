<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractEntity
 */
abstract class AbstractEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected ?int $id = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return AbstractEntity
     */
    public function setId(?int $id): AbstractEntity
    {
        $this->id = $id;
        return $this;
    }
}
