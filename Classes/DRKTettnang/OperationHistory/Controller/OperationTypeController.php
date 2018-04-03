<?php
namespace DRKTettnang\OperationHistory\Controller;

/*
 * This file is part of the DRKTettnang.OperationHistory package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use DRKTettnang\OperationHistory\Domain\Model\OperationType;

class OperationTypeController extends ActionController
{

    /**
     * @Flow\Inject
     * @var \DRKTettnang\OperationHistory\Domain\Repository\OperationTypeRepository
     */
    protected $operationTypeRepository;

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->view->assign('operationTypes', $this->operationTypeRepository->findAll());
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\OperationType $operationType
     * @return void
     */
    public function showAction(OperationType $operationType)
    {
        $this->view->assign('operationType', $operationType);
    }

    /**
     * @return void
     */
    public function newAction()
    {
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\OperationType $newOperationType
     * @return void
     * @Flow\Validate(argumentName="$newOperationType", type="UniqueEntity")
     */
    public function createAction(OperationType $newOperationType)
    {
        $this->operationTypeRepository->add($newOperationType);
        $this->addFlashMessage('Created a new operation type.');
        $this->redirect('index');
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\OperationType $operationType
     * @return void
     */
    public function editAction(OperationType $operationType)
    {
        $this->view->assign('operationType', $operationType);
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\OperationType $operationType
     * @return void
     */
    public function updateAction(OperationType $operationType)
    {
        $this->operationTypeRepository->update($operationType);
        $this->addFlashMessage('Updated the operation type.');
        $this->redirect('index');
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\OperationType $operationType
     * @return void
     */
    public function deleteAction(OperationType $operationType)
    {
        $this->operationTypeRepository->remove($operationType);
        $this->addFlashMessage('Deleted a operation type.');
        $this->redirect('index');
    }

}
