<?php
/**
 * Definition of class PluginLoader
 * 
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justtexts\service
 */

namespace justso\justtexts\service;

use justso\justapi\Bootstrap;
use justso\justapi\RestService;

/**
 * Class PluginLoader
 * @package justso\justtexts\service
 */
class PluginLoader extends RestService
{
    public function getAction()
    {
        $this->environment->sendHeader('HTTP/1.0 200 Ok');
        $this->environment->sendHeader('Content-Type: text/javascript; charset=utf-8');
        $bootstrap = Bootstrap::getInstance();
        foreach ($this->environment->getFileSystem()->glob($bootstrap->getAppRoot() . '/vendor/*/*/justtexts-plugin.js') as $file) {
            $content = $this->environment->getFileSystem()->getFile($file);
            $this->environment->sendResult('200 Ok', 'text/javascript; charset=utf-8', $content);
        }
    }
}
