<?php
/**
 * Definition of class PluginLoaderTest
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justtexts\test
 */

namespace justso\justtexts\test;

use justso\justapi\Bootstrap;
use justso\justapi\testutil\ServiceTestBase;
use justso\justapi\testutil\FileSystemSandbox;
use justso\justapi\testutil\TestEnvironment;
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
        /** @var FileSystemSandbox $sandbox */
        $sandbox = $env->getFileSystem();
        $appRoot = Bootstrap::getInstance()->getAppRoot();
        $content = 'var test;';
        $sandbox->putFile($appRoot . '/vendor/justso/justtexts/justtexts-plugin.js', $content);
        $this->checkPlugin($env, $content);
    }

    public function testLoaderWithoutPlugins()
    {
        $env = $this->createTestEnvironment();
        $this->checkPlugin($env, '');
    }

    public function testLoaderWithMultiplePlugins()
    {
        $env = $this->createTestEnvironment();
        /** @var FileSystemSandbox $sandbox */
        $sandbox = $env->getFileSystem();
        $appRoot = Bootstrap::getInstance()->getAppRoot();
        $content = array('var test;', 'var test2;');
        $sandbox->putFile($appRoot . '/vendor/justso/test1/justtexts-plugin.js', $content[0]);
        $sandbox->putFile($appRoot . '/vendor/justso/test2/justtexts-plugin.js', $content[1]);
        $this->checkPlugin($env, implode('', $content));
    }

    /**
     * @param TestEnvironment $env
     * @param string          $content
     */
    private function checkPlugin(TestEnvironment $env, $content)
    {
        $service = new PluginLoader($env);
        $service->getAction();
        $this->assertEquals(
            array(
                'HTTP/1.0 200 Ok',
                'Content-Type: text/javascript; charset=utf-8',
            ),
            $env->getResponseHeader()
        );
        $this->assertEquals($content, $env->getResponseContent());
    }
}
