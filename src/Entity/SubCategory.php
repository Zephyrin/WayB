<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Asset;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubCategoryRepository")
 */
class SubCategory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @SerializedName("id")
     */
    private $id;

    /**
     * @Asset\NotBlank()
     * @ORM\Column(type="string", length=255)
     * @SerializedName("name")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="subCategories")
     * @ORM\JoinColumn(nullable=false)
     * @Exclude
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ExtraFieldDef", mappedBy="subCategory", orphanRemoval=true)
     * @SerializedName("extraFieldDefs")
     */
    private $extraFieldDefs;

    public function __construct()
    {
        $this->extraFieldDefs = new ArrayCollection();
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
}
