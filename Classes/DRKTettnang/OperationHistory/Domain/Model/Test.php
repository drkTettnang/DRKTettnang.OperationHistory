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
class Test
{

    /**
     * @var string
     */
    protected $name;


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

}
