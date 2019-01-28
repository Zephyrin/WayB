<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Asset;
use JMS\Serializer\Annotation\SerializedName;
use Swagger\Annotations as SWG;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @SWG\Definition(
 *     description="A Category give a way to manage equipment depending on the category like 'Clothe' or 'Cooking'"
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @UniqueEntity(fields="name", message="This category name is already in use.")
 */
class Category
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @SerializedName("id")
     * @var int
     * @SWG\Property(description="The unique identifier of the Category.",
     *     readOnly=true)
     */
    private $id;

    /**
     * @Asset\NotBlank()
     * @ORM\Column(type="string", length=255, unique=true)
     * @SerializedName("name")
     * @var string
     * @SWG\Property(description="The name of the Category.")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SubCategory", mappedBy="category", orphanRemoval=true)
     * @SerializedName("subCategories")
     * @var SubCategory
     * @SWG\Property(description="The list of all SubCategory link to this Category.")
     */
    private $subCategories;

    public function __construct()
    {
        $this->subCategories = new ArrayCollection();
    }

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

    /**
     * @return Collection|SubCategory[]
     */
    public function getSubCategories(): Collection
    {
        return $this->subCategories;
    }

    public function addSubCategory(SubCategory $subCategory): self
    {
        if (!$this->subCategories->contains($subCategory)) {
            $this->subCategories[] = $subCategory;
            $subCategory->setCategory($this);
        }

        return $this;
    }

    public function removeSubCategory(SubCategory $subCategory): self
    {
        if ($this->subCategories->contains($subCategory)) {
            $this->subCategories->removeElement($subCategory);
            // set the owning side to null (unless already changed)
            if ($subCategory->getCategory() === $this) {
                $subCategory->setCategory(null);
            }
        }

        return $this;
    }
}
