<?php
/**
 * Definition of class Text
 * 
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\model
 */

namespace justso\justtexts\model;

use justso\justapi\FileSystemInterface;

/**
 * Class Text
 * @package justso\model
 */
class Text
{
    private $languages = null;
    private $baseLang;
    private $extraLangs;
    private $baseDir;
    private $outdatedDir;
    /**
     * @var FileSystemInterface
     */
    private $fs;

    private $pageName;
    private $contents = array();

    /**
     * Initializes a text page.
     *
     * @param FileSystemInterface $fs
     * @param string $pageName Name of page
     * @param string $appRoot Path where application is installed
     * @param string[] $languages List of short codes
     */
    public function __construct(FileSystemInterface $fs, $pageName, $appRoot, $languages)
    {
        $this->pageName = $pageName;
        $this->fs = $fs;
        if ($this->languages === null) {
            $this->extraLangs = $this->languages = $languages;
            $this->baseLang = array_shift($this->extraLangs);
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
    private function readFileContents($language)
    {
        $fileName = $this->getFileName($language);
        if (!$this->fs->fileExists($fileName)) {
            return array();
        }
        $content = json_decode(preg_replace('/^define\((.*)\);/s', '$1', $this->fs->getFile($fileName)), true);
        if ($language === $this->baseLang) {
            $content = $content['root'];
        }

        $fileName = $this->getOutdateInfoFileName($language);
        if ($this->fs->fileExists($fileName)) {
            $outdateInfo = json_decode($this->fs->getFile($fileName), true);
        } else {
            $outdateInfo = array_fill_keys(array_keys($content), true);
        }
        foreach ($content as $id => $text) {
            $content[$id] = array('id' => $id, 'name' => $id, 'content' => $text, 'outdated' => $outdateInfo[$id]);
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
        $this->fs->putFile($this->getFileName($language), 'define(' . json_encode($content, $encodeFlags) . ');');
        $this->fs->putFile($this->getOutdateInfoFileName($language), json_encode($outdateInfo, $encodeFlags));
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
        $this->fs->deleteFile($this->baseDir . $this->pageName . '.js');
        $this->fs->deleteFile($this->outdatedDir . $this->baseLang . '/' . $this->pageName . '.json');
        foreach ($this->extraLangs as $language) {
            $this->fs->deleteFile($this->baseDir . $language . '/' . $this->pageName . '.js');
            $this->fs->deleteFile($this->outdatedDir . $language . '/' . $this->pageName . '.json');
        }
    }
}
