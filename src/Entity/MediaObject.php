<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use JMS\Serializer\Annotation\SerializedName;
use App\Repository\MediaObjectRepository;
use Symfony\Component\Validator\Constraints as Asset;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass=MediaObjectRepository::class)
 * @SWG\Definition(
 *     description="Give an object that represent an image."
 * )
 * 
 */
class MediaObject implements Translatable
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @ORM\Id
     */
    protected $id;

    /**
     * @var string
     * 
     * @SerializedName("filePath")
     * @ORM\Column(type="string")
     */
    private $filePath;

    /**
     * @var string|null
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=1024, nullable=true)
     * @SWG\Property(description="The description of image use for alt description.")
     * @Asset\Length(
     *  max=1024,
     *  allowEmptyString = true
     * )
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     * @SWG\Property(description="The user creator")
     * @SerializedName("createdBy")
     */
    private $createdBy;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

    /**
     * @var array|null
     */
    private $translations;

    public function __construct()
    {
        $this->filePath = "";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getTranslations(): ?array
    {
        return $this->translations;
    }

    public function setTranslations(?array $translations): self
    {
        $this->translations = $translations;
        return $this;
    }
}
