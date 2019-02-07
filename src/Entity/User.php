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

    public function addHafe(Have $hafe): self
    {
        if (!$this->haves->contains($hafe)) {
            $this->haves[] = $hafe;
            $hafe->setUser($this);
        }

        return $this;
    }

    public function removeHafe(Have $hafe): self
    {
        if ($this->haves->contains($hafe)) {
            $this->haves->removeElement($hafe);
            // set the owning side to null (unless already changed)
            if ($hafe->getUser() === $this) {
                $hafe->setUser(null);
            }
        }

        return $this;
    }
}