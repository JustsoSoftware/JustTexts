<?php
/**
 * Definition of class Language
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\service
 */

namespace justso\justtexts\service;

use justso\justapi\Bootstrap;
use justso\justapi\RestService;

/**
 * Class Language
 * @package justso\service
 */
class Language extends RestService
{
    public function getAction()
    {
        $config = Bootstrap::getInstance()->getConfiguration();
        $this->environment->sendJSONResult($config['languages']);
    }
}
