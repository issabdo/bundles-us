<?php
/**
 * Created by PhpStorm.
 * Date: 23/12/14
 * Time: 10:43 AM
 *
 * @author Florian PICARD <fpicard@openstudio.fr>
 */

namespace Us\Bundle\SecurityBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

class DocumentValidationException extends \Exception
{
    protected $httpStatusCode;
    protected $verbose;

    protected $parametersList;

    public function __construct(array $parametersList, $message = '', \Exception $previous = null)
    {
        $code = 0;
        $this->httpStatusCode = Response::HTTP_BAD_REQUEST;
        $this->parametersList = $parametersList;

        parent::__construct($message, $code, $previous);
    }

    public function setParametersList($parametersList)
    {
        $this->parametersList = $parametersList;
    }

    /**
     * @return array
     */
    public function getParametersList()
    {
        return $this->parametersList;
    }

//    protected function setVerbose()
//    {
//        $this->verbose = ['code' => $this->httpStatusCode, 'errors' => $this->parametersList];
//    }
//
//
//    /**
//     * @return string - as JSON
//     */
//    public function getVerbose()
//    {
//        return $this->verbose;
//    }


    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }
}