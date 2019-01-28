<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Asset;
use App\Enum\TypeExtraFieldEnum;
/**
 * @ORM\Entity(repositoryClass="App\Repository\ExtraFieldDefRepository")
 */
class ExtraFieldDef
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @SerializedName("id")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Asset\GreaterThanOrEqual(TypeExtraFieldEnum::ARRAY)
     * @Asset\LessThanOrEqual(TypeExtraFieldEnum::NUMBER)
     * @SerializedName("type")
     */
    private $type;

    /**
     * @Asset\NotBlank()
     * @ORM\Column(type="string", length=255)
     * @SerializedName("name")
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     * @SerializedName("isPrice")
     */
    private $isPrice;

    /**
     * @ORM\Column(type="boolean")
     * @SerializedName("isWeight")
     */
    private $isWeight;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SubCategory", inversedBy="extraFieldDefs")
     * @ORM\JoinColumn(nullable=false)
     * @Exclude
     */
    private $subCategory;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ExtraFieldDef",
     *     inversedBy="extraFieldDefs")
     * @SerializedName("linkTo")
     */
    private $linkTo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ExtraFieldDef", mappedBy="linkTo")
     * @Exclude
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        if (!in_array($type, TypeExtraFieldEnum::getAvailableTypes())) {
            throw new \InvalidArgumentException("Invalid type");
        }
        $this->type = $type;

        return $this;
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

    public function getIsPrice(): ?bool
    {
        return $this->isPrice;
    }

    public function setIsPrice(bool $isPrice): self
    {
        $this->isPrice = $isPrice;

        return $this;
    }

    public function getIsWeight(): ?bool
    {
        return $this->isWeight;
    }

    public function setIsWeight(bool $isWeight): self
    {
        $this->isWeight = $isWeight;

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

    public function getLinkTo(): ?self
    {
        return $this->linkTo;
    }

    public function setLinkTo(?self $linkTo): self
    {
        $this->linkTo = $linkTo;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getExtraFieldDefs(): Collection
    {
        return $this->extraFieldDefs;
    }

    public function addExtraFieldDef(self $extraFieldDef): self
    {
        if (!$this->extraFieldDefs->contains($extraFieldDef)) {
            $this->extraFieldDefs[] = $extraFieldDef;
            $extraFieldDef->setLinkTo($this);
        }

        return $this;
    }

    public function removeExtraFieldDef(self $extraFieldDef): self
    {
        if ($this->extraFieldDefs->contains($extraFieldDef)) {
            $this->extraFieldDefs->removeElement($extraFieldDef);
            // set the owning side to null (unless already changed)
            if ($extraFieldDef->getLinkTo() === $this) {
                $extraFieldDef->setLinkTo(null);
            }
        }

        return $this;
    }
}
