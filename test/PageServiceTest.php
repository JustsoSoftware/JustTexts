<?php
/**
 * Definition of class PageServiceTest
 * 
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justtexts\test
 */

namespace justso\justtexts\test;

use justso\justapi\test\ServiceTestBase;
use justso\justtexts\model\PageList;
use justso\justtexts\service\Page;

/**
 * Class PageServiceTest
 * @package justso\justtexts\test
 */
class PageServiceTest extends ServiceTestBase
{
    public function testGetPageList()
    {
        $env = $this->createTestEnvironment();
        $pageList = new PageList(array('abc', 'def'));
        $service = new Page($env);
        $service->setPageList($pageList);
        $service->getAction();
        $this->assertJSONHeader($env);
        $this->assertSame('[{"id":"abc","name":"abc"},{"id":"def","name":"def"}]', $env->getResponseContent());
    }

    public function testGetSinglePage()
    {
        $env = $this->createTestEnvironment();
        $pageList = new PageList(array('abc', 'def'));
        $service = new Page($env);
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
        $page = new \justso\justtexts\model\Page('test', 'test', $request);
        $mockBuilder = $this->getMockBuilder('\\justso\\justtexts\\model\\PageList');
        $mockBuilder->setConstructorArgs(array(array('abc', 'def')));
        $pageList = $mockBuilder->getMock();
        $pageList->expects($this->once())->method('addPageFromRequest')->with('test', $request)->will($this->returnValue($page));
        $service = new Page($env);
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
        $pageList = new PageList(array('abc', 'def'));
        $service = new Page($env);
        $service->setPageList($pageList);
        $service->postAction();
    }

    public function testChangePage()
    {
        $env = $this->createTestEnvironment(array('name' => 'test'));
        $request = $env->getRequestHelper();
        $page = new \justso\justtexts\model\Page('test', 'test', $request);
        $mockBuilder = $this->getMockBuilder('\\justso\\justtexts\\model\\PageList');
        $mockBuilder->setConstructorArgs(array(array('abc', 'def')));
        $pageList = $mockBuilder->getMock();
        $pageList->expects($this->once())->method('changePageFromRequest')->with('test', $request)->will($this->returnValue($page));
        $service = new Page($env);
        $service->setName('/page/abc');
        $service->setPageList($pageList);
        $service->putAction();
        $this->assertJSONHeader($env);
        $this->assertSame('{"id":"test","name":"test"}', $env->getResponseContent());
    }

    public function testDeletePage()
    {
        $env = $this->createTestEnvironment(array('name' => 'abc'));
        $mockBuilder = $this->getMockBuilder('\\justso\\justtexts\\model\\PageList');
        $mockBuilder->setConstructorArgs(array(array('abc', 'def')));
        $pageList = $mockBuilder->getMock();
        $pageList->expects($this->once())->method('deletePage')->with('abc');
        $service = new Page($env);
        $service->setName('/page/abc');
        $service->setPageList($pageList);
        $service->deleteAction();
        $this->assertJSONHeader($env);
        $this->assertSame('"ok"', $env->getResponseContent());
    }
}
