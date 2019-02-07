<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Asset;

/**
 * @SWG\Definition(
 *     description="An Equipment give a way to describe an equipment with a minimum of caracteristic."
 * )
 * @ORM\Entity(repositoryClass="App\Repository\EquipmentRepository")
 */
class Equipment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @SerializedName("id")
     * @var int
     * @SWG\Property(description="The unique identifier of the Equipment.",
     *     readOnly=true)
     */
    private $id;

    /**
     * @Asset\NotBlank()
     * @ORM\Column(type="string", length=255)
     * @SerializedName("name")
     * @var string
     * @SWG\Property(description="The name of the Equipment.")
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @var string
     * @SWG\Property(description="A description about the Equipment.")
     */
    private $description;

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
     * @ORM\OneToMany(targetEntity="App\Entity\ExtraField",
     *     mappedBy="equipment",
     *     orphanRemoval=true)
     * @SWG\Property(description="Specific fields of the Equipment.")
     * @SerializedName("extraFields")
     */
    private $extraFields;

    public function __construct()
    {
        $this->extraFields = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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
     * @return Collection|ExtraField[]
     */
    public function getExtraFields(): Collection
    {
        return $this->extraFields;
    }

    public function addExtraField(ExtraField $extraField): self
    {
        if (!$this->extraFields->contains($extraField)) {
            $this->extraFields[] = $extraField;
            $extraField->setEquipment($this);
        }

        return $this;
    }

    public function removeExtraField(ExtraField $extraField): self
    {
        if ($this->extraFields->contains($extraField)) {
            $this->extraFields->removeElement($extraField);
            // set the owning side to null (unless already changed)
            if ($extraField->getEquipment() === $this) {
                $extraField->setEquipment(null);
            }
        }

        return $this;
    }
}
