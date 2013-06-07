<?php
/**
 * Find untranslated statement
 *
 * @category   Utility
 * @package    FindUntranslated
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
$root = dirname(__FILE__);
define('JAWS_PATH', $root. '/../../jaws/');
define('JAWS_DATA', JAWS_PATH . 'data'. DIRECTORY_SEPARATOR);
require_once JAWS_PATH. 'include/Jaws/Utils.php';
$GLOBALS['untranslated'] = array();
$GLOBALS['unused_statements'] = array();

/*
 *
 */
function FindStatements($path) {
    $path = rtrim($path, '\\/') . '/';
    if (false !== $files = @scandir($path)) {
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            // exclude languages directory
            if ($path. $file == JAWS_PATH. 'languages') {
                continue;
            }

            // scan sub-directory
            if (is_dir($path. $file)) {
                FindStatements($path. $file);
                continue;
            }

            $fileinfo = pathinfo($path. $file);
            // only php files
            if (!isset($fileinfo['extension']) || $fileinfo['extension'] !== 'php') {
                continue;
            }

            // get file content
            $content = @file_get_contents($path. $file);
            if (empty($content)) {
                continue;
            }

            // find _t function by regular expression
            if (!preg_match_all('@_t\((?:\'([[:alnum:]\._-]+)\'|\"([[:alnum:]\._-]+)\")@ism', $content, $statements, PREG_SET_ORDER)) {
                continue;
            }

            foreach ($statements as $key => $statement) {
                $GLOBALS['untranslated'][$statement[1]] = '';
            }

        }
    }
}

/*
 *
 */
function FindTranslatedStatements($path) {
    $path = rtrim($path, '\\/') . '/';
    if (false !== $files = @scandir($path)) {
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            // scan sub-directory
            if (is_dir($path. $file)) {
                FindTranslatedStatements($path. $file);
                continue;
            }

            $fileinfo = pathinfo($path. $file);
            // only php files
            if (!isset($fileinfo['extension']) || $fileinfo['extension'] !== 'php') {
                continue;
            }

            // get file content
            $content = @file_get_contents($path. $file);
            if (empty($content)) {
                continue;
            }

            // find _t function by regular expression
            if (!preg_match_all('@define\((?:\'_EN_([[:alnum:]\._-]+)\'|\"_EN_([[:alnum:]\._-]+)\")@ism', $content, $statements, PREG_SET_ORDER)) {
                continue;
            }

            foreach ($statements as $key => $statement) {
                if (array_key_exists($statement[1], $GLOBALS['untranslated'])) {
                    unset($GLOBALS['untranslated'][$statement[1]]);
                } else {
                    $GLOBALS['unused_statements'][$statement[1]] = '';
                }
            }

        }
    }
}

// scan jaws directory for find translation statements by finding "_t" function
FindStatements(JAWS_PATH);
FindTranslatedStatements(JAWS_PATH.'languages/en');
file_put_contents(JAWS_DATA. 'untranslated.txt', implode("\n", array_keys($GLOBALS['untranslated'])));
file_put_contents(JAWS_DATA. 'unusedstatements.txt', implode("\n", array_keys($GLOBALS['unused_statements'])));
echo 'Finished!';
exit;