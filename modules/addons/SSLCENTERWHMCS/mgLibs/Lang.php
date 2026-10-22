<?php

namespace MGModule\SSLCENTERWHMCS\mgLibs;

use MGModule\SSLCENTERWHMCS as main;

/**
 * Simple class to translating languages
 * 
 * @author Michal Czech <michael@modulesgarden.com>
 * @SuppressWarnings(PHPMD)
 */
class Lang
{

    /**
     *
     * @var \MGModule\SSLCENTERWHMCS\Lang
     */
    private static $instance;
    private $dir;

    /**
     *
     * @var Array
     */
    private $langs          = array();
    private $currentLang;
    private $fillLangFile   = true;
    public $context         = array();
    private $staggedContext = array();
    private $missingLangs   = array();

    private function __construct()
    {
        
    }

    private function __clone()
    {
        
    }

    /**
     * Get Single-ton Instance
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param type $dir
     * @param type $lang
     * @return \MGModule\SSLCENTERWHMCS\Lang
     */
    public static function getInstance($dir = null, $lang = null)
    {
        if (self::$instance === null)
        {
            self::$instance      = new self();
            self::$instance->dir = $dir;
            self::$instance->loadLang('english');

            self::$instance->fillLangFile = process\MainInstance::I()->isDebug();

            if (!$lang)
            {
                $lang = self::getLang();
            }

            if ($lang && $lang != 'english')
            {
                self::$instance->loadLang($lang);
            }
        }
        return self::$instance;
    }

    public static function getMissingLangs()
    {
        return self::$instance->missingLangs;
    }

    /**
     * Get Current Lang Name
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @return string
     */
    public static function getLang()
    {
        //TODO
        $language = '';
        if (isset($_SESSION['Language'])) // GET LANG FROM SESSION
        {
            $language = strtolower($_SESSION['Language']);
        }
        elseif (isset($_SESSION['uid']))
        {
            $row = MySQL\Query::query("SELECT language FROM tblclients WHERE id = " . $_SESSION['uid'])->fetch();
            if ($row['language'])
            {
                $language = $row['language'];
            }
        }

        if (!$language)
        {
            $row = MySQL\Query::query("SELECT value FROM tblconfiguration WHERE setting = 'Language' LIMIT 1")->fetch();
            $language = $row['value'];
        }

        if (!$language)
        {
            $language = 'english';
        }


        return strtolower($language);
    }

    /**
     * Get Avaiable Translations
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @return type
     */
    public static function getAvaiable()
    {
        $langArray = array();
        $handle    = opendir(self::$instance->dir);

        while (false !== ($entry = readdir($handle)))
        {
            list($lang, $ext) = explode('.', $entry);
            if ($lang && isset($ext) && strtolower($ext) == 'php')
            {
                $langArray[] = $lang;
            }
        }

        return $langArray;
    }

    /**
     * Load Lang File
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param string $lang Lang Name
     */
    public static function loadLang($lang)
    {
        // The addon and server modules share one language instance, but each has its own langs folder
        // (client area texts live in the server module, admin texts in the addon). Load both, key by key:
        // the other module's file first, then this module's file, then the overrides of both (overrides win).
        $dirs = self::getLangDirs();

        $files = [];
        foreach ($dirs as $dir) {
            $files[] = $dir . DS . $lang . '.php';
        }
        foreach ($dirs as $dir) {
            $files[] = $dir . DS . 'overrides' . DS . $lang . '.php';
        }

        foreach ($files as $file)
        {
            if (file_exists($file))
            {
                $_LANG = [];
                include $file;
                if (is_array($_LANG))
                {
                    self::getInstance()->langs = array_replace_recursive(self::getInstance()->langs, $_LANG);
                }
                self::getInstance()->currentLang = $lang;
            }
        }
    }

    /**
     * Language folders of both modules: the other module's folder first, this instance's folder last.
     */
    private static function getLangDirs()
    {
        $dir   = self::getInstance()->dir;
        $other = null;

        if (strpos($dir, DS . 'addons' . DS) !== false) {
            $other = str_replace(DS . 'addons' . DS, DS . 'servers' . DS, $dir);
        } elseif (strpos($dir, DS . 'servers' . DS) !== false) {
            $other = str_replace(DS . 'servers' . DS, DS . 'addons' . DS, $dir);
        }

        return ($other && is_dir($other)) ? [$other, $dir] : [$dir];
    }

    /**
     * Set Lang Context
     * 
     * Given parameters are lang levels
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     */
    public static function setContext()
    {
        self::getInstance()->context = array();
        foreach (func_get_args() as $name)
        {
            self::getInstance()->context[] = $name;
        }
    }

    /**
     * Add levels at stack upwards 
     * 
     * Given parameters are lang levels
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     */
    public static function addToContext()
    {
        foreach (func_get_args() as $name)
        {
            self::getInstance()->context[] = $name;
        }
    }

    /**
     * Stag Current levels stack
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param string $stagName
     */
    public static function stagCurrentContext($stagName)
    {
        self::getInstance()->staggedContext[$stagName] = self::getInstance()->context;
    }

    /**
     * Restore Lang levels from stag
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param string $stagName
     */
    public static function unstagContext($stagName)
    {
        if (isset(self::getInstance()->staggedContext[$stagName]))
        {
            self::getInstance()->context = self::getInstance()->staggedContext[$stagName];
            unset(self::getInstance()->staggedContext[$stagName]);
        }
    }

    /**
     * Get Translated Lang
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @return string
     */
    public static function T()
    {
        $lang = self::getInstance()->langs;
        
        $history = array();
        

        foreach (self::getInstance()->context as $name)
        {
            if (isset($lang[$name]))
            {
                $lang = $lang[$name];
            }

            $history[] = $name;
        }
        $returnLangArray = false;

        foreach (func_get_args() as $find)
        {
            
            $history[] = $find;

            if (isset($lang[$find]))
            {
                if (is_array($lang[$find]))
                {
                    $lang = $lang[$find];
                }
                else
                {
                    return htmlentities($lang[$find]);
                }
            }
            else
            {
                if (self::getInstance()->fillLangFile)
                {
                    $returnLangArray = true;
                }
                else
                {
                    return htmlentities($find);
                }
            }
        }

        if ($returnLangArray)
        {

            self::getInstance()->missingLangs['$' . "_LANG['" . implode("']['", $history) . "']"] = ucfirst(end($history));
            return '$' . "_LANG['" . implode("']['", $history) . "']";
        }
        if (is_array($lang) && self::getInstance()->fillLangFile)
        {
            self::getInstance()->missingLangs['$' . "_LANG['" . implode("']['", $history) . "']"] = implode(" ",
                                                                                                            array_slice($history,
                                                                                                                        -3,
                                                                                                                        3,
                                                                                                                        true));
            return '$' . "_LANG['" . implode("']['", $history) . "']";
        }
        
        return htmlentities($find);
    }

    /**
     * Get Translated Absolute Lang 
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @return string
     */
    public static function absoluteT()
    {
        $lang = self::getInstance()->langs;
        
        $returnLangArray = false;

        foreach (func_get_args() as $find)
        {
            $history[] = $find;
            if (isset($lang[$find]))
            {
                if (is_array($lang[$find]))
                {
                    $lang = $lang[$find];
                }
                else
                {
                    return htmlentities($lang[$find]);
                }
            }
            else
            {

                if (self::getInstance()->fillLangFile)
                {
                    $returnLangArray = true;
                }
                else
                {
                    return htmlentities($find);
                }
            }
        }


        if ($returnLangArray)
        {
            self::getInstance()->missingLangs['$' . "_LANG['" . implode("']['", $history) . "']"] = implode(" ",
                                                                                                            array_slice($history,
                                                                                                                        -3,
                                                                                                                        3,
                                                                                                                        true));
            return '$' . "_LANG['" . implode("']['", $history) . "']";
        }

        return htmlentities($lang);
    }

}
