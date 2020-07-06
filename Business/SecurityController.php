<?php

namespace Us\Bundle\SecurityBundle\Business;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Us\Bundle\SecurityBundle\Business\AccessSecurity\Authorizer;
use Us\Bundle\SecurityBundle\Dispatcher\DocumentHydratationEvent;
use Us\Bundle\SecurityBundle\Dispatcher\DocumentPersistEvent;
use Us\Bundle\SecurityBundle\Dispatcher\RequestValidatorEvent;
use Us\Bundle\SecurityBundle\Events\DocumentHydratationEvents;
use Us\Bundle\SecurityBundle\Response\ApiJsonResponse;
use Us\Bundle\SecurityBundle\Document\BaseDocument;
use Us\Bundle\SecurityBundle\Document\User;
use Us\Bundle\SecurityBundle\Exception\DocumentHydratationException;
use Us\Bundle\SecurityBundle\Exception\DocumentValidationException;
use Us\Bundle\SecurityBundle\Handler\DocumentValidationHandler;
use Us\Bundle\SecurityBundle\Handler\FileUploadHandler;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Route;
use Us\Bundle\SecurityBundle\Handler\StoreCustomParametersValidationHandler;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class SecurityController extends Controller
{
    const DOCUMENT_BASE_NAMESPACE = 'Us\Bundle\SecurityBundle\\Document\\';

    const FORM_BASE_NAMESPACE = 'Us\Bundle\SecurityBundle\\Form\\';

//    protected $eventDispatcher;

    /** @var Authorizer */
    protected $authorizer;

    protected $requestIdentifier = '';

    /** @var Request */
    public $currentRequest = null;

    public $requestData = null;

    public $currentResourceName;

    /** @var  User */
    protected $user;

    /** @var JsonRespone $jsonReponse */
    protected $jsonResponse = null;

    protected $documentEventBase = '';


    /** @var RequestHandler */
    protected $requestHandler;

    /** Default function executed just before the action call => Can be overrided
     *  - see the RequestListener
     */
    public function preAction() {}

    public function setRequestHandler($ozmoRequestHandler)
    {
        $this->requestHandler = $ozmoRequestHandler;
    }

    protected function retrieveDocuments(BaseDocument $document, $byCriteriaValue = [])
    {
        $doctrineMongodbService = $this->container->get('doctrine_mongodb');
        $dm = $doctrineMongodbService->getManager();

        /** @var DocumentRepository $repository */
        $repository = $dm->getRepository(get_class($document));

        $documents = $repository->findBy($byCriteriaValue);

        unset($repository);

        if (null === $documents) {
            $message = 'None ' . $document->_getName() . ' found';
            throw new ResourceNotFoundException($message);
        }

        return $documents;
    }

    protected function retrieveDocument(BaseDocument $document, $byCriteriaValue)
    {
        $doctrineMongodbService = $this->container->get('doctrine_mongodb');
        $dm = $doctrineMongodbService->getManager();

        /** @var DocumentRepository $repository */
        $repository = $dm->getRepository(get_class($document));

        if (is_array($byCriteriaValue)) {
            $doc = $repository->findOneBy($byCriteriaValue);
        } else {
            $doc = $repository->find($byCriteriaValue);
        }

        unset($repository);

        if (null === $doc) {
            $message = $document->_getName() . ' not found';
            throw new ResourceNotFoundException($message);
        }

        return $doc;
    }

    protected function brutPersistDocument(BaseDocument $document, $flush = true)
    {
        $dispatcher = $this->getEventDispatcher();

        $event = new DocumentPersistEvent($document);

        $this->documentEventBase = strtoupper($document->_getName()) . '_PERSIST';

        try {
            //  I / GENERIC documentPersistEvent
            $dispatcher->dispatch('DOCUMENT_PERSIST', $event);
            //  II / DOCUMENT documentPersistEvent
            $dispatcher->dispatch(
                $this->documentEventBase,
                $event
            );

            $doctrineMongodbService = $this->container->get('doctrine_mongodb');
            /** @var DocumentManager $dm */
            $dm = $doctrineMongodbService->getManager();
            $dm->persist($event->getDocument());
            if ($flush) {
                $dm->flush();
                $dm->refresh($event->getDocument());
//                $dm->getManager()->clear();
            }

            //  IV /  1.1 GENERIC documentPersistEvent SUCCESS
            $dispatcher->dispatch('DOCUMENT_PERSIST_SUCCESS', $event);
            //  IV /  1.2 DOCUMENT documentPersistEvent SUCCESS
            $dispatcher->dispatch(
                $this->documentEventBase . '_SUCCESS',
                $event
            );

        } catch (\Exception $e) {
            $message = $e->getMessage();

            $dispatcher = $this->getEventDispatcher();

            //  IV /  2.1 GENERIC documentPersistEvent FAIL
            $dispatcher->dispatch('DOCUMENT_PERSIT_FAIL', $event);
            //  IV /  2.2 DOCUMENT documentPersistEvent FAIL
            $dispatcher->dispatch(
                $this->documentEventBase . '_FAIL',
                $event
            );

            throw new \Exception('Document persist fail : ' . $e->getMessage(), 500);
        }
    }

    protected function simpleDocumentPersist($document)
    {
        $doctrineMongodbService = $this->container->get('doctrine_mongodb');
        /** @var DocumentManager $dm */
        $dm = $doctrineMongodbService->getManager();
        $dm->persist($document);
        $dm->flush();
    }

    /**
     * @param $document
     * @param array $data
     * @param bool $replace
     * @param bool $throwExceptions
     * @return bool|BaseDocument
     * @throws \Exception
     */
    protected function hydrateAndValidateDocument($document, $data = [], $replace = false, $throwExceptions = true)
    {
        /** @var BaseDocument $document */

        $validationHandlerServiceName = $this->container->getParameter('us_security.document_validation_handler');
        /** @var DocumentValidationHandler $documentValidationHandler */
        $documentValidationHandler = $this->container->get($validationHandlerServiceName);

        if ($data) {
            // @todo here is a STORE CORE EVENT entry
            $rawData = $this->dispatchDocumentHydratationEvent(DocumentHydratationEvents::PRE_PROCESS, $data, $document, $replace);

            $document = $documentValidationHandler->hydrateDocument($document, $rawData, $replace, $throwExceptions);

            // @todo here is a STORE CORE EVENT entry
            $this->dispatchDocumentHydratationEvent(DocumentHydratationEvents::POST_PROCESS, $data, $document, $replace);
        }
        if (null === $document) {
            return false;
        }

        $uniqueFieldsToIgnore = [];
        if ($document->getId()) {
            $uniqueFieldsToIgnore = $documentValidationHandler->filterUniqueFieldsToIgnore($document, $data);
        }

        $result = $documentValidationHandler->validateDocument($document, $throwExceptions, $uniqueFieldsToIgnore);

        if (true === $result) {
            return $document;
        }

        return [$document, $result];
    }

    protected function dispatchDocumentHydratationEvent($name, $data, $document, $replace)
    {
        $event = new DocumentHydratationEvent($data, $document, $replace);
        $this->getEventDispatcher()->dispatch(
            $name,
            $event
        );
        return $event->getData();
    }


    /**
     * @param BaseDocument $document
     * @param bool $existing
     * @param null $data
     * @return JsonResponse
     */
    protected function persistDocument(BaseDocument $document, $id = null, $data = null)
    {
        if (!$data) {
            $data = $this->requestData;
        }

        /*
         *  DOCUMENT VALIDATION
         */

        $document = $this->hydrateAndValidateDocument($document, $data);

        /*
         *  @todo FILES IMPORT ---
         */
//            $this->handleUploadedFiles($data, $document);

        $this->brutPersistDocument($document);

        return [$document, new ApiJsonResponse($document->toArray())];
    }

    protected function populateDocument($document, Array $data)
    {
//        foreach($data as )
    }

    protected function setAuthorizer($authorizer)
    {
        $this->authorizer = $authorizer;
    }

    /**
     * @return Authorizer
     *
     */
    public function getAuthorizer()
    {
        /** @var Authorizer $authorizer */
        $authorizer = $this->get('api.authorizer');
        $authorizer->setUser($this->user);
        $route = $this->get('request_stack')->getCurrentRequest()->attributes->get('_route');
        $authorizer->setRoute($route);

        return $authorizer;
    }

    /**
     * Get the Event Dispatcher
     *
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        $dispatcher = $this->get('event_dispatcher');

        return $dispatcher;
    }

    protected function dispatchRequestValidatorEvent($eventName, Array $request)
    {
        $dispatcher = $this->getEventDispatcher();
        $event = new RequestValidatorEvent($request);
        $dispatcher->dispatch($eventName, $event);

        return $event;
    }

    /**
     * @param $data
     * @return StoreCustomParametersValidationHandler
     */
    protected function getValidator($data)
    {
        return new StoreCustomParametersValidationHandler($data);
    }


    public function setJsonResponse(JsonResponse $response)
    {
        $this->jsonResponse = $response;
    }

    /**
     * @return JsonRespone $this->jsonResponse
     */
    public function getJsonResponse()
    {
        return $this->jsonResponse;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        if (!$user instanceof \Us\Bundle\SecurityBundle\Document\User) {
            throw new \LogicException('Wrong user type given');
        }

        $this->user = $user;
        return $this;
    }

    public function setCurrentResourceName($name)
    {
        $this->currentResourceName = $name;
        return $this;
    }

//    /**
//     * @param $data
//     * Deeply detects all the "UploadedFile" in $data and move its into right directory
//     * @todo TEST IT ... need to add filters of types/size etc etc on each uploaded files IN FORM FILE !!! cf "pdfFile" on FCC...
//     */
//    protected function handleUploadedFiles($data, BaseDocument $document)
//    {
//        $uploadedFiles = [];
//        $dataDiver =  function ($data, &$uploadedFiles) use (&$dataDiver) {
//
//            if (!($data instanceof  UploadedFile) && !is_array($data)) {
//                return;
//            }
//            if (is_array($data)) {
//
//                foreach ($data as $item) {
//                    $dataDiver($item, $uploadedFiles);
//                }
//            }
//            if ($data instanceof  UploadedFile) {
//                return $uploadedFiles[] = $data;
//            }
//        };
//
//        $dataDiver($data, $uploadedFiles);
//
//        // Move all files
//        if ($uploadedFiles) {
//            /** @var UploadedFile $file */
//            foreach($uploadedFiles as $file) {
//                $extension = $file->getExtension();
//                $fileProductTypeSuffix = '';
//                $fileName = $file->getFilename();
//                $isProduct = false;
//
//                if ($document instanceof Product) {
//                    $fileProductTypeSuffix = 'parent';
//                } else if ($document instanceof ProductDeclination) {
//                    $fileProductTypeSuffix = 'declination';
//                }
//
//                if ($fileProductTypeSuffix) {
//                    $fileName = $fileName . '_' . $fileProductTypeSuffix;
//                    $isProduct = true;
//                }
//
//                /** @var FileUploadHandler $fileUploadHandler */
//                $fileUploadHandler = $this->get('base.file.upload.handler');
//                $moved = $fileUploadHandler->moveUploadedFile(
//                    $file,
//                    $fileName,
//                    $isProduct ? 'product/' . substr($file->getFilename(), 0, 1) : null
//                );
//            }
//        }
//
//    }


    public function generateCacheRequestIdentifier(Request $request)
    {
        $sortedQueryString = "";

        $pathinfo = $request->getPathInfo();

        $query = $request->query->all();

        ksort($query);

        foreach ($query as $name => $value) {

            if (empty($sortedQueryString)) {
                $prefix = '?';
            } else {
                $prefix = '&';
            }

            if (is_array($value)) {
                sort($value);
                foreach ($value as $index => $val) {
                    $sortedQueryString .= $prefix . $name . '[]=' . $val;
                }
            } else {
                $sortedQueryString .= $prefix . $name . '=' . $value;
            }
        }

        $this->requestIdentifier = $pathinfo . $sortedQueryString;
    }

    /**
     * @param $data
     * @param $status
     * @param bool $setMaxAge
     * @param array $headers
     * @return ApiJsonResponse
     */
    protected function apiJsonResponse($data, $status, $setMaxAge = false, $headers = [])
    {
        $response = new ApiJsonResponse($data, $status, $headers);

        if ($setMaxAge) {
            $maxAge = ApiConfiguration::getValue(sprintf('resources.%s.maxAge', $this->currentResourceName));
            $maxAge = $maxAge ? (int)$maxAge : ApiJsonResponse::DEFAULT_CACHE_MAX_AGE;
            $response->setMaxAge($maxAge);
        }

        return $response;
    }

    /**
     * @param $document
     * @return array
     */
    protected function toArraySerializeDocument($document)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        return json_decode($serializer->serialize($document->toArray(), 'json'), true);
    }
}
