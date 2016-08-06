<?php
/**
 * Definition of Text service
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
 * Handles requests regarding website page texts.
 *
 * @package    justso\service
 */
class TextService extends RestService
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
        $bootstrap = $environment->getBootstrap();
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
        if (!preg_match('/\/page\/(\w+[\-\w]*)\/text\/(..)(\/(\w+))?$/', $this->name, $matches)) {
            throw new InvalidParameterException("Invalid parameters");
        }
        $pageName = $matches[1];
        $language = $matches[2];
        $pageTexts = $this->getTextModel($pageName);
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
        if (!preg_match('/\/page\/(\w+[\-\w]*)\/text\/(..)$/', $this->name, $matches)) {
            throw new InvalidParameterException("Invalid parameters");
        }
        list($dummy, $pageName, $language) = $matches;

        $request = $this->environment->getRequestHelper();
        $name = $request->getIdentifierParam('name');
        $content = $request->getParam('content', '');

        try {
            $pageTexts = $this->getTextModel($pageName);
            $text = $pageTexts->addTextContainer($name, $this->filter($content), $language);
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
        if (!preg_match('/\/page\/(\w+[\-\w]*)\/text\/(..)\/(\w+)$/', $this->name, $matches)) {
            throw new InvalidParameterException("Invalid parameters");
        }
        list($dummy, $pageName, $language, $oldName) = $matches;

        $request = $this->environment->getRequestHelper();
        $newName = $request->getIdentifierParam('name');
        $content = $request->getParam('content', '');

        try {
            $pageTexts = $this->getTextModel($pageName);
            $text = $pageTexts->modifyTextContainer($oldName, $newName, $this->filter($content), $language);
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

        if (!preg_match('/\/page\/(\w+[\-\w]*)\/text\/..\/(\w+)$/', $this->name, $matches)) {
            throw new InvalidParameterException("Invalid parameters");
        }
        list($dummy, $pageName, $containerName) = $matches;
        $pageTexts = $this->getTextModel($pageName);
        $pageTexts->deleteTextContainer($containerName);

        $this->environment->sendJSONResult('ok');
    }

    /**
     * @param string $pageName
     * @return Text
     */
    private function getTextModel($pageName)
    {
        return $this->environment->getDIC()->get('\justso\justtexts\Text', [
            $this->environment,
            $pageName
        ]);
    }

    /**
     * Filters out unwanted html code like formatting and so on
     *
     * @param string $content
     * @return string
     */
    private function filter($content)
    {
        return strip_tags($content, '<p><br>');
    }
}
