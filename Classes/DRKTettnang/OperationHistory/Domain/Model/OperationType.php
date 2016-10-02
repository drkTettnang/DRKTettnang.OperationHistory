<?php
namespace DRKTettnang\OperationHistory\Domain\Model;

/*
 * This file is part of the DRKTettnang.OperationHistory package.
 */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class OperationType
{

    /**
     * @var string
     * @Flow\Validate(type="NotEmpty")
     */
    protected $label;

    /**
     * @var string
     * @Flow\Identity
     * @Flow\Validate(type="NotEmpty")
     */
    protected $id;

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

}
