<?php
namespace DRKTettnang\OperationHistory\Domain\Model;

/*
 * This file is part of the DRKTettnang.OperationHistory package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Flow\Entity
 */
class Operation
{
    /**
     * @Flow\Inject
     *
     * @var \Neos\Media\Domain\Repository\AssetRepository
     */
    protected $assetRepository;

    /**
    * @Flow\Inject
    *
    * @var \Neos\Flow\ResourceManagement\ResourceManager
    */
    protected $resourceManager;

    /**
    * @Flow\Inject
    *
    * @var \DRKTettnang\OperationHistory\Domain\Repository\OperationRepository
    */
    protected $operationRepository;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $description;

    /**
     * @var \DRKTettnang\OperationHistory\Domain\Model\OperationType
     * @ORM\ManyToOne
     */
    protected $type;

    /**
     * @var string
     */
    protected $location;

    /**
    * @var \Doctrine\Common\Collections\Collection<\DRKTettnang\OperationHistory\Domain\Model\OperationBos>
    * @ORM\ManyToMany
    */
    protected $bos;

    /**
     * @var string
     */
    protected $author;

    /**
     * @var \DateTime
     * @Flow\Validate(type="NotEmpty")
     */
    protected $date;

    /**
     * @var integer
     */
    protected $year;

    /**
     * @var string
     */
    protected $fb_post_id;

    /**
     * @ORM\Column(type="flow_json_array")
     * @var array<string>
     */
    protected $images;//\TYPO3\Media\Domain\Model\Image

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     * @return void
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getBos()
    {
        return $this->bos;
    }

    /**
     * @param string $bos
     * @return void
     */
    public function setBos($bos)
    {
        $this->bos = $bos;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $author
     * @return void
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return void
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param integer $date
     * @return void
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return string
     */
    public function getFb_post_id()
    {
        return $this->fb_post_id;
    }

    /**
     * @param string $fb_post_id
     * @return void
     */
    public function setFb_post_id($fb_post_id)
    {
        $this->fb_post_id = $fb_post_id;
    }

    public function getJsonImages() {
      return json_encode($this->images);
   }

    /**
     * @return array<\Neos\Media\Domain\Model\Image>
     */
    public function getImages()
    {
      $images = array();

      if (is_array($this->images)) {
         foreach ($this->images as $identifier) {
            $asset = $this->assetRepository->findByIdentifier($identifier);

            if ($asset !== null) {
               $images[] = $asset;
            }
         }
      }
        return $images;
    }

    /**
     * @param array<string> $images
     * @return void
     */
    public function setImages($images)
    {
        $this->images = $images;
    }

    /**
     * @return integer Number of this operation
     */
    public function getNumber() {
      return $this->operationRepository->getNumber($this);
   }
}
