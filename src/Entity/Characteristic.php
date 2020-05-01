<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Asset;
use Swagger\Annotations as SWG;
use App\Enum\GenderEnum;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CharacteristicRepository")
 */
class Characteristic
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @SerializedName("id")
     * @SWG\Property(description="The unique identifier of the Characteristic.",
     *     readOnly=true)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=6)
     * @SerializedName("gender")
     * @SWG\Property(
     *     description="The genre of the Characteristic."
     *     , enum={GenderEnum::FEMALE, GenderEnum::MALE, GenderEnum::UNISEX})
     */
    private $gender;

    /**
     * @Asset\NotNull()
     * @Asset\NotBlank()
     * @ORM\Column(type="string", length=25)
     * @SerializedName("size")
     * @SWG\Property(
     *     description="The size of the Characteristic.")
     */
    private $size;

    /**
     * @Asset\NotNull()
     * @Asset\NotBlank()
     * @ORM\Column(type="integer")
     * @SerializedName("price")
     * @SWG\Property(description="The price of the Characteristic.")
     * @var integer
     */
    private $price;

    /**
     * @Asset\NotNull()
     * @Asset\NotBlank()
     * @ORM\Column(type="integer")
     * @SerializedName("weight")
     * @SWG\Property(description="The weight of the Characteristic.")
     * @var integer
     */
    private $weight;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Equipment", inversedBy="characteristics")
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

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;
        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize($size): self
    {
        $this->size = $size;
        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;
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
