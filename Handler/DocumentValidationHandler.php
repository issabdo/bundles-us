<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 02/09/16
 * Time: 23:21
 */

namespace Us\Bundle\SecurityBundle\Handler;


use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Hydrator\HydratorFactory;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Store\BaseBundle\BusinessLayer\Core\StoreDocument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Us\Bundle\SecurityBundle\Document\BaseDocument;
use Us\Bundle\SecurityBundle\Exception\DocumentValidationException;

class DocumentValidationHandler
{
    const VALIDATION_CHILD_VIOLATIONS_INDEX = 'errors';
    const VALIDATION_GLOBAL_VIOLATIONS_INDEX = 'global';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ManagerRegistry
     */
    protected $dm;

    /**
     * @var DocumentManager
     */
    protected $odmManager;

    /**
     * @var RecursiveValidator
     */
    protected $validator;

    protected $dispatcher;

    public function __construct(ContainerInterface $serviceContainer, ManagerRegistry $dm, $dispatcher)
    {
        $this->container = $serviceContainer;
        $this->dm = $dm;
        $this->dispatcher = $dispatcher;
        $this->odmManager = $dm->getManager();
        $this->validator = $this->container->get('validator');
    }

    protected function getRepository($document)
    {
        return $this->odmManager->getRepository(get_class($document));
    }

    public function hydrateDocument(BaseDocument $document, $data, $replace = false, $throwExceptions)
    {
        $resourceName = $document->_getName();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $id = $document->getId();

        if ($id) {
            $document = $this->getRepository($document)->find($id);
            if (null === $document) {
                if ($throwExceptions) {
                    throw new NotFoundHttpException(sprintf('Can not find resource "%s" with id "%s"', $resourceName, $id));
                }
                return null;
            }

            if (!$replace) {
                $objectDataArray = $document->toArray();
                $data = $this->array_merge_recursive_ex($objectDataArray, $data);
            }
        }

        $document = $serializer->deserialize(json_encode($data), get_class($document), 'json');

        return $document;
    }

    public function mergeCollectionsData($document, &$data)
    {
        $fetchLevel = function ($level) use ($document, &$fetchLevel) {

            foreach ($level as $param => $value) {
                if (is_array($value)) {
//                    foreach ($value as $i => $embed) {
//                        if (is_array($embed) && is_numeric($i)) {
//    //                        $getter
//                        }
//                    }
                }
            }
        };

        $fetchLevel($data);

//        foreach ($document as $param => $value) {
//            if ($value instanceof PersistentCollection) {
//
//                $data[$param]
//            }
//        }
    }


    /**
     * Validates a StoreDocument $hydratedDocument based on its declared constraints
     *
     * @param StoreDocument $hydratedDocument
     * @param $data
     * @throws EEDocumentValidationException
     */
    public function validateDocument(BaseDocument $hydratedDocument, $throwExceptions, $uniqueFieldsToIgnore = [])
    {
        $violationParameters = [];

        /** @var ConstraintViolationList $violations */
        $violations = $this->validator->validate($hydratedDocument);

        if (count($violations) > 0) {

            /** @var ConstraintViolation $constraintViolation */
            foreach ($violations->getIterator() as $constraintViolation) {
                $message = $constraintViolation->getMessage();
                $constraint = $constraintViolation->getConstraint();
                $params = $constraintViolation->getParameters();
                $propertyPath = $this->formatPropertyPathAsChain($constraintViolation->getPropertyPath());

                if ($constraint instanceof Callback) {

                    if (!$params && empty($propertyPath)) {
                        $this->addDocumentGlobalViolation($message, $violationParameters);
                        continue;
                    }

                    if ($params) {
                        foreach ($params as $param) {
                            $this->addDocumentChildViolation($message, $param, $violationParameters);
                        }
                        continue;
                    }
                }

                if ($uniqueFieldsToIgnore) {
                    if ($constraint instanceof Unique && in_array($constraint->fields, $uniqueFieldsToIgnore)) {
                        continue;
                    }
                }

                $this->addDocumentChildViolation($message, $propertyPath, $violationParameters);
            }
        }

        if (!empty($violationParameters)) {
            if ($throwExceptions) {
                throw new DocumentValidationException($violationParameters);
            }
            return $violationParameters;
        }

        return true;
    }

    /**
     * HACK - Defines the list of all the properties defined as "unique" we will ignore after validation
     * @ https://github.com/doctrine/DoctrineMongoDBBundle/blob/master/Validator/Constraints/Unique.php
     *
     * @param $documentToHydrate
     * @param $data
     * @return array
     */
    public function filterUniqueFieldsToIgnore($documentToHydrate, $data)
    {
        $uniqueFields = [];
        /** @var ClassMetadata $meta */
        $meta = $this->validator->getMetadataFor($documentToHydrate);
        foreach ($meta->getConstraints() as $constraint) {
            if ($constraint instanceof Unique) {
                $getter = 'get' . ucfirst($constraint->fields);
                if (!isset($data[$constraint->fields]) || $documentToHydrate->$getter() === $data[$constraint->fields]) {
                    $uniqueFields[] = $constraint->fields;
                }
            }
        }
        return $uniqueFields;
    }

    public static function addDocumentGlobalViolation($message, &$violationParameters)
    {
        if (!isset($violationParameters['errors'])) {
            $violationParameters['errors'] = [];
        }

        if (!isset($violationParameters['errors'][self::VALIDATION_GLOBAL_VIOLATIONS_INDEX])) {
            $violationParameters['errors'][self::VALIDATION_GLOBAL_VIOLATIONS_INDEX] = [];
        }

        $violationParameters['errors'][self::VALIDATION_GLOBAL_VIOLATIONS_INDEX][] = $message;
    }

    public static function addDocumentChildViolation($message, $propertyPath, &$violationParameters)
    {
        if (!isset($violationParameters['errors'])) {
            $violationParameters['errors'] = [];
        }

        if (!isset($violationParameters['errors']['fields'])) {
            $violationParameters['errors']['fields'] = [];
        }

        $propertyPath = 'errors.fields.' . $propertyPath;

        $levels = explode('.', $propertyPath);
        $levelsReverse = array_reverse($levels);

        foreach ($levelsReverse as $index => $level) {
            if ((int)$index === 0) {
                $levelsReverse[$index] = [$level => [self::VALIDATION_CHILD_VIOLATIONS_INDEX => [$message]]];
                continue;
            }
            $levelsReverse[$index] = [$level => $levelsReverse[((int)$index - 1)]];
        }

        $violationParameters = self::array_merge_recursive_ex($violationParameters, $levelsReverse[count($levels) - 1]);
    }

    public static function array_merge_recursive_ex(array & $array1, array & $array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => & $value) {

            if (is_numeric($key)) {
                $key = (int)$key;
            }

            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::array_merge_recursive_ex($merged[$key], $value);
            } else if (is_numeric($key) && is_string($value)) {
                if (!in_array($value, $merged)) {
                    $merged[] = $value;
                }
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    public function formatPropertyPathAsChain($string)
    {
        return str_replace(' ', '.', trim(str_replace('  ', ' ', str_replace(['[', ']', '.'], ' ', $string))));
    }
}