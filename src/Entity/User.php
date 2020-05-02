<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\SerializedName;
use App\Enum\GenderEnum;
use Swagger\Annotations as SWG;
use JMS\Serializer\Annotation\Exclude;
// Contraints for BaseUser class
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=6, nullable=false )
     * @SerializedName("gender")
     * @SWG\Property(
     *     description="The gender of the user."
     *     , enum={GenderEnum::MALE, GenderEnum::FEMALE})
     * @var string
     */
    protected $gender;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Have", mappedBy="user", orphanRemoval=true)
     * @SWG\Property(
     *     description="The list of what the user has."
     *     , type="array::class")
     */
    private $haves;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Equipment", mappedBy="createdBy", orphanRemoval=true)
     * @Exclude()
     * @SWG\Property(
     *     description="The list of what the user created."
     *     , type="array::class")
     */
    private $equipments;

    public function __construct()
    {
        parent::__construct();
        $this->haves = new ArrayCollection();
        // your own logic
    }

    /**
     * Set constrainte on BaseUser class
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        //$metadata->addPropertyConstraint('username', new Assert\Unique());
        $metadata->addConstraint(new UniqueEntity([
            'fields' => 'username',
        ]));
        $metadata->addPropertyConstraint('username', new Assert\NotBlank([
            'payload' => ['severity' => 'error'],
        ]));
        $metadata->addPropertyConstraint('email', new Assert\NotBlank([
            'payload' => ['severity' => 'error'],
        ]));
        $metadata->addConstraint(new UniqueEntity([
            'fields' => 'email',
        ]));
        $metadata->addPropertyConstraint('email', new Assert\Email());
        $metadata->addPropertyConstraint('password', new Assert\NotBlank([
            'payload' => ['severity' => 'error'],
        ]));
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        if (!in_array($gender, GenderEnum::getAvailableTypes())) {
            throw new \InvalidArgumentException("Invalid type");
        }
        $this->gender = $gender;

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
            $have->setUser($this);
        }

        return $this;
    }

    public function removeHave(Have $have): self
    {
        if ($this->haves->contains($have)) {
            $this->haves->removeElement($have);
            // set the owning side to null (unless already changed)
            if ($have->getUser() === $this) {
                $have->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Equipment[]
     */
    public function getEquimpents(): Collection
    {
        return $this->equipments;
    }

    public function addEquipment(Equipment $equipment): self
    {
        if (!$this->equipments->contains($equipment)) {
            $this->equipments[] = $equipment;
            $equipment->setCreatedBy($this);
        }

        return $this;
    }

    public function removeEquipment(Equipment $equipment): self
    {
        if ($this->haves->contains($equipment)) {
            $this->haves->removeElement($equipment);
            // set the owning side to null (unless already changed)
            if ($equipment->getCreatedBy() === $this) {
                $equipment->setCreatedBy(null);
            }
        }

        return $this;
    }
}