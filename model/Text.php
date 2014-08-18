<?php
/**
 * Definition of class Text
 * 
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\model
 */

namespace justso\justtexts\model;

use justso\justapi\Bootstrap;

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

    private $pageName;
    private $contents = array();
    private $outdated = array();

    /**
     * Initializes a text page
     * @param $pageName
     */
    public function __construct($pageName)
    {
        $this->pageName = $pageName;
        if (self::$languages === null) {
            $bootstrap = Bootstrap::getInstance();
            $config = $bootstrap->getConfiguration();
            self::$extraLangs = self::$languages = $config['languages'];
            self::$baseLang = array_shift(self::$extraLangs);
            self::$baseDir = $bootstrap->getAppRoot() . '/htdocs/nls/';
            self::$outdatedDir = $bootstrap->getAppRoot() . '/content/outdateInfo/';
        }
    }

    private function readFileContents($language)
    {
        $fileName = $this->getFileName($language);
        if (!file_exists($fileName)) {
            return array();
        }
        $content = json_decode(preg_replace('/^define\((.*)\);/s', '$1', file_get_contents($fileName)), true);
        if ($language === self::$baseLang) {
            $content = $content['root'];
        }

        $fileName = $this->getOutdateInfoFileName($language);
        if (file_exists($fileName)) {
            $outdateInfo = json_decode(file_get_contents($fileName), true);
        } else {
            $outdateInfo = array_fill_keys(array_keys($content), true);
        }
        foreach ($content as $id => $text) {
            $content[$id] = array('id' => $id, 'name' => $id, 'content' => $text, 'outdated' => $outdateInfo[$id]);
        }

        return $content;
    }

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
        $this->writeFile($this->getFileName($language), 'define(' . json_encode($content, JSON_PRETTY_PRINT) . ');');
        $this->writeFile($this->getOutdateInfoFileName($language), json_encode($outdateInfo, JSON_PRETTY_PRINT));
    }

    public function getAllTexts($language)
    {
        if (!isset($this->contents[$language])) {
            $this->contents[$language] = $this->readFileContents($language);
        }
        return $this->contents[$language];
    }

    public function getTextsWithBaseTexts($language)
    {
        $texts = $this->getAllTexts($language);

        if ($language !== self::$baseLang) {
            $baseTexts = $this->getAllTexts(self::$baseLang);

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
        $allTexts = $this->getAllTexts($language);
        if (isset($allTexts[$name])) {
            return $allTexts[$name];
        } else {
            return null;
        }
    }

    public function addTextContainer($name, $content, $language)
    {
        $allTexts = $this->getAllTexts($language);
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
            $allTexts = $this->getAllTexts($lang);
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
            $this->getAllTexts($lang);
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
            $this->getAllTexts($lang);
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
     * Write data to a file, creating the folder if it doesn't yet exists.
     *
     * @param $fileName
     * @param $content
     */
    private function writeFile($fileName, $content)
    {
        $dirname = dirname($fileName);
        if (!file_exists($dirname)) {
            mkdir($dirname, 0777, true);
        }
        file_put_contents($fileName, $content);
    }

    /**
     * Deletes a file if it exists
     *
     * @param $fileName
     */
    private function deleteFile($fileName)
    {
        if (file_exists($fileName)) {
            unlink($fileName);
        }
    }

    /**
     * Removes all files related to a text page
     */
    public function removeAll()
    {
        $this->deleteFile(self::$baseDir . $this->pageName . '.js');
        $this->deleteFile(self::$outdatedDir . self::$baseLang . '/' . $this->pageName . '.json');
        foreach (self::$extraLangs as $language) {
            $this->deleteFile(self::$baseDir . $language . '/' . $this->pageName . '.js');
            $this->deleteFile(self::$outdatedDir . $language . '/' . $this->pageName . '.json');
        }
    }
}
