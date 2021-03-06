<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Asset;
use JMS\Serializer\Annotation\Exclude;

/**
 * @SWG\Definition(
 *     description="An Equipment give a way to describe an equipment with a minimum of caracteristic."
 * )
 * @ORM\Entity(repositoryClass="App\Repository\EquipmentRepository")
 */
class Equipment extends Base
{
    /**
     * @Asset\NotBlank()
     * @ORM\Column(type="string", length=255)
     * @SerializedName("name")
     * @var string
     * @SWG\Property(description="The name of the Equipment.")
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @SerializedName("linkToManufacturer")
     * @var string
     * @SWG\Property(description="The link of the equipment to the manufacturer website.")
     */
    private $linkToManufacturer = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SubCategory"
     *   , inversedBy="equipments")
     * @ORM\JoinColumn(nullable=false)
     * @SWG\Property(description="The SubCategory that this Equipment belong to.")
     * @SerializedName("subCategory")
     */
    private $subCategory;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Brand", inversedBy="equipments")
     * @SWG\Property(description="The brand of the Equipment.")
     */
    private $brand;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Characteristic",
     *     mappedBy="equipment",
     *     orphanRemoval=true,
     *     cascade={"persist"})
     * @SWG\Property(description="Specific fields of the Equipment.")
     * @SerializedName("characteristics")
     */
    private $characteristics;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Have", mappedBy="equipment")
     * @Exclude()
     */
    private $haves;

    public function __construct()
    {
        parent::__construct();
        $this->characteristics = new ArrayCollection();
        $this->haves = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLinkToManufacturer(): ?string
    {
        return $this->linkToManufacturer;
    }

    public function setLinkToManufacturer(?string $linkToManufacturer): self
    {
        $this->linkToManufacturer = $linkToManufacturer;

        return $this;
    }

    public function getSubCategory(): ?SubCategory
    {
        return $this->subCategory;
    }

    public function setSubCategory(?SubCategory $subCategory): self
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return Collection|Characteristic[]
     */
    public function getCharacteristics(): Collection
    {
        return $this->characteristics;
    }

    public function addCharacteristic($characteristic): self
    {
        if (!$this->characteristics->contains($characteristic)) {
            $this->characteristics[] = $characteristic;
            $characteristic->setEquipment($this);
        }

        return $this;
    }

    public function removeCharacteristic($characteristic): self
    {
        if ($this->characteristics->contains($characteristic)) {
            $this->characteristics->removeElement($characteristic);
            // set the owning side to null (unless already changed)
            if ($characteristic->getEquipment() === $this) {
                $characteristic->setEquipment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Have[]
     */
    public function getHaves(): Collection
    {
        return $this->haves;
    }

    public function addHave(Have $have): self
    {
        if (!$this->haves->contains($have)) {
            $this->haves[] = $have;
            $have->setEquipment($this);
        }

        return $this;
    }

    public function removeHave(Have $have): self
    {
        if ($this->haves->contains($have)) {
            $this->haves->removeElement($have);
            // set the owning side to null (unless already changed)
            if ($have->getEquipment() === $this) {
                $have->setEquipment(null);
            }
        }

        return $this;
    }
}
