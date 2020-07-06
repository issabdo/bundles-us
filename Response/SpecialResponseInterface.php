<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 12/10/16
 * Time: 01:32
 */

namespace Us\Bundle\SecurityBundle\Response;

interface SpecialResponseInterface
{
    public function __construct($headers = []);

    public function setHeaders($headers = []);
}