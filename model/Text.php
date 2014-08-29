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
    private static $languages = null;
    private static $baseLang;
    private static $extraLangs;
    private static $baseDir;
    private static $outdatedDir;
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
        if (self::$languages === null) {
            self::$extraLangs = self::$languages = $languages;
            self::$baseLang = array_shift(self::$extraLangs);
            self::$baseDir = $appRoot . '/htdocs/nls/';
            self::$outdatedDir = $appRoot . '/content/outdateInfo/';
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
        if ($language === self::$baseLang) {
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
        if ($language === self::$baseLang) {
            $content = array('root' => $textInfo) + array_fill_keys(self::$extraLangs, true);
        } else {
            $content = $textInfo;
        }
        $this->fs->putFile($this->getFileName($language), 'define(' . json_encode($content, JSON_PRETTY_PRINT) . ');');
        $this->fs->putFile($this->getOutdateInfoFileName($language), json_encode($outdateInfo, JSON_PRETTY_PRINT));
    }

    /**
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

    public function getTextsWithBaseTexts($language)
    {
        $texts = $this->getPageTexts($language);

        if ($language !== self::$baseLang) {
            $baseTexts = $this->getPageTexts(self::$baseLang);

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

    public function getText($name, $language)
    {
        $allTexts = $this->getPageTexts($language);
        if (isset($allTexts[$name])) {
            return $allTexts[$name];
        } else {
            return null;
        }
    }

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
        if ($language === self::$baseLang) {
            $this->setExtraLanguagesOutdated($name, $content);
        }

        return $this->contents[$language][$name];
    }

    public function modifyTextContainer($oldName, $newName, $content, $language)
    {
        foreach (self::$languages as $lang) {
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
                unset($this->contents[$lang][$oldName]);
                $modified = true;
            }
            if ($language === $lang) {
                $this->contents[$lang][$newName]['content'] = $content;
                $this->contents[$lang][$newName]['outdated'] = false;
                $modified = true;
            } elseif ($language === self::$baseLang) {
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
        foreach (self::$languages as $lang) {
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
        if ($language === self::$baseLang) {
            return self::$baseDir . $this->pageName . '.js';
        } else {
            return self::$baseDir . $language . '/' . $this->pageName . '.js';
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
        foreach (self::$extraLangs as $lang) {
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
        return self::$outdatedDir . $language . '/' . $this->pageName . '.json';
    }

    /**
     * Removes all files related to a text page
     */
    public function removeAll()
    {
        $this->fs->deleteFile(self::$baseDir . $this->pageName . '.js');
        $this->fs->deleteFile(self::$outdatedDir . self::$baseLang . '/' . $this->pageName . '.json');
        foreach (self::$extraLangs as $language) {
            $this->fs->deleteFile(self::$baseDir . $language . '/' . $this->pageName . '.js');
            $this->fs->deleteFile(self::$outdatedDir . $language . '/' . $this->pageName . '.json');
        }
    }
}
