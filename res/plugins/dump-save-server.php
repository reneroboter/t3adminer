<?php

/** Dump to file
 *
 * @author Jakub KluvÃ¡nek, kluvanek@gmail.com
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 *
 *    USAGE:
 *    For example when you want to do automated dumps of some databases with selenium. Selenium can't control the download dialog.
 *    Just set the desired path where you want to save dump in constructor of this plugin.
 *
 *    BUGS:
 *    #1 - other than sql formats shows the download dialog - FIXED
 */
class AdminerDumpSaveServer
{
    /** @access protected */
    var $dir;
    var $fileName;

    /**
     * AdminerDumpSaveServer constructor.
     *
     * @param null $dir
     */
    function __construct($dir = null)
    {
        $this->dir = $dir;
    }

    /**
     * Option for this dump type
     *
     * @return array
     */
    function dumpOutput()
    {
        return array('server' => 'Server');
    }

    static public function _redirect($location, $message = null)
    {
        if ($message !== null) {
            restart_session();
            $_SESSION['messages'][preg_replace('~^[^?]*~', '', ($location !== null ? $location : $_SERVER['REQUEST_URI']))][] =
                $message;
        }
        if ($location !== null) {
            if ($location === '') {
                $location = '.';
            }
            header('Location: ' . $location);
        }
    }

    function _save($string, $state)
    {
        if ($_POST['output'] == 'server') {
            if (function_exists('header_remove')) {
                header_remove('Content-Disposition');
                header_remove('Content-Type');
            } else {
                header('Content-Disposition:');
                header('Content-Type:');
            }
            $file = $this->dir . $this->fileName;
            file_put_contents($file, $string);
            self::_redirect(remove_from_uri(), lang('Webserver file %s', htmlspecialchars($file)));

            return '';
        }
    }

    function dumpHeaders($identifier, $multi_table = false)
    {
        if ($_POST['output'] == 'server') {
            $this->fileName = $identifier . '.' . ($multi_table && preg_match('/[ct]sv/', $_POST["format"]) ? 'tar' : $_POST['format']);
            ob_start(array($this, '_save'));
        }
    }
}