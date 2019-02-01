<?php
namespace App\Entity;

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

    public function __construct()
    {
        parent::__construct();
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
}