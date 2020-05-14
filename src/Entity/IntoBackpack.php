<?php

namespace App\Entity;

use App\Repository\IntoBackpackRepository;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity(repositoryClass=IntoBackpackRepository::class)
 */
class IntoBackpack
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $count;

    /**
     * @ORM\ManyToOne(targetEntity=Have::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $equipment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function getBackpack(): ?Backpack
    {
        return $this->backpack;
    }

    public function setBackpack(?Backpack $backpack): self
    {
        $this->backpack = $backpack;
        return $this;
    }

    public function getEquipment(): ?Have
    {
        return $this->equipment;
    }

    public function setEquipment(?Have $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }
}
