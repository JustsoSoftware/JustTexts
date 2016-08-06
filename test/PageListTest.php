<?php
/**
 * Definition of class PageListTest
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justtexts\test
 */

namespace justso\justtexts;

use justso\justapi\Bootstrap;
use justso\justapi\RequestHelper;
use justso\justapi\testutil\TestEnvironment;

/**
 * Class PageListTest
 * @package justso\justtexts\test
 */
class PageListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PageList
     */
    private $list;

    /**
     * Setup a test configuration containing a defined set of pages.
     */
    protected function setUp()
    {
        parent::setUp();
        $config = array(
            'environments' => array('test' => array('approot' => '/tmp')),
            'languages' => array('de'),
            'pages' => array('abc', 'def')
        );
        $env = new TestEnvironment(new RequestHelper());
        $env->getBootstrap()->setTestConfiguration('/tmp', $config);
        $this->list = new PageList($env);
    }

    /**
     * Reset configuration after tests.
     */
    protected function tearDown()
    {
        parent::tearDown();
        Bootstrap::getInstance()->resetConfiguration();
        if (file_exists('/tmp/config.json')) {
            unlink('/tmp/config.json');
        }
    }

    public function testGetPages()
    {
        $this->assertSame(array('abc', 'def'), $this->list->getPages());
    }

    public function testGetPage()
    {
        $page = $this->list->getPage('abc');
        $this->assertSame('justso\justtexts\Page', get_class($page));
        $this->assertSame('abc', $page->getId());
    }

    public function testAddPageFromRequest()
    {
        $request = new RequestHelper();
        $request->fillWithData(array('name' => 'ghi'));
        $this->list->addPageFromRequest('ghi', $request);
        $this->assertEquals(array('abc', 'def', 'ghi'), $this->list->getPages());
        $page = $this->list->getPage('ghi');
        $this->assertSame('justso\justtexts\Page', get_class($page));
        $this->assertSame('ghi', $page->getId());
    }

    public function testChangePage()
    {
        $request = new RequestHelper();
        $request->fillWithData(array('name' => 'def'));
        $this->list->changePageFromRequest('def', $request);
        $this->assertEquals(array('abc', 'def'), $this->list->getPages());
        $page = $this->list->getPage('def');
        $this->assertSame('justso\justtexts\Page', get_class($page));
        $this->assertSame('def', $page->getId());
    }

    public function testRename()
    {
        $this->list->renamePage('def', 'ghi');
        $this->assertEquals(array('abc', 'ghi'), $this->list->getPages());
        $page = $this->list->getPage('ghi');
        $this->assertSame('justso\justtexts\Page', get_class($page));
    }

    public function testDeletePage()
    {
        $this->list->deletePage('abc');
        $this->assertSame(array('def'), $this->list->getPages());
    }
}
