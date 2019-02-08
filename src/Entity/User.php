<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\SerializedName;
use App\Enum\GenderEnum;
use Swagger\Annotations as SWG;

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

    public function __construct()
    {
        parent::__construct();
        $this->haves = new ArrayCollection();
        // your own logic
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
}