<?php
/**
 * Definition of class Text
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\model
 */

namespace justso\justtexts;

use justso\justapi\SystemEnvironmentInterface;

/**
 * Class Text
 * @package justso\model
 */
class Text implements TextInterface
{
    protected $languages = null;
    protected $baseLang;
    protected $extraLangs;
    protected $baseDir;
    protected $outdatedDir;

    /**
     * @var SystemEnvironmentInterface
     */
    protected $env;

    protected $pageName;
    protected $contents = array();

    /**
     * Initializes a text page.
     *
     * @param SystemEnvironmentInterface $env
     * @param string $pageName Name of page
     */
    public function __construct(SystemEnvironmentInterface $env, $pageName)
    {
        $this->env = $env;
        $this->pageName = $pageName;
        if ($this->languages === null) {
            $bootstrap = $env->getBootstrap();
            $configuration = $bootstrap->getConfiguration();
            $this->extraLangs = $this->languages = $configuration['languages'];
            $this->baseLang = array_shift($this->extraLangs);
            $appRoot = $bootstrap->getAppRoot();
            $this->baseDir = $appRoot . '/htdocs/nls/';
            $this->outdatedDir = $appRoot . '/content/outdateInfo/';
        }
    }

    /**
     * Reads the contents of a language file and returns it.
     *
     * @param string $language language code
     * @return array
     */
    protected function readFileContents($language)
    {
        $fileName = $this->getFileName($language);
        $fs = $this->env->getFileSystem();
        if (!$fs->fileExists($fileName)) {
            return array();
        }
        $content = json_decode(preg_replace('/^.*?define\((.*)\);\s*/s', '$1', $fs->getFile($fileName)), true);
        if ($language === $this->baseLang) {
            $content = $content['root'];
        }

        $outdateInfo = [];
        $fileName = $this->getOutdateInfoFileName($language);
        if ($fs->fileExists($fileName)) {
            $outdateInfo = json_decode($fs->getFile($fileName), true);
        }
        foreach ($content as $id => $text) {
            $outdated = isset($outdateInfo[$id]) ? $outdateInfo[$id] : true;
            $content[$id] = array('id' => $id, 'name' => $id, 'content' => $text, 'outdated' => $outdated);
        }

        return $content;
    }

    /**
     * Writes the current texts of the specified language to the file system.
     *
     * @param string $language
     */
    private function writeTextsToFile($language)
    {
        $textInfo = array();
        $outdateInfo = array();
        foreach ($this->contents[$language] as $id => $info) {
            $textInfo[$id] = $info['content'];
            $outdateInfo[$id] = $info['outdated'];
        }
        if ($language === $this->baseLang) {
            $content = array('root' => $textInfo) + array_fill_keys($this->extraLangs, true);
        } else {
            $content = $textInfo;
        }
        $encodeFlags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;
        $content = "'use strict';\n/*global define*/\ndefine(" . json_encode($content, $encodeFlags) . ");\n";
        $fs = $this->env->getFileSystem();
        $fs->putFile($this->getFileName($language), $content);
        $fs->putFile($this->getOutdateInfoFileName($language), json_encode($outdateInfo, $encodeFlags));
    }

    /**
     * Returns all texts of a page in the specified language.
     *
     * @param $language
     * @return mixed
     */
    public function getPageTexts($language)
    {
        if (!isset($this->contents[$language])) {
            $this->contents[$language] = $this->readFileContents($language);
        }
        return $this->contents[$language];
    }

    /**
     * Returns the texts including an information about the text in the base language.
     *
     * @param $language
     * @return array
     */
    public function getTextsWithBaseTexts($language)
    {
        $texts = $this->getPageTexts($language);

        if ($language !== $this->baseLang) {
            $baseTexts = $this->getPageTexts($this->baseLang);

            $texts = array_map(
                function ($text, $baseText) {
                    return $text + array("basecontent" => $baseText['content']);
                },
                $texts,
                $baseTexts
            );
        }

        return $texts;
    }

    /**
     * Returns the text of a container in the specified language.
     * If the text is not defined, null is returned.
     *
     * @param string $name
     * @param string $language
     * @return array
     */
    public function getText($name, $language)
    {
        $allTexts = $this->getPageTexts($language);
        if (isset($allTexts[$name])) {
            return $allTexts[$name];
        } else {
            return null;
        }
    }

    /**
     * Adds a new text container.
     *
     * @param string $name
     * @param string $content
     * @param string $language
     * @return array
     * @throws \Exception
     */
    public function addTextContainer($name, $content, $language)
    {
        $allTexts = $this->getPageTexts($language);
        if (isset($allTexts[$name])) {
            throw new \Exception("Text container name already used.");
        }
        $this->contents[$language][$name] = array(
            'id'       => $name,
            'name'     => $name,
            'content'  => $content,
            'outdated' => false
        );
        $this->writeTextsToFile($language);
        if ($language === $this->baseLang) {
            $this->setExtraLanguagesOutdated($name, $content);
        }

        return $this->contents[$language][$name];
    }

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
    public function modifyTextContainer($oldName, $newName, $content, $language)
    {
        foreach ($this->languages as $lang) {
            $modified = false;
            $allTexts = $this->getPageTexts($lang);
            if (!isset($allTexts[$oldName])) {
                throw new \Exception("Text container unknown.");
            }
            if ($oldName !== $newName) {
                if (isset($allTexts[$newName])) {
                    throw new \Exception("Text container already exists");
                }
                $this->contents[$lang][$newName] = $this->contents[$lang][$oldName];
                $this->contents[$lang][$newName]['id'] = $newName;
                $this->contents[$lang][$newName]['name'] = $newName;
                unset($this->contents[$lang][$oldName]);
                $modified = true;
            }
            if ($language === $lang) {
                $this->contents[$lang][$newName]['content'] = $content;
                $this->contents[$lang][$newName]['outdated'] = false;
                $modified = true;
            } elseif ($language === $this->baseLang) {
                $this->contents[$lang][$newName]['outdated'] = true;
                $modified = true;
            }
            if ($modified) {
                $this->writeTextsToFile($lang);
            }
        }
        return $this->contents[$language][$newName];
    }

    /**
     * Deletes a single text container
     *
     * @param $name
     */
    public function deleteTextContainer($name)
    {
        foreach ($this->languages as $lang) {
            $this->getPageTexts($lang);
            unset($this->contents[$lang][$name]);
            $this->writeTextsToFile($lang);
        }
    }

    /**
     * Returns the name of the file containing the texts.
     *
     * @param $language
     * @return string
     */
    private function getFileName($language)
    {
        if ($language === $this->baseLang) {
            return $this->baseDir . $this->pageName . '.js';
        } else {
            return $this->baseDir . $language . '/' . $this->pageName . '.js';
        }
    }

    /**
     * Sets all extra language texts to 'outdated'
     *
     * @param $name
     * @param $content
     */
    private function setExtraLanguagesOutdated($name, $content)
    {
        foreach ($this->extraLangs as $lang) {
            $this->getPageTexts($lang);
            $this->contents[$lang][$name]['outdated'] = true;
            if (!isset($this->contents[$lang][$name]['content'])) {
                $this->contents[$lang][$name]['content'] = $content;
            }
            $this->writeTextsToFile($lang);
        }
    }

    /**
     * @param $language
     * @return string
     */
    private function getOutdateInfoFileName($language)
    {
        return $this->outdatedDir . $language . '/' . $this->pageName . '.json';
    }

    /**
     * Removes all files related to a text page
     */
    public function removeAll()
    {
        $fs = $this->env->getFileSystem();
        $fs->deleteFile($this->baseDir . $this->pageName . '.js');
        $fs->deleteFile($this->outdatedDir . $this->baseLang . '/' . $this->pageName . '.json');
        foreach ($this->extraLangs as $language) {
            $fs->deleteFile($this->baseDir . $language . '/' . $this->pageName . '.js');
            $fs->deleteFile($this->outdatedDir . $language . '/' . $this->pageName . '.json');
        }
    }
}
