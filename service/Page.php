<?php
/**
 * Definition of class Pages
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    justso\service
 */

namespace justso\justtexts\service;

use justso\justapi\Bootstrap;
use justso\justapi\InvalidParameterException;
use justso\justapi\RestService;
use justso\justtexts\model\Text;

/**
 * Handles requests regarding generated website pages.
 *
 * @package    justso\service
 */
class Page extends RestService
{
    public function getAction()
    {
        $config = Bootstrap::getInstance()->getConfiguration();
        $id = $this->getPageId();
        if ($id !== null) {
            $this->assertPageExistence($id, $config);
            $result = $this->toJSONObject($id, $config['pages'][$id]);
        } else {
            $result = array();
            foreach ($config['pages'] as $pageName => $templateName) {
                $result[] = $this->toJSONObject($pageName, $templateName);
            }
        }
        $this->environment->sendJSONResult($result);
    }

    public function postAction()
    {
        $request = $this->environment->getRequestHelper();
        $config = Bootstrap::getInstance()->getConfiguration();
        $pageName = $request->getIdentifierParam('name');
        $templateName = $request->getParam('template');
        $this->assertPageExistence($pageName, $config, false);
        $this->changePageInfo($pageName, $templateName, $config);
    }

    public function putAction()
    {
        $request = $this->environment->getRequestHelper();
        $config = Bootstrap::getInstance()->getConfiguration();
        $id = $this->getPageId();
        $this->assertPageExistence($id, $config);
        $pageName = $request->getIdentifierParam('name');
        $templateName = $request->getParam('template');

        if ($id != $pageName) {
            $this->assertPageExistence($pageName, $config, false);
            unset($config['pages'][$id]);
        }
        $this->changePageInfo($pageName, $templateName, $config);
    }

    public function deleteAction()
    {
        $bootstrap = Bootstrap::getInstance();
        $config = $bootstrap->getConfiguration();
        $id = $this->getPageId();
        $this->assertPageExistence($id, $config);
        unset($config['pages'][$id]);
        $bootstrap->setConfiguration($config);

        $pageTexts = new Text($id, $bootstrap->getAppRoot(), $config['languages']);
        $pageTexts->removeAll();

        $this->environment->sendJSONResult('ok');
    }

    /**
     * @param string $pageName
     * @param string $templateName
     *
     * @return array
     */
    private function toJSONObject($pageName, $templateName)
    {
        return array('id' => $pageName, 'name' => $pageName, 'template' => $templateName);
    }

    /**
     * Checks that the specified page exists or not
     *
     * @param string  $pageName
     * @param mixed[] $config
     * @param bool    $exists
     *
     * @throws InvalidParameterException
     */
    private function assertPageExistence($pageName, $config, $exists = true)
    {
        if (empty($config['pages'][$pageName]) === $exists) {
            throw new InvalidParameterException($exists ? "Page not found" : "Page already exists");
        }
    }

    /**
     * @param string  $pageName
     * @param string  $templateName
     * @param mixed[] $config
     */
    private function changePageInfo($pageName, $templateName, $config)
    {
        $config['pages'][$pageName] = $templateName;
        Bootstrap::getInstance()->setConfiguration($config);
        $this->environment->sendJSONResult($this->toJSONObject($pageName, $templateName));
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
