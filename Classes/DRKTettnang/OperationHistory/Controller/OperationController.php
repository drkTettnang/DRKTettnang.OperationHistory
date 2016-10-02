<?php
namespace DRKTettnang\OperationHistory\Controller;

/*
 * This file is part of the DRKTettnang.OperationHistory package.
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use DRKTettnang\OperationHistory\Domain\Model\Operation;
use TYPO3\Media\Domain\Model\ThumbnailConfiguration;
use Doctrine\Common\Collections\ArrayCollection;

class OperationController extends ActionController
{
   /**
    * @Flow\Inject
    * @var \TYPO3\Flow\Configuration\ConfigurationManager
    */
   protected $configurationManager;
   
   /**
    * @Flow\Inject
    * @var \TYPO3\Media\Domain\Repository\AssetCollectionRepository
    */
   protected $assetCollectionRepository;
   /**
     * @Flow\Inject
     * @var \TYPO3\Media\Domain\Service\AssetService
     */
    protected $assetService;
    
   /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
     */
    protected $persistenceManager;

   /**
   * @Flow\Inject
   *
   * @var TYPO3\Media\Domain\Repository\AssetRepository
   */
  protected $assetRepository;
  
  /**
  * @Flow\Inject
  *
  * @var \TYPO3\Flow\Resource\ResourceManager
  */
  protected $resourceManager;

    /**
     * @Flow\Inject
     * @var \DRKTettnang\OperationHistory\Domain\Repository\OperationRepository
     */
    protected $operationRepository;
    
    /**
     * @Flow\Inject
     * @var \DRKTettnang\OperationHistory\Domain\Repository\OperationTypeRepository
     */
    protected $operationTypeRepository;
    
    /**
     * @Flow\Inject
     * @var \DRKTettnang\OperationHistory\Domain\Repository\OperationBosRepository
     */
    protected $operationBosRepository;

    /**
     * @var array
     */
    protected $viewFormatToObjectNameMap = array(
        'html' => 'TYPO3\Fluid\View\TemplateView',
        'json' => 'TYPO3\Flow\Mvc\View\JsonView'
    );

    /**
     * A list of IANA media types which are supported by this controller
     *
     * @var array
     * @see http://www.iana.org/assignments/media-types/index.html
     */
    protected $supportedMediaTypes = array(
        'text/html',
        'application/json'
    );

    /**
     * @param integer $year
     * @param integer $limit
     * @param integer $offset
     * @return void
     */
    public function indexAction($year = null, $limit = null, $offset = 0)
    {
      if(is_numeric($year)){
         $operations = $this->operationRepository->findByYear($year, $limit, $offset);
      } else {
         $year = $this->operationRepository->findLastYear();
         $this->redirect('index', null, null, array('year' => $year));
      }
        $this->view->assign('operations', $operations);
        
        $this->view->assign('years', $this->operationRepository->findYears());
    }

    /**
     * param integer $year
     * param integer $number
     * @param \DRKTettnang\OperationHistory\Domain\Model\Operation $operation
     * @return void
     */
    public function showAction($operation)
    {
        //$operation = $this->operationRepository->findByYearAndNumber($year, $number);
        $this->view->assign('operation', $operation);
    }

    /**
     * @return void
     */
    public function newAction()
    {
      $this->view->assign('operationTypes', $this->operationTypeRepository->findAll());
      $this->view->assign('operationBos', $this->operationBosRepository->findAll());
    }
    
    public function initializeCreateAction() {
       $mappingConfig = $this->arguments['newOperation']->getPropertyMappingConfiguration();
       $mappingConfig->forProperty('date')->setTypeConverterOption(
           'TYPO3\Flow\Property\TypeConverter\DateTimeConverter',
           \TYPO3\Flow\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT,
           'd.m.Y H:i'
       );
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\Operation $newOperation
     * @param string $images
     * @return void
     * @Flow\Validate(argumentName="$newOperation", type="UniqueEntity", options={"identityProperties"={"date"}})
     */
    public function createAction(Operation $newOperation, string $images)
    {
        $newOperation->setAuthor('Klaus');
        $newOperation->setFb_post_id('');
        $newOperation->setYear($newOperation->getDate()->format('Y'));

        try{
           $images = json_decode($images);
        }catch(Exception $e){}

        if ($images === null || !is_array($images)){
           $images = array();
        }
        $newOperation->setImages($images);
        
        //$type = $this->operationTypeRepository->findById('hnr');
        //$newOperation->setType($type);
        
        $this->operationRepository->add($newOperation);
        $this->addFlashMessage('Created a new operation with '.count($images).' photos.');
        
        $this->redirect('show', null, null, array('operation' => $newOperation));
    }
    
    public function initializeUploadAction() {
      $commentConfiguration = $this->arguments['image']->getPropertyMappingConfiguration();
      $commentConfiguration->allowAllProperties();
      $commentConfiguration
                ->setTypeConverterOption(
                \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::class,
                \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
                TRUE
        );
   }
    
    /**
     * 
     * @param  \TYPO3\Media\Domain\Model\Image $image [description]
     * @return void
     */
    public function uploadAction(\TYPO3\Media\Domain\Model\Image $image) {
      $this->assetRepository->add($image);
   
      //$ext = $image->getFileExtension();
      
      $collection = $this->assetCollectionRepository->findByTitle('Upload')->getFirst();
      if($collection === null) {
         $collection = new \TYPO3\Media\Domain\Model\AssetCollection('Upload');
         $this->assetCollectionRepository->add($collection);
      }
      $collections = $image->getAssetCollections();
      $collections->add($collection);
      $image->setAssetCollections($collections);
      
      $thumbnailConfiguration = new ThumbnailConfiguration(100, null, 100, null, true, false, false);
      $thumbnailData = $this->assetService->getThumbnailUriAndSizeForAsset($image, $thumbnailConfiguration);
      
      $results = array('files' => array(
         array(
            'identifier' => $image->getIdentifier(),
            'name' => $image->getResource()->getFilename(),
            'size' => $image->getResource()->getFilesize(),
            'url' => $this->resourceManager->getPublicPersistentResourceUri($image->getResource()),
            'thumbnailurl' => $thumbnailData['src']
         )
      ));
      
      $this->view->assign('images', $results);
      
      $this->view->assign('value', $results);
   }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\Operation $operation
     * @return void
     */
    public function editAction(Operation $operation)
    {
      $this->view->assign('operationTypes', $this->operationTypeRepository->findAll());
      
      $checkedBos = $operation->getBos();
      $existingBos = $this->operationBosRepository->findAll();
      foreach($existingBos as $bos) {
         $bos->checked = in_array($bos, $checkedBos->toArray());
      }
      $this->view->assign('operationBos', $existingBos);
      
      $this->view->assign('images', $operation->getJsonImages());
      $this->view->assign('operation', $operation);
    }
    
    public function initializeUpdateAction() {
       $mappingConfig = $this->arguments['operation']->getPropertyMappingConfiguration();
       $mappingConfig->forProperty('date')->setTypeConverterOption(
           'TYPO3\Flow\Property\TypeConverter\DateTimeConverter',
           \TYPO3\Flow\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT,
           'd.m.Y H:i'
       );
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\Operation $operation
     * @param string $images
     * @return void
     * @Flow\Validate(argumentName="$operation", type="UniqueEntity", options={"identityProperties"={"date"}})
     */
    public function updateAction(Operation $operation, string $images)
    {
         try{
            $images = json_decode($images);
         }catch(Exception $e){}

         if ($images === null || !is_array($images)){
            $images = array();
         }
         $operation->setImages($images);
      
        $this->operationRepository->update($operation);
        $this->addFlashMessage('Updated the operation.');
        
        $this->redirect('show', null, null, array('operation' => $operation));
    }

    /**
     * @param \DRKTettnang\OperationHistory\Domain\Model\Operation $operation
     * @return void
     */
    public function deleteAction(Operation $operation)
    {
        $this->operationRepository->remove($operation);
        $this->addFlashMessage('Deleted a operation.');
        $this->redirect('index');
    }
    
    /**
	 * A special action which is called if the originally intended action could
	 * not be called, for example if the arguments were not valid.
	 *
	 * The default implementation sets a flash message, request errors and forwards back
	 * to the originating action. This is suitable for most actions dealing with form input.
	 *
	 * We clear the page cache by default on an error as well, as we need to make sure the
	 * data is re-evaluated when the user changes something.
	 *
	 * @return string
	 */
	protected function errorAction() {
      $message = 'An error occurred while trying to call ' . get_class($this) . '->' . $this->actionMethodName . '(). ' . PHP_EOL;
			foreach ($this->arguments->getValidationResults()->getFlattenedErrors() as $propertyPath => $errors) {
            foreach ($errors as $error) {
					$message .= 'Error for ' . $propertyPath . ': ' . $error->render() . PHP_EOL;
				}
			}
			$this->addFlashMessage($message);
         
         $this->forwardToReferringRequest();
	}

}
