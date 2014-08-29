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
use justso\justapi\RestService;
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

    private $indexFileName;

    protected function setUp()
    {
        parent::setUp();
        $this->env = $this->createTestEnvironment();
        /** @var \justso\justapi\test\FileSystemSandbox $sandbox */
        $sandbox = $this->env->getFileSystem();
        $appRoot = Bootstrap::getInstance()->getAppRoot();
        $this->indexFileName = $appRoot . '/htdocs/nls/index.js';
        $sandbox->putFile($appRoot . '/htdocs/nls/empty.js', 'define({"root":{}});');
        $sandbox->putFile($this->indexFileName, 'define({"root":{"Test":"Hallo Welt!"}});');
        $sandbox->putFile($appRoot . '/htdocs/nls/en/index.js', 'define({"Test":"Hello World!"});');
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
        $this->checkTextContent($service, null, '[' . self::TEST_TEXT . ']');
    }

    public function testGetExistingText()
    {
        $service = new Text($this->env);
        $this->checkTextContent($service, 'Test', self::TEST_TEXT);
    }

    public function testGetANonExistingText()
    {
        $service = new Text($this->env);
        $this->checkTextContent($service, 'abc', 'null');
    }

    /**
     * @expectedException \justso\justapi\InvalidParameterException
     */
    public function testInvalidGet()
    {
        $service = new Text($this->env);
        $service->setName('/page/index/text/abc/def');
        $service->getAction();
    }

    public function testPostAction()
    {
        $service = new Text($this->env);
        $this->env->getRequestHelper()->set(array('name' => 'NewText', 'content' => 'NewText content'));
        $service->setName('/page/index/text/de');
        $service->postAction();
        $this->assertJSONHeader($this->env);
        $expected = '{"id":"NewText","name":"NewText","content":"NewText content","outdated":false}';
        $this->assertSame($expected, $this->env->getResponseContent());

        $this->checkTextContent($service, 'NewText', $expected);
    }

    /**
     * @expectedException \justso\justapi\InvalidParameterException
     */
    public function testInvalidPost()
    {
        $service = new Text($this->env);
        $service->setName('/page/index/text/abc');
        $service->postAction();
    }

    public function testPutAction()
    {
        $service = new Text($this->env);
        $this->env->getRequestHelper()->set(array('name' => 'NewText', 'content' => 'NewText content'));
        $service->setName('/page/index/text/de/Test');
        $service->putAction();
        $this->assertJSONHeader($this->env);
        $expected = '{"id":"NewText","name":"NewText","content":"NewText content","outdated":false}';
        $this->assertSame($expected, $this->env->getResponseContent());

        foreach (array('Test' => 'null', 'NewText' => $expected) as $container => $expected) {
            $this->checkTextContent($service, $container, $expected);
        }
    }

    /**
     * @expectedException \justso\justapi\InvalidParameterException
     */
    public function testInvalidPut()
    {
        $service = new Text($this->env);
        $service->setName('/page/index/text/abc');
        $service->postAction();
    }

    public function testDeleteAction()
    {
        $service = new Text($this->env);
        $service->setName('/page/index/text/de/Test');
        $service->deleteAction();
        $this->assertJSONHeader($this->env);
        $this->assertSame('"ok"', $this->env->getResponseContent());
    }

    /**
     * @param RestService $service
     * @param string      $container
     * @param string      $expected
     */
    private function checkTextContent(RestService $service, $container, $expected)
    {
        $this->env->clearResponse();
        $service->setName('/page/index/text/de' . ($container ? '/' . $container : ''));
        $service->getAction();
        $this->assertJSONHeader($this->env);
        $this->assertSame($expected, $this->env->getResponseContent());
    }
}