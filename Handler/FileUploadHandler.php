<?php

namespace Us\Bundle\SecurityBundle\Handler;

use Store\BaseBundle\Document\PdfFile;
use Store\BaseBundle\Document\PictureFile;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Created by PhpStorm.
 * User: florian
 * Date: 26/08/16
 * Time: 13:09
 */
class FileUploadHandler
{
    const DEFAULT_LOCATION_DIVISION_NAME = 'AppBundle\AppBundle';


    /** @var  ContainerInterface */
    protected $container;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var Kernel */
    protected $kernel;

    public function __construct(ContainerInterface $serviceContainer, EventDispatcherInterface $eventDispatcher, Kernel $kernel)
    {
        $this->container = $serviceContainer;
        $this->eventDispatcher = $eventDispatcher;
        $this->kernel = $kernel;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param $resourceDirPath - ex. "pdf", "product/img/parent", "product/img/declination" ...
     * @param string $divisionName
     * @return bool
     */
    public function moveUploadedFile(UploadedFile $uploadedFile, $fileName, $resourceDirPath = null, $divisionName = self::DEFAULT_LOCATION_DIVISION_NAME)
    {
        // @todo ...
//        return true;

        $extension = $uploadedFile->getExtension();
        $fileName = strtolower($fileName . '__' . microtime());
        if ($resourceDirPath === null) {
            if (in_array($extension, PictureFile::getExtensions())) {
                $resourceDirPath = 'img';
            } else if (in_array($extension, PdfFile::getExtensions())) {
                $resourceDirPath = 'pdf';
            }
        }

        try {
            $uploadedFile->move(
                $this->kernel->locateResource(sprintf('@%s/Resources/%s', $divisionName, $resourceDirPath)),
                sprintf('%s.%s', $fileName, $extension)
            );
            return true;
        } catch (Exception $e) {
            // @todo log error ..
            return false;
        }
    }
}