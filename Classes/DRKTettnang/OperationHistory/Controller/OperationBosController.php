<?php
namespace DRKTettnang\OperationHistory\Controller;

/*
 * This file is part of the DRKTettnang.OperationHistory package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use DRKTettnang\OperationHistory\Domain\Model\OperationBos;

class OperationBosController extends ActionController
{

    /**
     * @Flow\Inject
     * @var \DRKTettnang\OperationHistory\Domain\Repository\OperationBosRepository
     */
    protected $operationBosRepository;

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->view->assign('operationBos', $this->operationBosRepository->findAll());
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\OperationBos $operationBos
     * @return void
     */
    public function showAction(OperationBos $operationBos)
    {
        $this->view->assign('operationBos', $operationBos);
    }

    /**
     * @return void
     */
    public function newAction()
    {
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\OperationBos $newOperationBos
     * @return void
     */
    public function createAction(OperationBos $newOperationBos)
    {
        $this->operationBosRepository->add($newOperationBos);
        $this->addFlashMessage('Created a new operation bos.');
        $this->redirect('index');
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\OperationBos $operationBos
     * @return void
     */
    public function editAction(OperationBos $operationBos)
    {
        $this->view->assign('operationBos', $operationBos);
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\OperationBos $operationBos
     * @return void
     */
    public function updateAction(OperationBos $operationBos)
    {
        $this->operationBosRepository->update($operationBos);
        $this->addFlashMessage('Updated the operation bos.');
        $this->redirect('index');
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\OperationBos $operationBos
     * @return void
     */
    public function deleteAction(OperationBos $operationBos)
    {
        $this->operationBosRepository->remove($operationBos);
        $this->addFlashMessage('Deleted a operation bos.');
        $this->redirect('index');
    }

}
