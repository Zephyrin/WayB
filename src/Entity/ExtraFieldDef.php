<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Asset;
use App\Enum\TypeExtraFieldEnum;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     description="An ExtraFieldDef give a way to describe an equipment field depending on their type like 'Pant' or 'Camera'"
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ExtraFieldDefRepository")
 */
class ExtraFieldDef
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @SerializedName("id")
     * @SWG\Property(description="The unique identifier of the ExtraFieldDef.",
     *     readOnly=true)
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Asset\GreaterThanOrEqual(TypeExtraFieldEnum::ARRAY)
     * @Asset\LessThanOrEqual(TypeExtraFieldEnum::NUMBER)
     * @SerializedName("type")
     * @SWG\Property(description="The type of the ExtraFieldDef.", enum={TypeExtraFieldEnum::ARRAY, TypeExtraFieldEnum::NUMBER})
     */
    private $type;

    /**
     * @Asset\NotBlank()
     * @ORM\Column(type="string", length=255)
     * @SerializedName("name")
     * @SWG\Property(description="The name of the ExtraFieldDef.")
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     * @SerializedName("isPrice")
     * @SWG\Property(description="If this field is the price or not.")
     */
    private $isPrice;

    /**
     * @ORM\Column(type="boolean")
     * @SerializedName("isWeight")
     * @SWG\Property(description="If this field is the weight or not.")
     */
    private $isWeight;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SubCategory", inversedBy="extraFieldDefs")
     * @ORM\JoinColumn(nullable=false)
     * @Exclude
     * @SWG\Property(type="integer",
     *     readOnly=true,
     *     description="Not used, leave it empty. Swagger problem, not abble to remove this field form the documentation...")
     */
    private $subCategory;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ExtraFieldDef",
     *     inversedBy="extraFieldDefs")
     * @ORM\JoinColumn(name="link_to_id", referencedColumnName="id")
     * @SerializedName("linkTo")
     * @SWG\Property(
     *     type="ExtraFieldDef",
     *     description="Give the other extra field definition which it is link on. For example a price can be link to a weight of type Array.")
     */
    private $linkTo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ExtraFieldDef", mappedBy="linkTo")
     * @Exclude
     * @SWG\Property(type="integer",
     *     readOnly=true,
     *     description="Not used, leave it empty. Swagger problem, not abble to remove this field form the documentation...")
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
