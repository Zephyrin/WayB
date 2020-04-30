<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Asset;
use Swagger\Annotations as SWG;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @SWG\Definition(
 *     description="A Sub-Category give a way to describe an equipment depending on their type like 'Pant' or 'Camera'"
 * )
 * @ORM\Entity(repositoryClass="App\Repository\SubCategoryRepository")
 * @UniqueEntity(fields="name", message="This sub-category name is already in use.")
 */
class SubCategory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @SerializedName("id")
     * @SWG\Property(description="The unique identifier of the Sub-Category.",
     *     readOnly=true)
     */
    private $id;

    /**
     * @Asset\NotBlank()
     * @ORM\Column(type="string", length=255)
     * @SerializedName("name")
     * @SWG\Property(description="The name of the Sub-Category.")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="subCategories")
     * @ORM\JoinColumn(nullable=false)
     * @Exclude
     * @SWG\Property(type="integer",
     *     readOnly=true,
     *     description="Not used, leave it empty. Swagger problem, not abble to remove this field form the documentation...")
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ExtraFieldDef",
     *  mappedBy="subCategory",
     *  orphanRemoval=true,
     *  cascade={"persist"})
     * @SerializedName("extraFieldDefs")
     * @SWG\Property(description="The list of all ExtraFieldDefs link to this Sub-Category.",
     *     readOnly=true)
     */
    private $extraFieldDefs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Equipment",
     *     mappedBy="subCategory",
     *     orphanRemoval=true)
     * @Exclude
     */
    private $equipments;

    public function __construct()
    {
        $this->extraFieldDefs = new ArrayCollection();
        $this->equipments = new ArrayCollection();
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|ExtraFieldDef[]
     */
    public function getExtraFieldDefs(): Collection
    {
        return $this->extraFieldDefs;
    }

    public function addExtraFieldDef(ExtraFieldDef $extraFieldDef): self
    {
        if (!$this->extraFieldDefs->contains($extraFieldDef)) {
            $this->extraFieldDefs[] = $extraFieldDef;
            $extraFieldDef->setSubCategory($this);
        }

        return $this;
    }

    public function removeExtraFieldDef(ExtraFieldDef $extraFieldDef): self
    {
        if ($this->extraFieldDefs->contains($extraFieldDef)) {
            $this->extraFieldDefs->removeElement($extraFieldDef);
            // set the owning side to null (unless already changed)
            if ($extraFieldDef->getSubCategory() === $this) {
                $extraFieldDef->setSubCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Equipment[]
     */
    public function getEquipments(): Collection
    {
        return $this->equipments;
    }

    public function addEquipment(Equipment $equipment): self
    {
        if (!$this->equipments->contains($equipment)) {
            $this->equipments[] = $equipment;
            $equipment->setSubCategory($this);
        }

        return $this;
    }

    public function removeEquipment(Equipment $equipment): self
    {
        if ($this->equipments->contains($equipment)) {
            $this->equipments->removeElement($equipment);
            // set the owning side to null (unless already changed)
            if ($equipment->getSubCategory() === $this) {
                $equipment->setSubCategory(null);
            }
        }

        return $this;
    }
}
