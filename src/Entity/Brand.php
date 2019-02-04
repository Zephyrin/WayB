<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Asset;
use Swagger\Annotations as SWG;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @SWG\Definition(
 *     description="A Brand give a way to group equipments depending on their brand like 'MSR' or 'Mammut'"
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\BrandRepository")
 * @UniqueEntity(fields="name", message="This brand name is already in use.")
 * @UniqueEntity(fields="uri", message="This brand URI is already in use.")
 */
class Brand
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @SWG\Property(description="The unique identifier of the Category.",
     *     readOnly=true)
     */
    private $id;

    /**
     * @Asset\NotBlank()
     * @ORM\Column(type="string", length=255, unique=true)
     * @SWG\Property(description="The name of the Brand.")
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @SWG\Property(description="The description of the Brand.")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @SWG\Property(description="The URI of the Brand.")
     */
    private $uri;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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
}
