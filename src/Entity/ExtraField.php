<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Asset;
use App\Enum\TypeExtraFieldEnum;
use Swagger\Annotations as SWG;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExtraFieldRepository")
 */
class ExtraField
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @SerializedName("id")
     * @SWG\Property(description="The unique identifier of the ExtraField.",
     *     readOnly=true)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=6)
     * @SerializedName("type")
     * @SWG\Property(
     *     description="The type of the ExtraField."
     *     , enum={TypeExtraFieldEnum::ARRAY, TypeExtraFieldEnum::NUMBER})
     */
    private $type;

    /**
     * @ORM\Column(type="object")
     * @SerializedName("value")
     * @SWG\Property(
     *     type="Object::class",
     *     description="The value of the ExtraField.")
     */
    private $value;

    /**
     * @Asset\NotBlank()
     * @ORM\Column(type="string", length=255)
     * @SerializedName("name")
     * @SWG\Property(description="The name of the ExtraField.")
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     * @SerializedName("isWeight")
     * @SWG\Property(description="If this field is the weight or not.")
     */
    private $isWeight;

    /**
     * @ORM\Column(type="boolean")
     * @SerializedName("isPrice")
     * @SWG\Property(description="If this field is the price or not.")
     */
    private $isPrice;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Equipment", inversedBy="extraFields")
     * @ORM\JoinColumn(nullable=false)
     * @Exclude
     * @SWG\Property(type="integer",
     *     readOnly=true,
     *     description="Not used, leave it empty. Swagger problem, not abble to remove this field form the documentation...")
     */
    private $equipment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): self
    {
        $this->value = $value;

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

    public function getIsWeight(): ?bool
    {
        return $this->isWeight;
    }

    public function setIsWeight(bool $isWeight): self
    {
        $this->isWeight = $isWeight;

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

    public function getEquipment(): ?Equipment
    {
        return $this->equipment;
    }

    public function setEquipment(?Equipment $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }
}
