<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;
use Symfony\Component\Validator\Constraints as Asset;
use Swagger\Annotations as SWG;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @SWG\Definition(
 *     description="A Brand give a way to group equipments depending on their brand like 'MSR' or 'Mammut'"
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\BrandRepository")
 * @UniqueEntity(fields="name", message="This brand name is already in use.")
 * @UniqueEntity(fields="uri", message="This brand URI is already in use.")
 */
class Brand extends Base
{
    /**
     * @Asset\NotBlank()
     * @ORM\Column(type="string", length=255, unique=true)
     * @SWG\Property(description="The name of the Brand.")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @SWG\Property(description="The URI of the Brand.")
     */
    private $uri;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Equipment", mappedBy="brand")
     * @Exclude
     */
    private $equipments;

    /**
     * @var MediaObject|null
     *
     * @ORM\ManyToOne(targetEntity=MediaObject::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $logo;

    public function __construct()
    {
        $this->equipments = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;

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
            $equipment->setBrand($this);
        }

        return $this;
    }

    public function removeEquipment(Equipment $equipment): self
    {
        if ($this->equipments->contains($equipment)) {
            $this->equipments->removeElement($equipment);
            // set the owning side to null (unless already changed)
            if ($equipment->getBrand() === $this) {
                $equipment->setBrand(null);
            }
        }

        return $this;
    }

    public function getLogo(): ?MediaObject
    {
        return $this->logo;
    }

    public function setLogo(MediaObject $logo): self
    {
        $this->logo = $logo;

        return $this;
    }
}
