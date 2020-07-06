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

class DocumentHydratationException extends \Exception
{
    protected $httpStatusCode;

    public function __construct($httpStatusCode, $message = '', \Exception $previous = null)
    {
        $code = 0;
        $this->httpStatusCode = $httpStatusCode;
        $this->message = $message;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }
}