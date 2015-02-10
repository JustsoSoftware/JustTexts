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

use justso\justapi\Bootstrap;
use justso\justapi\InvalidParameterException;
use justso\justapi\RestService;
use justso\justapi\SystemEnvironmentInterface;
use justso\justtexts\model;

/**
 * Handles requests regarding website page texts.
 *
 * @package    justso\service
 */
class Text extends RestService
{
    private $appRoot;
    private $languages;

    /**
     * Initialize private variables.
     *
     * @param SystemEnvironmentInterface $environment
     */
    public function __construct(SystemEnvironmentInterface $environment)
    {
        parent::__construct($environment);
        $bootstrap = Bootstrap::getInstance();
        $this->appRoot = $bootstrap->getAppRoot();
        $config = $bootstrap->getConfiguration();
        $this->languages = $config['languages'];
    }

    /**
     * Returns the content of a text container.
     *
     * @throws \justso\justapi\InvalidParameterException
     */
    public function getAction()
    {
        if (!preg_match('/\/page\/(\w+\-?\w*)\/text\/(..)(\/(\w+))?$/', $this->name, $matches)) {
            throw new InvalidParameterException("Invalid parameters");
        }
        $pageName = $matches[1];
        $language = $matches[2];
        $pageTexts = new model\Text($this->environment->getFileSystem(), $pageName, $this->appRoot, $this->languages);
        if (empty($matches[4])) {
            $result = array_values($pageTexts->getTextsWithBaseTexts($language));
        } else {
            $result = $pageTexts->getText($matches[4], $language);
        }
        $this->environment->sendJSONResult($result);
    }

    /**
     * Creates a new text container.
     *
     * @throws \justso\justapi\InvalidParameterException
     */
    public function postAction()
    {
        if (!preg_match('/\/page\/(\w+\-?\w*)\/text\/(..)$/', $this->name, $matches)) {
            throw new InvalidParameterException("Invalid parameters");
        }
        list($dummy, $pageName, $language) = $matches;

        $request = $this->environment->getRequestHelper();
        $name = $request->getIdentifierParam('name');
        $content = $request->getParam('content', '');

        try {
            $fs = $this->environment->getFileSystem();
            $pageTexts = new model\Text($fs, $pageName, $this->appRoot, $this->languages);
            $text = $pageTexts->addTextContainer($name, $content, $language);
            $this->environment->sendJSONResult($text);
        } catch (\Exception $e) {
            throw new InvalidParameterException($e->getMessage());
        }
    }

    /**
     * Changes an existing text container.
     *
     * @throws \justso\justapi\InvalidParameterException
     */
    public function putAction()
    {
        if (!preg_match('/\/page\/(\w+\-?\w*)\/text\/(..)\/(\w+)$/', $this->name, $matches)) {
            throw new InvalidParameterException("Invalid parameters");
        }
        list($dummy, $pageName, $language, $oldName) = $matches;

        $request = $this->environment->getRequestHelper();
        $newName = $request->getIdentifierParam('name');
        $content = $request->getParam('content', '');

        try {
            $fs = $this->environment->getFileSystem();
            $pageTexts = new model\Text($fs, $pageName, $this->appRoot, $this->languages);
            $text = $pageTexts->modifyTextContainer($oldName, $newName, $content, $language);
            $this->environment->sendJSONResult($text);
        } catch (\Exception $e) {
            throw new InvalidParameterException($e->getMessage());
        }
    }

    /**
     * Deletes a text container.
     *
     * @throws \justso\justapi\InvalidParameterException
     */
    public function deleteAction()
    {
        // @todo It's not necessary to specify a language, since the texts in all languages are removed.

        if (!preg_match('/\/page\/(\w+\-?\w*)\/text\/..\/(\w+)$/', $this->name, $matches)) {
            throw new InvalidParameterException("Invalid parameters");
        }
        list($dummy, $pageName, $containerName) = $matches;
        $pageTexts = new model\Text($this->environment->getFileSystem(), $pageName, $this->appRoot, $this->languages);
        $pageTexts->deleteTextContainer($containerName);

        $this->environment->sendJSONResult('ok');
    }
}
