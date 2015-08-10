<?php
/**
 * Definition of TextInterface
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justtexts\model
 */

namespace justso\justtexts\model;

use justso\justapi\FileSystemInterface;

interface TextInterface
{
    /**
     * Initializes a text page.
     *
     * @param FileSystemInterface $fs
     * @param string $pageName Name of page
     * @param string $appRoot Path where application is installed
     * @param string[] $languages List of short codes
     */
    public function __construct(FileSystemInterface $fs, $pageName, $appRoot, $languages);

    /**
     * Returns all texts of a page in the specified language.
     *
     * @param $language
     * @return mixed
     */
    public function getPageTexts($language);

    /**
     * Returns the texts including an information about the text in the base language.
     *
     * @param $language
     * @return array
     */
    public function getTextsWithBaseTexts($language);

    /**
     * Returns the text of a container in the specified language.
     * If the text is not defined, null is returned.
     *
     * @param string $name
     * @param string $language
     * @return array
     */
    public function getText($name, $language);

    /**
     * Adds a new text container.
     *
     * @param string $name
     * @param string $content
     * @param string $language
     * @return array
     * @throws \Exception
     */
    public function addTextContainer($name, $content, $language);

    /**
     * Modifies the content and optionally the name of a text container.
     * If the base language text is changed, the corresponding texts of other languages are invalidated.
     *
     * @param string $oldName
     * @param string $newName
     * @param string $content
     * @param string $language
     * @return array
     * @throws \Exception
     */
    public function modifyTextContainer($oldName, $newName, $content, $language);

    /**
     * Deletes a single text container
     *
     * @param $name
     */
    public function deleteTextContainer($name);

    /**
     * Removes all files related to a text page
     */
    public function removeAll();
}
