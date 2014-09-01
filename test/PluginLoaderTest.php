<?php
/**
 * Definition of class PluginLoaderTest
 * 
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justtexts\test
 */

namespace justso\justtexts\test;

use justso\justapi\test\ServiceTestBase;
use justso\justtexts\service\PluginLoader;

/**
 * Class PluginLoaderTest
 * @package justso\justtexts\test
 */
class PluginLoaderTest extends ServiceTestBase
{
    public function testLoader()
    {
        $env = $this->createTestEnvironment();
        $service = new PluginLoader($env);
        $service->getAction();
        $this->assertEquals(
            array(
                'HTTP/1.0 200 Ok',
                'Content-Type: text/javascript; charset=utf-8',
            ),
            $env->getResponseHeader()
        );
    }
}
