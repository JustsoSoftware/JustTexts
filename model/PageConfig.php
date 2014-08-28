<?php
/**
 * Definition of class PageConfig
 * 
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justtexts\model
 */

namespace justso\justtexts\model;

use justso\justapi\Bootstrap;
use justso\justapi\InvalidParameterException;

/**
 * Class PageConfig
 * @package justso\justtexts\model
 */
class PageConfig
{
    /**
     * @var Bootstrap
     */
    private $bootstrap;

    /**
     * @var mixed
     */
    private $pages = array();

    public function __construct(Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    public function loadFromConfigFile()
    {
        $config = $this->bootstrap->getConfiguration();
        $this->pages = $config['pages'];
    }

    public function storeInConfigFile()
    {
        $config = $this->bootstrap->getConfiguration();
        $config['pages'] = $this->pages;
        $this->bootstrap->setConfiguration($config);
    }

    public function setPage($id, Page $page)
    {
        $this->pages[$id] = $page->getConfig();
    }

    public function deletePage($id)
    {
        if (!isset($this->pages[$id])) {
            throw new InvalidParameterException("Page not found in config");
        }
        unset($this->pages[$id]);
    }
}
