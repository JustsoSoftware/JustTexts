<?php
/**
 * Definition of class PluginLoader
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justtexts\service
 */

namespace justso\justtexts;

use justso\justapi\RestService;

/**
 * Class PluginLoader
 */
class PluginLoader extends RestService
{
    public function getAction()
    {
        $appRoot = $this->environment->getBootstrap()->getAppRoot();
        $content = '';
        $fs = $this->environment->getFileSystem();
        foreach ($fs->glob($appRoot . '/vendor/*/*/justtexts-plugin.js') as $file) {
            $content .= $fs->getFile($file);
        }
        $this->environment->sendResult('200 Ok', 'text/javascript; charset=utf-8', $content);
    }
}
