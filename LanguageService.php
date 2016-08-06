<?php
/**
 * Definition of class LanguageService
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\service
 */

namespace justso\justtexts;

use justso\justapi\RestService;

/**
 * Class LanguageService
 * @package justso\service
 */
class LanguageService extends RestService
{
    public function getAction()
    {
        $config = $this->environment->getBootstrap()->getConfiguration();
        $this->environment->sendJSONResult($config['languages']);
    }
}
