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
 *     description="Base for each component of the project."
 * )
 */

class Base 
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
  protected $id;

  /**
   * @ORM\ManyToOne(targetEntity="App\Entity\User")
   * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
   * @SWG\Property(description="The user creator")
   * @SerializedName("createdBy")
   */
  protected $createdBy;

  /**
   * @ORM\Column(type="boolean", options={"default" : 0})
   * @SerializedName("validate")
   * @var boolean
   * @SWG\Property(description="The entity is validate by ambassador's user and can be used by other user.")
   */
  protected $validate = false;

  /**
   * @ORM\Column(type="boolean", options={"default" : 0})
   * @SerializedName("askValidate")
   * @var boolean
   * @SWG\Property(description="The entity can be ask to be validate by an ambassador.")
   */
  protected $askValidate = false;

  public function __construct()
  {
    
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getValidate(): ?bool
  {
    return $this->validate;
  }

  public function setValidate(bool $validate): self
  {
    $this->validate = $validate;

    return $this;
  }

  public function getAskValidate(): ?bool
  {
    return $this->askValidate;
  }

  public function setAskValidate(bool $askValidate): self
  {
    $this->askValidate = $askValidate;

    return $this;
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
}