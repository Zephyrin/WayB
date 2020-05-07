<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Asset;
use Swagger\Annotations as SWG;
use App\Enum\GenderEnum;
use JMS\Serializer\Annotation\Exclude;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CharacteristicRepository")
 */
class Characteristic extends Base
{
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
     * @ORM\Column(type="float")
     * @SerializedName("price")
     * @SWG\Property(description="The price of the Characteristic.")
     * @var float
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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Have",
     *     mappedBy="characteristic",
     *     orphanRemoval=true,
     *     cascade={"persist"})
     * @Exclude
     * @SWG\Property(description="List of all users that own this equipment characteristic.")
     * @SerializedName("haves")
     */
    private $haves;

    public function __construct()
    {
        $this->haves = new ArrayCollection();
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
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

    /**
     * @return Collection|Have[]
     */
    public function getHaves(): Collection
    {
        return $this->haves;
    }

    public function addHave($have): self
    {
        if (!$this->haves->contains($have)) {
            $this->haves[] = $have;
            $have->setCharacteristic($this);
        }

        return $this;
    }

    public function removeHave($have): self
    {
        if ($this->haves->contains($have)) {
            $this->haves->removeElement($have);
            // set the owning side to null (unless already changed)
            if ($have->getCharacteristic() === $this) {
                $have->setCharacteristic(null);
            }
        }
        return $this;
    }
}
