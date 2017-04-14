<?php

namespace DRKTettnang\OperationHistory\Controller;

/*
 * This file is part of the DRKTettnang.OperationHistory package.
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use DRKTettnang\OperationHistory\CsrfProtectionToken;
use DRKTettnang\OperationHistory\Domain\Model\Operation;
use DRKTettnang\OperationHistory\Domain\Model\OperationBos;
use DRKTettnang\OperationHistory\Domain\Model\OperationType;
use TYPO3\Media\Domain\Model\ThumbnailConfiguration;
use TYPO3\Flow\Mvc\View\ViewInterface;
use TYPO3\Flow\Session\SessionInterface;

class OperationController extends ActionController
{
   /**
    * @Flow\Inject
    *
    * @var CsrfProtectionToken
    */
   protected $csrfProtectionToken;

   /**
    * @Flow\Inject
    *
    * @var \TYPO3\Media\Domain\Repository\AssetCollectionRepository
    */
   protected $assetCollectionRepository;

   /**
    * @Flow\Inject
    *
    * @var \TYPO3\Media\Domain\Service\AssetService
    */
   protected $assetService;

   /**
    * @Flow\Inject
    *
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
    *
    * @var \DRKTettnang\OperationHistory\Domain\Repository\OperationRepository
    */
   protected $operationRepository;

   /**
    * @Flow\Inject
    *
    * @var \DRKTettnang\OperationHistory\Domain\Repository\OperationTypeRepository
    */
   protected $operationTypeRepository;

   /**
    * @Flow\Inject
    *
    * @var \DRKTettnang\OperationHistory\Domain\Repository\OperationBosRepository
    */
   protected $operationBosRepository;

   /**
    * @var array
    */
   protected $viewFormatToObjectNameMap = array(
      'html' => 'TYPO3\Fluid\View\TemplateView',
      'json' => 'TYPO3\Flow\Mvc\View\JsonView',
   );

   /**
    * A list of IANA media types which are supported by this controller.
    *
    * @var array
    * @see http://www.iana.org/assignments/media-types/index.html
    */
   protected $supportedMediaTypes = array(
      'text/html',
      'application/json',
   );

   /**
    * Assign years in every action.
    *
    * @param  ViewInterface $view
    */
   public function initializeView($view)
   {
      $this->view->assign('years', $this->operationRepository->findYears());

      parent::initializeView($view);
   }

   /**
    * Show list of operations in given year. If no year is provided, the user is
    * redirected to the list of operations of the latest year.
    *
    * @param int [$year]
    */
   public function indexAction($year = null)
   {
      if (is_numeric($year)) {
         $operations = $this->operationRepository->findByYear($year);
         $this->view->assign('currentYear', $year);
      } else {
         $year = $this->operationRepository->findLastYear();
         $this->redirect('index', null, null, array('year' => $year));
      }

      $this->view->assign('operations', $operations);
      $this->view->assign('typeStatistic', $this->operationRepository->getTypeStatisticByYear($year));
   }

   /**
    * Show single operation.
    *
    * @param \DRKTettnang\OperationHistory\Domain\Model\Operation $operation
    */
   public function showAction($operation)
   {
      $this->view->assign('operation', $operation);
      $this->view->assign('currentYear', $operation->getDate()->format('Y'));
   }

   /**
    * Show latest operation.
    */
   public function showLatestAction()
   {
      $operation = $this->operationRepository->findLatest();

      $this->forward('show', null, null, array('operation' => $operation));
   }

   /**
    * Show new operation form.
    */
   public function newAction()
   {
      $this->view->assign('csrfProtectionToken', $this->csrfProtectionToken->get());
      $this->view->assign('operationTypes', $this->operationTypeRepository->findAll());
      $this->view->assign('operationBos', $this->operationBosRepository->findAll());
   }

   /**
    * Set up datetime converter.
    */
   public function initializeCreateAction()
   {
      $mappingConfig = $this->arguments['newOperation']->getPropertyMappingConfiguration();
      $mappingConfig->forProperty('date')->setTypeConverterOption(
         'TYPO3\Flow\Property\TypeConverter\DateTimeConverter',
         \TYPO3\Flow\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT,
         'd.m.Y H:i'
      );
   }

   /**
    * Create new operation.
    *
    * @param \DRKTettnang\OperationHistory\Domain\Model\Operation $newOperation
    * @param string $images
    * @Flow\Validate(argumentName="$newOperation", type="UniqueEntity", options={"identityProperties"={"date"}})
    */
   public function createAction($newOperation, $images)
   {
      $this->csrfProtectionToken->delete();

      // @TODO add author
      $newOperation->setAuthor('nobody');
      // @TODO publish operation on facebook
      $newOperation->setFb_post_id('');
      $newOperation->setYear($newOperation->getDate()->format('Y'));

      try {
         $images = json_decode($images);
      } catch (Exception $e) {
      }

      if ($images === null || !is_array($images)) {
         $images = array();
      }
      $newOperation->setImages($images);

      $this->operationRepository->add($newOperation);
      $this->addFlashMessage('Neuen Einsatz mit '.count($images).' Fotos erstellt.');

      $this->redirect('show', null, null, array('operation' => $newOperation));
   }

   /**
    * Show operation update form.
    *
    * @param \DRKTettnang\OperationHistory\Domain\Model\Operation $operation
    */
   public function editAction($operation)
   {
      // assign csrf protection token, because upload action is reachable for everone
      $this->view->assign('csrfProtectionToken', $this->csrfProtectionToken->get());
      $this->view->assign('operationTypes', $this->operationTypeRepository->findAll());

      $checkedBos = $operation->getBos();
      $existingBos = $this->operationBosRepository->findAll();
      foreach ($existingBos as $bos) {
         $bos->checked = in_array($bos, $checkedBos->toArray());
      }

      $this->view->assign('operationBos', $existingBos);
      $this->view->assign('images', $operation->getJsonImages());
      $this->view->assign('operation', $operation);
   }

   /**
    * Set up datetime converter.
    */
   public function initializeUpdateAction()
   {
      $mappingConfig = $this->arguments['operation']->getPropertyMappingConfiguration();
      $mappingConfig->forProperty('date')->setTypeConverterOption(
         'TYPO3\Flow\Property\TypeConverter\DateTimeConverter',
         \TYPO3\Flow\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT,
         'd.m.Y H:i'
      );
   }

   /**
    * Update given operation.
    *
    * @param \DRKTettnang\OperationHistory\Domain\Model\Operation $operation
    * @param string                                               $images
    * @Flow\Validate(argumentName="$operation", type="UniqueEntity", options={"identityProperties"={"date"}})
    */
   public function updateAction($operation, $images)
   {
      // make token invalid
      $this->csrfProtectionToken->delete();

      try {
         $images = json_decode($images);
      } catch (Exception $e) {
      }

      if ($images === null || !is_array($images)) {
         $images = array();
      }
      $operation->setImages($images);

      $this->operationRepository->update($operation);
      $this->addFlashMessage('Updated the operation.');

      $this->redirect('show', null, null, array('operation' => $operation));
   }

   /**
    * Delete given operation.
    *
    * @param \DRKTettnang\OperationHistory\Domain\Model\Operation $operation
    */
   public function deleteAction($operation)
   {
      $this->operationRepository->remove($operation);
      $this->addFlashMessage('Deleted a operation.');
      $this->redirect('index');
   }

   /**
    * Set up datetime converter.
    */
   public function initializeUploadAction()
   {
      $commentConfiguration = $this->arguments['image']->getPropertyMappingConfiguration();
      $commentConfiguration->allowAllProperties();
      $commentConfiguration->setTypeConverterOption(
         \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::class,
         \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
         true
      );
   }

   /**
    * Save uploaded image in Upload collection.
    *
    * @param \TYPO3\Media\Domain\Model\Image $image
    * @param string $protection
    */
   public function uploadAction($image, $protection)
   {
      // We need to use our own csrf protection token, because this action is reachable by everone
      if (!$this->csrfProtectionToken->verify($protection)) {
         return;
      }

      //$ext = $image->getFileExtension();
      $this->assetRepository->add($image);

      // Check if Upload collection exists
      $collection = $this->assetCollectionRepository->findByTitle('Upload')->getFirst();
      if ($collection === null) {
         $collection = new \TYPO3\Media\Domain\Model\AssetCollection('Upload');
         $this->assetCollectionRepository->add($collection);
      }

      $collections = $image->getAssetCollections();
      $collections->add($collection);
      $image->setAssetCollections($collections);

      // create thumbnail for preview
      $thumbnailConfiguration = new ThumbnailConfiguration(100, null, 100, null, true, false, false);
      $thumbnailData = $this->assetService->getThumbnailUriAndSizeForAsset($image, $thumbnailConfiguration);

      $results = array('files' => array(
         array(
            'identifier' => $image->getIdentifier(),
            'name' => $image->getResource()->getFilename(),
            'size' => $image->getResource()->getFilesize(),
            'url' => $this->resourceManager->getPublicPersistentResourceUri($image->getResource()),
            'thumbnailurl' => $thumbnailData['src'],
         ),
      ));

      $this->view->assign('images', $results);
      $this->view->assign('value', $results);
   }

   /**
    * Import old operations and create operation type and bos. Use with care,
    * because no duplication check is included.
    */
   public function importAction()
   {
      $oldOperations = $this->operationRepository->getOldOperations();
      $operations = [];

      $long = array(
         'fw' => 'Feuerwehr',
         'polizei' => 'Polizei',
         'drk' => 'Weitere DRK Gruppen',
         'thw' => 'THW',
         'rhs' => 'Rettungshundestaffel',
         'rd' => 'Rettungsdienst',
         'rth' => 'Rettungshubschrauber',
         'malteser' => 'Malteser',
         'johanniter' => 'Johanniter',
         'dlrg' => 'DLRG',
      );

      $name = array(
         'segf' => 'SEG Führung',
         'segg' => 'SEG groß',
         'segk' => 'SEG klein',
         'btr' => 'Betreuung und Logistik',
         'hnr' => 'Hausnotruf',
         'rdh' => 'Rettungsdiensthintergrund',
      );

      foreach ($oldOperations as $o) {
         $operation = new Operation();
         $operation->setLocation($o->location);
         $operation->setDescription($o->description);
         $operation->setDate(new \DateTime($o->date));
         $operation->setYear($operation->getDate()->format('Y'));
         $operation->setAuthor('nobody');
         $operation->setFb_post_id('');

         $type = $this->operationTypeRepository->findOneById($o->type);
         if ($type == null) {
            $type = new OperationType();
            $type->setId($o->type);
            $type->setLabel($name[$o->type]);

            $this->persistenceManager->whiteListObject($type);
            $this->operationTypeRepository->add($type);
         }
         $operation->setType($type);

         if (isset($o->bos) && is_object($o->bos)) {
            $bosCollection = new \Doctrine\Common\Collections\ArrayCollection();

            foreach ($o->bos as $b) {
               $bos = $this->operationBosRepository->findOneByName($long[$b]);

               if ($bos == null) {
                  $bos = new OperationBos();
                  $bos->setName($long[$b]);

                  $this->persistenceManager->whiteListObject($bos);
                  $this->operationBosRepository->add($bos);
               }

               $bosCollection->add($bos);
            }
            $operation->setBos($bosCollection);
         }

         if (isset($o->assets) && is_object($o->assets)) {
            $images = [];
            foreach ($o->assets as $a) {
               $images[] = $a->__identifier;
            }
            $operation->setImages($images);
         }

         $operations[] = $operation;

         $this->persistenceManager->whiteListObject($operation);
         $this->operationRepository->add($operation);
      }
      $this->view->assign('operations', $operations);
   }

   /**
    * Pseudo login function to trigger login form.
    */
   public function loginAction()
   {
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
   protected function errorAction()
   {
      $message = 'An error occurred while trying to call '.get_class($this).'->'.$this->actionMethodName.'(). '.PHP_EOL;
      foreach ($this->arguments->getValidationResults()->getFlattenedErrors() as $propertyPath => $errors) {
         foreach ($errors as $error) {
            $message .= 'Error for '.$propertyPath.': '.$error->render().PHP_EOL;
         }
      }
      $this->addFlashMessage($message);

      $this->forwardToReferringRequest();
   }
}
