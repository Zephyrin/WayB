<?php
// api/src/Entity/MediaObject.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity
 * 
 * @SWG\Definition(
 *     description="Give an object that represent an image."
 * )
 * 
 */
class MediaObject
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
     * @var string|null
     * 
     * @SerializedName("filePath")
     * @ORM\Column(nullable=true)
     */
    public $filePath;

    /**
     * @var string
     * 
     * @ORM\Column(type="string", length=1024)
     * @SWG\Property(description="The description of the media")
     */
    private $description;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    } 

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }
}