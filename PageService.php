<?php
/**
 * Definition of class Pages
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    justso\service
 */

namespace justso\justtexts;

use justso\justapi\InvalidParameterException;
use justso\justapi\RestService;
use justso\justapi\SystemEnvironmentInterface;

/**
 * Handles requests regarding generated website pages.
 *
 * @package    justso\service
 */
class PageService extends RestService
{
    /**
     * @var PageList
     */
    private $pageList;

    /**
     * Initializes the service.
     *
     * @param SystemEnvironmentInterface $environment
     */
    public function __construct(SystemEnvironmentInterface $environment)
    {
        parent::__construct($environment);
        $this->pageList = new PageList($environment);
    }

    /**
     * Sets the pageList.
     *
     * @param PageList $pageList
     */
    public function setPageList(PageList $pageList)
    {
        $this->pageList = $pageList;
    }

    /**
     * Yields a list of pages or a single page, if a page name is specified in the service name.
     */
    public function getAction()
    {
        $id = $this->getPageId();
        if ($id !== null) {
            $result = $this->pageList->getPage($id)->getJSON();
        } else {
            $result = array();
            foreach ($this->pageList->getPages() as $pageName) {
                $result[] = $this->pageList->getPage($pageName)->getJSON();
            }
        }
        $this->environment->sendJSONResult($result);
    }

    /**
     * Creates a new page
     */
    public function postAction()
    {
        $request = $this->environment->getRequestHelper();
        $id = $request->getIdentifierParam('name');
        try {
            $this->pageList->getPage($id);
        } catch (InvalidParameterException $e) {
            $page = $this->pageList->addPageFromRequest($id, $request);
            $this->environment->sendJSONResult($page->getJSON());
            return;
        }
        throw new InvalidParameterException("Page already exists");
    }

    public function putAction()
    {
        $request = $this->environment->getRequestHelper();
        $id = $this->getPageId();
        $this->pageList->getPage($id);
        $newName = $request->getIdentifierParam('name');
        if ($id != $newName) {
            if ($this->pageList->getPage($newName)) {
                throw new InvalidParameterException("Page already exists");
            }
            $this->pageList->renamePage($id, $newName);
        }
        $page = $this->pageList->changePageFromRequest($newName, $request);
        $this->environment->sendJSONResult($page->getJSON());
    }

    public function deleteAction()
    {
        $id = $this->getPageId();
        $this->pageList->deletePage($id);

        $arguments = [$this->environment, $id];
        /** @var \justso\justtexts\TextInterface $pageTexts */
        $pageTexts = $this->environment->getDIC()->get('\justso\justtexts\Text', $arguments);
        $pageTexts->removeAll();

        $this->environment->sendJSONResult('ok');
    }

    /**
     * Checks if the page is contained in service name or specified in parameter 'id' and returns the value then.
     *
     * @return mixed
     */
    private function getPageId()
    {
        if (preg_match('/\/page\/(\w+)$/', $this->name, $matches)) {
            $id = $matches[1];
        } else {
            $id = $this->environment->getRequestHelper()->getIdentifierParam('id', null, true);
        }
        return $id;
    }
}
