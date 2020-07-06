<?php
/**
 * Created by PhpStorm.
 * User: fpicard
 * Date: 21/04/16
 * Time: 12:03
 */

namespace Us\Bundle\SecurityBundle\Handler;

// @todo create new ones for Store...


use Us\Bundle\SecurityBundle\Exception\CustomParametersException;
use Us\Bundle\SecurityBundle\Exception\MissingFieldException;
use Symfony\Component\HttpFoundation\JsonResponse;

class StoreCustomParametersValidationHandler
{
    const TYPE_STRING = 'string';
    const TYPE_ARRAY = 'array';
    const TYPE_JSON = 'json';
    const TYPE_INTEGER = 'int';
    const TYPE_DOUBLE = 'float';
    const TYPE_NUMBER = 'number';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_EMAIL = 'email';

    const DEFAULT_REGEX_PATTERN_EMAIL = '^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$';

    protected $data;

    protected $required = [];
    protected $accepted = [];

    protected $missing = [];
    protected $extra = [];
    protected $invalid = [];


    public function __construct(Array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @param $name
     * @param $type
     * @return $this
     */
    public function require ($name, $type)
    {
        $this->required[$name] = $type;
        return $this;
    }

    /**
     * @param $name
     * @param $type
     * @return $this
     */
    public function accept($name, $type)
    {
        $this->accepted[$name] = $type;
        return $this;
    }

    public function validate(\Closure $callback)
    {
        $combinedParameters = array_merge($this->required, $this->accepted);

        // check extra parameters
        foreach ($this->data as $name => $value) {

            if (!array_key_exists($name, $combinedParameters)) {
                $this->extra[] = $name;
                continue;
            }
        }

        // check missing parameters
        foreach ($this->required as $name => $type) {
            if (!array_key_exists($name, $this->data)) {
//            throw new CustomParametersException($name, CustomParametersException::TYPE_MISSING);
                $this->missing[$name] = $type;
                continue;
            }
            $this->unitValidate($name, $type);
        }

        foreach ($this->accepted as $name => $type) {
            $this->unitValidate($name, $type);
        }

        if (!$this->missing && !$this->extra && !$this->invalid) {
            return $callback($this->data);
        }

        return $this->formatJsonBadRequest();
    }

    protected function unitValidate($name, $type)
    {
        if (!isset($this->data[$name])) {
            return;
        }

        $value = $this->data[$name];

        if ($type === self::TYPE_ARRAY) {
            if (!is_array($value)) {
//                throw new CustomParametersException($name, CustomParametersException::TYPE_INVALID);
                $this->invalid[$name] = 'must be an array';
            }
            return;
        }

        if ($type === self::TYPE_STRING) {
            if (!is_string($value)) {
//                throw new CustomParametersException($name, CustomParametersException::TYPE_INVALID);
                $this->invalid[$name] = 'must be a string';
            }
            return;
        }

        if ($type === self::TYPE_JSON) {
            if (!(is_string($value) && is_array(json_decode($value, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false)) {
//                throw new CustomParametersException($name, CustomParametersException::TYPE_INVALID);
                $this->invalid[$name] = 'must be a JSON string';
            }
            return;
        }

        if ($type === self::TYPE_INTEGER) {
            if ((int)$value === 0 && $value !== '0') {
//                throw new CustomParametersException($name, CustomParametersException::TYPE_INVALID);
                $this->invalid[$name] = 'must be an integer';
            }
            return;
        }

        if ($type === self::TYPE_BOOLEAN) {
            if (!(is_bool($value) || $value !== 1 || $value !== '1' || $value !== 0 || $value !== '0')) {
//                throw new CustomParametersException($name, CustomParametersException::TYPE_INVALID);
                $this->invalid[$name] = 'must be a boolean';
            }
            return;
        }

        if ($type === self::TYPE_NUMBER) {
            $value = round($value, 2, PHP_ROUND_HALF_EVEN);
            if (!is_double($value) || $value == 0) {
//                throw new CustomParametersException($name, CustomParametersException::TYPE_INVALID);
                $this->invalid[$name] = 'must be a number';
            }
            return;
        }

        if ($type === self::TYPE_EMAIL) {
            if (preg_match('/' . self::DEFAULT_REGEX_PATTERN_EMAIL . '/', $value) !== 1) {
//                throw new CustomParametersException($name, CustomParametersException::TYPE_INVALID);
                $this->invalid[$name] = 'must be a valid email string';
            }
            return;
        }

        // ELSE ... $type as a regex ...

        if (preg_match('/' . $type . '/', $value) !== 1) {
//            throw new CustomParametersException($name, CustomParametersException::TYPE_INVALID);
            $this->invalid[$name] = 'must match regex ' . $type;
        }
    }

    protected function formatJsonBadRequest()
    {
        $content = [];

        if ($this->missing) {
            $content['missing parameters'] = $this->missing;
        }

        if ($this->invalid) {
            $content['invalid parameters'] = $this->invalid;
        }

        if ($this->extra) {
            $content['unexpected parameters'] = $this->extra;
        }

        return new JsonResponse($content, 400);
    }
}