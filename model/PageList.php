<?php
/**
 * Definition of class PageList
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justtexts\model
 */

namespace justso\justtexts\model;

use justso\justapi\Bootstrap;
use justso\justapi\InvalidParameterException;
use justso\justapi\RequestHelper;

/**
 * Class PageList
 * @package justso\justtexts\model
 */
class PageList
{
    /**
     * Name of page model class
     * @var string
     */
    private $pageModel;

    /**
     * List of pages
     * @var Page[]
     */
    private $pages;

    /**
     * Initializes the page list
     */
    public function __construct($pageConfig = array(), $pageModel = null)
    {
        $this->pageModel = $pageModel ?: '\\justso\\justtexts\\model\\Page';
        $this->pages = array();
        foreach ($pageConfig as $key => $value) {
            /** @var \justso\justtexts\model\Page $page */
            $page = new $this->pageModel($key, $value);
            $this->pages[$page->getId()] = $page;
        }
    }

    /**
     * Returns a list of page names.
     *
     * @return string[]
     */
    public function getPages()
    {
        return array_keys($this->pages);
    }

    /**
     * Returns the Page object identified by its page name.
     *
     * @param string $name
     * @return Page
     * @throws \justso\justapi\InvalidParameterException
     */
    public function getPage($name)
    {
        if (!isset($this->pages[$name])) {
            throw new InvalidParameterException("Page not found");
        }
        return $this->pages[$name];
    }

    /**
     * Adds a new page with the given $id with data from the request.
     *
     * @param string        $id
     * @param RequestHelper $request
     * @return Page
     */
    public function addPageFromRequest($id, RequestHelper $request)
    {
        $page = new $this->pageModel(null, null, $request);
        $this->pages[$id] = $page;
        $this->persist();
        return $page;
    }

    /**
     * Changes a page's attributes according to data from the request.
     *
     * @param string        $id
     * @param RequestHelper $request
     * @return Page
     */
    public function changePageFromRequest($id, $request)
    {
        $this->getPage($id);
        $page = new $this->pageModel(null, null, $request);
        $this->pages[$id] = $page;
        $this->persist();
        return $page;
    }

    /**
     * Changes the id of a page.
     *
     * @param string $id
     * @param string $newName
     */
    public function renamePage($id, $newName)
    {
        $this->pages[$newName] = $this->getPage($id);
        unset($this->pages[$id]);
        $this->persist();
    }

    /**
     * Deletes a page.
     *
     * @param $id
     */
    public function deletePage($id)
    {
        $this->getPage($id);
        unset($this->pages[$id]);
        $this->persist();
    }

    /**
     * Persists page list in configuration file.
     */
    private function persist()
    {
        $config = Bootstrap::getInstance()->getConfiguration();
        $config['pages'] = array();
        foreach ($this->pages as $page) {
            $page->appendConfig($config['pages']);
        }
        Bootstrap::getInstance()->setConfiguration($config);
    }
}
