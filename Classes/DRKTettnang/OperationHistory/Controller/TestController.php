<?php
namespace DRKTettnang\OperationHistory\Controller;

/*
 * This file is part of the DRKTettnang.OperationHistory package.
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use DRKTettnang\OperationHistory\Domain\Model\Test;

class TestController extends ActionController
{

    /**
     * @Flow\Inject
     * @var \DRKTettnang\OperationHistory\Domain\Repository\TestRepository
     */
    protected $testRepository;

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->view->assign('tests', $this->testRepository->findAll());
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\Test $test
     * @return void
     */
    public function showAction(Test $test)
    {
        $this->view->assign('test', $test);
    }

    /**
     * @return void
     */
    public function newAction()
    {
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\Test $newTest
     * @return void
     */
    public function createAction(Test $newTest)
    {
        $this->testRepository->add($newTest);
        $this->addFlashMessage('Created a new test.');
        $this->redirect('index');
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\Test $test
     * @return void
     */
    public function editAction(Test $test)
    {
        $this->view->assign('test', $test);
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\Test $test
     * @return void
     */
    public function updateAction(Test $test)
    {
        $this->testRepository->update($test);
        $this->addFlashMessage('Updated the test.');
        $this->redirect('index');
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\Test $test
     * @return void
     */
    public function deleteAction(Test $test)
    {
        $this->testRepository->remove($test);
        $this->addFlashMessage('Deleted a test.');
        $this->redirect('index');
    }

}
