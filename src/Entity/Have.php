<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\SerializedName;
use Swagger\Annotations as SWG;
use JMS\Serializer\Annotation\Exclude;
use Symfony\Component\Validator\Constraints as Asset;

/**
 * @SWG\Definition(
 *     description="A Have give a way to manage user's equipment.
 * An user will create or use an equipment. He can have some quantity and whish more."
 * )
 * @ORM\Entity(repositoryClass="App\Repository\HaveRepository")
 */
class Have
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @SerializedName("id")
     * @var int
     * @SWG\Property(description="The unique identifier of the Category",
     *     readOnly=true)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="haves")
     * @ORM\JoinColumn(nullable=false)
     * @Exclude()
     * @SWG\Property(description="The User",
     *     type="Object::class")
     */
    private $user;

    /**
     * @Asset\NotNull()
     * @Asset\NotBlank()
     * @ORM\Column(type="integer")
     * @SerializedName("ownQuantity")
     * @var integer
     * @SWG\Property(description="The number of equipment that the user owns")
     */
    private $ownQuantity;

    /**
     * @Asset\NotNull()
     * @Asset\NotBlank()
     * @ORM\Column(type="integer")
     * @SerializedName("wantQuantity")
     * @var integer
     * @SWG\Property(description="The number of equipment that the user want for a later use")
     */
    private $wantQuantity;

    /**
     * @Asset\NotNull()
     * @ORM\ManyToOne(targetEntity="App\Entity\Equipment", inversedBy="haves")
     * @ORM\JoinColumn(nullable=false)
     * @SerializedName("equipment")
     * @SWG\Property(description="The equipment information")
     */
    private $equipment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Characteristic", inversedBy="haves")
     * @SerializedName("characteristic")
     * @SWG\Property(description="The characteristic linked to the equipment that the user want or have")
     */
    private $characteristic;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getOwnQuantity(): ?int
    {
        return $this->ownQuantity;
    }

    public function setOwnQuantity(int $ownQuantity): self
    {
        $this->ownQuantity = $ownQuantity;

        return $this;
    }

    public function getWantQuantity(): ?int
    {
        return $this->wantQuantity;
    }

    public function setWantQuantity(int $wantQuantity): self
    {
        $this->wantQuantity = $wantQuantity;

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

    public function getCharacteristic(): ?Characteristic
    {
        return $this->characteristic;
    }

    public function setCharacteristic(?Characteristic $characteristic): self
    {
        $this->characteristic = $characteristic;
        return $this;
    }
}
