<?php
/**
 * Definition of class TextServiceTest
 * 
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justtexts\test
 */

namespace justso\justtexts\test;

use justso\justapi\Bootstrap;
use justso\justapi\test\ServiceTestBase;
use justso\justapi\test\TestEnvironment;
use justso\justtexts\service\Text;

/**
 * Class TextServiceTest
 * @package justso\justtexts\test
 */
class TextServiceTest extends ServiceTestBase
{
    const TEST_TEXT = '{"id":"Test","name":"Test","content":"Hallo Welt!","outdated":true}';

    /**
     * @var TestEnvironment
     */
    private $env;

    protected function setUp()
    {
        parent::setUp();
        $this->env = $this->createTestEnvironment();
        /** @var \justso\justapi\test\FileSystemSandbox $sandbox */
        $sandbox = $this->env->getFileSystem();
        $appRoot = Bootstrap::getInstance()->getAppRoot();
        $sandbox->putFile($appRoot . '/htdocs/nls/empty.js', 'define({"root":{}});');
        $sandbox->putFile($appRoot . '/htdocs/nls/index.js', 'define({"root":{"Test":"Hallo Welt!"}});');
        $sandbox->resetProtocol();
    }

    public function tearDown()
    {
        parent::tearDown();
        /** @var \justso\justapi\test\FileSystemSandbox $sandbox */
        $sandbox = $this->env->getFileSystem();
        $protocol = $sandbox->getProtocol();
        if (!empty($protocol)) {
            // print_r($protocol);
        }
        $sandbox->cleanUpSandbox();
    }

    public function testGetAllTextsOnEmptyPage()
    {
        $service = new Text($this->env);
        $service->setName('/page/empty/text/de');
        $service->getAction();
        $this->assertJSONHeader($this->env);
        $this->assertSame('[]', $this->env->getResponseContent());
    }

    public function testGetAllTextsOnNonEmtpyPage()
    {
        $service = new Text($this->env);
        $service->setName('/page/index/text/de');
        $service->getAction();
        $this->assertJSONHeader($this->env);
        $this->assertSame('[' . self::TEST_TEXT . ']', $this->env->getResponseContent());
    }

    public function testGetExistingText()
    {
        $service = new Text($this->env);
        $service->setName('/page/index/text/de/Test');
        $service->getAction();
        $this->assertJSONHeader($this->env);
        $this->assertSame(self::TEST_TEXT, $this->env->getResponseContent());
    }

    public function testGetANonExistingText()
    {
        $service = new Text($this->env);
        $service->setName('/page/index/text/de/abc');
        $service->getAction();
        $this->assertJSONHeader($this->env);
        $this->assertSame('null', $this->env->getResponseContent());
    }

    public function testPostAction()
    {

    }

    public function testPutAction()
    {

    }

    public function testDeleteAction()
    {

    }
}
