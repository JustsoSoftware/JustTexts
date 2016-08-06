<?php
/**
 * Definition of class PageList
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justtexts\model
 */

namespace justso\justtexts;

use justso\justapi\Bootstrap;
use justso\justapi\InvalidParameterException;
use justso\justapi\RequestHelper;
use justso\justapi\SystemEnvironmentInterface;

/**
 * Class PageList
 * @package justso\justtexts\model
 */
class PageList
{
    /**
     * List of pages
     * @var Page[]
     */
    private $pages;

    /** @var SystemEnvironmentInterface */
    private $env;

    /**
     * Initializes the page list
     */
    public function __construct(SystemEnvironmentInterface $env)
    {
        $this->env = $env;
        $this->pages = array();
        $configuration = $env->getBootstrap()->getConfiguration();
        foreach ($configuration['pages'] as $key => $value) {
            $page = $this->createPageObject($key, $value);
            $this->pages[$page->getId()] = $page;
        }
    }

    /**
     * @param string $key
     * @param string $value
     * @return \justso\justtexts\PageInterface
     */
    private function createPageObject($key, $value, RequestHelper $request = null)
    {
        return $this->env->getDIC()->get('\justso\justtexts\Page', [$key, $value, $request]);
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
        $page = $this->createPageObject(null, null, $request);
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
        $page = $this->createPageObject(null, null, $request);
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
        $bootstrap = Bootstrap::getInstance();
        $config = $bootstrap->getConfiguration();
        $config['pages'] = array();
        foreach ($this->pages as $page) {
            $page->appendConfig($config['pages']);
        }
        $bootstrap->setConfiguration($config);
    }
}
