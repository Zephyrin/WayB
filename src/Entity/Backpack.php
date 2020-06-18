<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BackpackRepository")
 * 
 * @SWG\Definition(
 *  description="A backpack is what the user bring with him for a hike.")
 */
class Backpack
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @SWG\Property(description="The unique identifier of the backpack", 
     *  readOnly=true)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @SWG\Property(description="The name of the backpack")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\IntoBackpack",
     *  mappedBy="backpack",
     *  orphanRemoval=true,
     *  cascade={"persist"})
     * @SerializedName("intoBackpacks")
     * @SWG\Property(description="The number of equipment the user put into the backpack")
     */
    private $intoBackpacks;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     * @SWG\Property(description="The user creator")
     * @SerializedName("createdBy")
     */
    protected $createdBy;

    public function __construct()
    {
        $this->intoBackpacks = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|IntoBackpack[]
     */
    public function getIntoBackpacks(): Collection
    {
        return $this->intoBackpacks;
    }

    public function addIntoBackpack($intoBackpack): self
    {
        if (!$this->intoBackpacks->contains($intoBackpack)) {
            $this->intoBackpacks[] = $intoBackpack;
            $intoBackpack->setEquipment($this);
        }

        return $this;
    }

    public function removeIntoBackpack($intoBackpack): self
    {
        if ($this->intoBackpacks->contains($intoBackpack)) {
            $this->intoBackpacks->removeElement($intoBackpack);
            // set the owning side to null (unless already changed)
            if ($intoBackpack->getEquipment() === $this) {
                $intoBackpack->setEquipment(null);
            }
        }

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
