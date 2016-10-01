<?php
/**
 * Definition of class PageServiceTest
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justtexts\test
 */

namespace justso\justtexts;

use justso\justapi\testutil\ServiceTestBase;

/**
 * Class PageServiceTest
 * @package justso\justtexts\test
 */
class PageServiceTest extends ServiceTestBase
{
    public function testGetPageList()
    {
        $env = $this->createTestEnvironment();
        $pageList = new PageList($env);
        $service = new PageService($env);
        $service->setPageList($pageList);
        $service->getAction();
        $this->assertJSONHeader($env);
        $this->assertSame('[{"id":"abc","name":"abc"},{"id":"def","name":"def"}]', $env->getResponseContent());
    }

    public function testGetSinglePage()
    {
        $env = $this->createTestEnvironment();
        $pageList = new PageList($env);
        $service = new PageService($env);
        $service->setPageList($pageList);
        $service->setName('/page/def');
        $service->getAction();
        $this->assertJSONHeader($env);
        $this->assertSame('{"id":"def","name":"def"}', $env->getResponseContent());
    }

    public function testAddPage()
    {
        $env = $this->createTestEnvironment(array('name' => 'test'));
        $request = $env->getRequestHelper();
        $page = new \justso\justtexts\Page('test', 'test', $request);
        $mockBuilder = $this->getMockBuilder('\justso\justtexts\PageList');
        $mockBuilder->setConstructorArgs(array($env));
        $pageList = $mockBuilder->getMock();
        $pageList->expects($this->once())->method('getPage')->with('test')
            ->will($this->throwException(new \justso\justapi\InvalidParameterException()));
        $pageList->expects($this->once())->method('addPageFromRequest')->with('test', $request)
            ->will($this->returnValue($page));
        $service = new PageService($env);
        $service->setPageList($pageList);
        $service->postAction();
        $this->assertJSONHeader($env);
        $this->assertSame('{"id":"test","name":"test"}', $env->getResponseContent());
    }

    /**
     * @expectedException \justso\justapi\InvalidParameterException
     */
    public function testAddExistingPage()
    {
        $env = $this->createTestEnvironment(array('name' => 'abc'));
        $pageList = new PageList($env);
        $service = new PageService($env);
        $service->setPageList($pageList);
        $service->postAction();
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function testChangePage()
    {
        $env = $this->createTestEnvironment(array('name' => 'test'));
        $request = $env->getRequestHelper();
        $page = new \justso\justtexts\Page('test', 'test', $request);
        $mockBuilder = $this->getMockBuilder('\justso\justtexts\PageList');
        $mockBuilder->setConstructorArgs(array($env));
        $pageList = $mockBuilder->getMock();
        $pageList->expects($this->once())->method('changePageFromRequest')->with('test', $request)
            ->will($this->returnValue($page));
        $service = new PageService($env);
        $service->setName('/page/abc');
        $service->setPageList($pageList);
        $service->putAction();
        $this->assertJSONHeader($env);
        $this->assertSame('{"id":"test","name":"test"}', $env->getResponseContent());
    }

    public function testDeletePage()
    {
        $env = $this->createTestEnvironment(array('name' => 'abc'));
        $mockBuilder = $this->getMockBuilder('\justso\justtexts\PageList');
        $mockBuilder->setConstructorArgs(array($env));
        $pageList = $mockBuilder->getMock();
        $pageList->expects($this->once())->method('deletePage')->with('abc');
        $service = new PageService($env);
        $service->setName('/page/abc');
        $service->setPageList($pageList);
        $service->deleteAction();
        $this->assertJSONHeader($env);
        $this->assertSame('"ok"', $env->getResponseContent());
    }

    protected function createTestEnvironment(array $params = [], array $header = [], array $server = [])
    {
        $env = parent::createTestEnvironment($params, $header, $server);
        $config = array(
            'environments' => array('test' => array('approot' => '/tmp')),
            'languages' => array('de'),
            'pages' => array('abc', 'def')
        );
        $env->getBootstrap()->setTestConfiguration('/tmp', $config);
        return $env;
    }
}
