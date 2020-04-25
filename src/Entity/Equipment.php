<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use JMS\Serializer\Annotation\SerializedName;
use phpDocumentor\Reflection\Types\Boolean;
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
     *     orphanRemoval=true,
     *     cascade={"persist"})
     * @SWG\Property(description="Specific fields of the Equipment.")
     * @SerializedName("extraFields")
     */
    private $extraFields;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Have", mappedBy="equipment")
     */
    private $haves;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="equipments")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     * @SWG\Property(description="The User",
     *     type="Object::class")
     */
    private $createdBy;

    /**
     * @ORM\Column(type="boolean")
     * @SerializedName("validate")
     * @var boolean
     * @SWG\Property(description="The equipment is validate by ambassador's user and can be used by other user")
     */
    private $validate;

    public function __construct()
    {
        $this->extraFields = new ArrayCollection();
        $this->haves = new ArrayCollection();
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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getValidate(): ?bool
    {
        return $this->validate;
    }

    public function setValidate(bool $validate): self
    {
        $this->validate = $validate;

        return $this;
    }
}
