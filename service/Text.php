<?php
/**
 * Definition of Text service
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    justso\service
 */

namespace justso\justtexts\service;

use justso\justapi\InvalidParameterException;
use justso\justapi\RestService;
use justso\justtexts\model;

/**
 * Handles requests regarding website page texts.
 *
 * @package    justso\service
 */
class Text extends RestService
{
    public function getAction()
    {
        if (!preg_match('/\/page\/(\w+)\/text\/(..)(\/(\w+))?$/', $this->name, $matches)) {
            throw new InvalidParameterException("Invalid parameters");
        }
        $pageName = $matches[1];
        $language = $matches[2];
        $pageTexts = new model\Text($pageName);
        if (empty($matches[4])) {
            $result = array_values($pageTexts->getTextsWithBaseTexts($language));
        } else {
            $result = $pageTexts->getText($matches[4], $language);
        }
        $this->environment->sendJSONResult($result);
    }

    public function postAction()
    {
        if (!preg_match('/\/page\/(\w+)\/text\/(..)$/', $this->name, $matches)) {
            throw new InvalidParameterException("Invalid parameters");
        }
        list($dummy, $pageName, $language) = $matches;

        $request = $this->environment->getRequestHelper();
        $name = $request->getIdentifierParam('name');
        $content = $request->getParam('content', '');

        try {
            $pageTexts = new model\Text($pageName);
            $text = $pageTexts->addTextContainer($name, $content, $language);
            $this->environment->sendJSONResult($text);
        } catch (\Exception $e) {
            throw new InvalidParameterException($e->getMessage());
        }
    }

    public function putAction()
    {
        if (!preg_match('/\/page\/(\w+)\/text\/(..)\/(\w+)$/', $this->name, $matches)) {
            throw new InvalidParameterException("Invalid parameters");
        }
        list($dummy, $pageName, $language, $oldName) = $matches;

        $request = $this->environment->getRequestHelper();
        $newName = $request->getIdentifierParam('name');
        $content = $request->getParam('content', '');

        try {
            $pageTexts = new model\Text($pageName);
            $text = $pageTexts->modifyTextContainer($oldName, $newName, $content, $language);
            $this->environment->sendJSONResult($text);
        } catch (\Exception $e) {
            throw new InvalidParameterException($e->getMessage());
        }
    }

    public function deleteAction()
    {
        if (!preg_match('/\/page\/(\w+)\/text\/..\/(\w+)$/', $this->name, $matches)) {
            throw new InvalidParameterException("Invalid parameters");
        }
        list($dummy, $pageName, $containerName) = $matches;
        $pageTexts = new model\Text($pageName);
        $pageTexts->deleteTextContainer($containerName);

        $this->environment->sendJSONResult('ok');
    }
}
