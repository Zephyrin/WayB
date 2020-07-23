<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Asset;
use JMS\Serializer\Annotation\SerializedName;
use Swagger\Annotations as SWG;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @SWG\Definition(
 *     description="A Category give a way to manage equipment depending on the category like 'Clothe' or 'Cooking'"
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @UniqueEntity(fields="name", message="This category name is already in use.")
 */
class Category extends Base implements Translatable
{
    /**
     * @var string|null
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255)
     * @SerializedName("name")
     * @Asset\Length(max=255, allowEmptyString=true)
     * @SWG\Property(description="The name of the Category.")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SubCategory",
     *     mappedBy="category",
     *     orphanRemoval=true,
     *     cascade={"persist"})
     * @SerializedName("subCategories")
     * @var SubCategory
     * @SWG\Property(description="The list of all SubCategory link to this Category")
     */
    private $subCategories;

    /**
     * @var array|null
     */
    private $translations;


    public function __construct()
    {
        parent::__construct();
        $this->subCategories = new ArrayCollection();
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
