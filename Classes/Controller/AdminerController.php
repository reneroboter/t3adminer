<?php
namespace jigal\t3adminer\Controller;

use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
class AdminerController extends \TYPO3\CMS\Backend\Module\BaseScriptClass
{
    public $pageinfo;

    /**
     * @return \jigal\t3adminer\Controller\AdminerController
     */
    public function __construct()
    {
        $GLOBALS['LANG']->includeLLFile('EXT:t3adminer/mod1/locallang.xml');

        // This checks permissions and exits if the users has no permission for entry.
        $this->MCONF = $GLOBALS['TBE_MODULES']['_configuration']['tools_txt3adminerM1'];
        $this->getBackendUser()->modAccess($this->MCONF, 1);
        parent::init();
    }

    /**
     * Adds items to the ->MOD_MENU array. Used for the function menu selector.
     */
    public function menuConfig()
    {
        parent::menuConfig();
    }

    /**
     * Main function of the module. Write the content to $this->content
     */
    public function main()
    {
        // Access check!
        if ($GLOBALS['BE_USER']->user['admin']) {
            $content = '';

            // Set the path to adminer
            $extPath = ExtensionManagementUtility::extPath('t3adminer');
            $typo3DocumentRoot = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT');

            // Set class config for module
            $this->MCONF = $GLOBALS['TBE_MODULES']['_configuration']['tools_txt3adminerM1'];

            // Get config
            $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['t3adminer']);

            // IP-based Access restrictions
            $devIPmask = trim($GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']);
            $remoteAddress = GeneralUtility::getIndpEnv('REMOTE_ADDR');

            // Check for devIpMask restriction
            $useDevIpMask = (boolean)$extensionConfiguration['applyDevIpMask'];
            if ($useDevIpMask === true) {
                // Only use devIPmask if it is specific (not '*')
                if ($devIPmask !== '*') {
                    if (!GeneralUtility::cmpIP($remoteAddress, $devIPmask)) {
                        $this->printContent(sprintf($GLOBALS['LANG']->getLL('mlang_notindevipmask'), $remoteAddress));
                        return;
                    }
                }
            }

            // Check for specified IP restrictions
            $allowedIps = trim($extensionConfiguration['IPaccess']);
            if (!empty($allowedIps)) {
                if (!GeneralUtility::cmpIP($remoteAddress, $allowedIps)) {
                    $this->printContent(sprintf($GLOBALS['LANG']->getLL('mlang_notinipaccess'), $remoteAddress));
                    return;
                }
            }

            // Check export directory
            $exportDirectory = GeneralUtility::getFileAbsFileName(trim($extensionConfiguration['exportDirectory']));
            if (!is_dir($exportDirectory)) {
                $exportDirectory = GeneralUtility::getFileAbsFileName($GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir']);
            }

            // Path to install dir
            $this->MCONF['ADM_absolute_path'] = $extPath . $this->MCONF['ADM_subdir'];

            // Path to web dir
            $relativePathToAdminer = ExtensionManagementUtility::siteRelPath('t3adminer');
            $this->MCONF['ADM_relative_path'] =
                (StringUtility::beginsWith($relativePathToAdminer, TYPO3_mainDir)
                ? substr($relativePathToAdminer, strlen(TYPO3_mainDir))
                : '../' . $relativePathToAdminer)
            . $this->MCONF['ADM_subdir'];

            // If t3adminer is configured in the conf.php script, we continue to load it...
            if ($this->MCONF['ADM_absolute_path'] && @is_dir($this->MCONF['ADM_absolute_path'])) {
                // Need to have cookie visible from parent directory
                session_set_cookie_params(0, '/', '', 0);

                // Create signon session
                $session_name = 'tx_t3adminer';
                session_name($session_name);
                session_start();

                // Pass export directory
                $_SESSION['exportDirectory'] = $exportDirectory;
                // Detect DBMS
                $_SESSION['ADM_driver'] = 'server';
                if (isset($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['driver'])) {
                    switch ($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['driver']) {
                        case 'mysqli':
                            $_SESSION['ADM_driver'] = 'server';
                            break;
                        case 'pdo_mysql':
                            $_SESSION['ADM_driver'] = 'server';
                            break;
                        case 'pdo_pgsql':
                            $_SESSION['ADM_driver'] = 'pgsql';
                            break;
                        case 'mssql':
                            $_SESSION['ADM_driver'] = 'mssql';
                            break;
                        default:
                            $_SESSION['ADM_driver'] = 'server';
                    }
                }

                // Store there credentials in the session
                $_SESSION['ADM_user'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user'];
                $_SESSION['pwds'][$_SESSION['ADM_driver']][$GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['host']][$GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user']] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['password'];
                $_SESSION['ADM_password'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['password'];
                $_SESSION['ADM_server'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['host'];
                $_SESSION['ADM_port'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['port'];
                $_SESSION['ADM_db'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'];

                // Configure some other parameters
                $_SESSION['ADM_extConf'] = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['t3adminer'];
                $_SESSION['ADM_hideOtherDBs'] = $extensionConfiguration['hideOtherDBs'];

                // Get signon uri for redirect
                $path_ext = substr($extPath, strlen($typo3DocumentRoot), strlen($extPath));
                $path_ext = (substr($path_ext, 0, 1) != '/' ? '/' . $path_ext : $path_ext);
                $path_adm = $path_ext . $this->MCONF['ADM_subdir'];
                $_SESSION['ADM_SignonURL'] = $path_adm . $this->MCONF['ADM_script'];

                // Try to get the TYPO3 backend uri even if it's installed in a subdirectory
                // Compile logout path and add a slash if the returned string does not start with
                $path_typo3 = substr(PATH_typo3, strlen($typo3DocumentRoot), strlen(PATH_typo3));
                $path_typo3 = (substr($path_typo3, 0, 1) != '/' ? '/' . $path_typo3 : $path_typo3);
                $_SESSION['ADM_LogoutURL'] = $path_typo3 . 'logout.php';

                // Prepend document root if uploadDir does not start with a slash "/"
                $extensionConfiguration['uploadDir'] = trim($extensionConfiguration['uploadDir']);
                if (strpos($extensionConfiguration['uploadDir'], '/') !== 0) {
                    $_SESSION['ADM_uploadDir'] = $typo3DocumentRoot . '/' . $extensionConfiguration['uploadDir'];
                } else {
                    $_SESSION['ADM_uploadDir'] = $extensionConfiguration['uploadDir'];
                }
                $id = session_id();

                // Force to set the cookie
                setcookie($session_name, $id, 0, '/', '');

                // Close that session
                session_write_close();

                // Mapping language keys for Adminer (both for TYPO3 4.5 and later versions)
                $LANG_KEY_MAP = [
                    'ar' => 'ar',       // Arabic
                    //'' => 'bn',	    // Bengali
                    'bg' => 'bg',       // Bulgarian
                    'bs' => 'bs',       // Bosnian
                    'ca' => 'ca',       // Catalan
                    'cs' => 'cs',       // Czech
                    'cz' => 'cs',       // Czech
                    'de' => 'de',       // German
                    'da' => 'da',       // Danish
                    'el' => 'el',       // Greek
                    'en' => 'en',       // English
                    'es' => 'es',       // Spanish
                    'et' => 'et',       // Estonian
                    'fi' => 'fi',       // Finnish
                    'fa' => 'fa',       // Persian
                    'fr' => 'fr',       // French
                    'gl' => 'gl',       // Galician
                    'hu' => 'hu',       // Hungarian
                    'ms' => 'id',       // Malay (Indonesian)
                    'my' => 'id',       // Malay (Indonesian)
                    'it' => 'it',       // Italian
                    'jp' => 'ja',       // Japanese
                    'ja' => 'ja',       // Japanese
                    'ko' => 'ko',       // Korean
                    'kr' => 'ko',       // Korean
                    'lt' => 'lt',       // Lithuanian
                    'nl' => 'nl',       // Dutch
                    'no' => 'no',       // Norwegian
                    'pl' => 'pl',       // Polish
                    'pt' => 'pt',       // Portuguese
                    'pt_BR' => 'pt-br', // Portuguese (Brazil)
                    'br' => 'pt-br',    // Portuguese (Brazil)
                    'ro' => 'ro',       // Romanian
                    'ru' => 'ru',       // Russian
                    'sk' => 'sk',       // Slovak
                    'si' => 'sl',       // Slovenian
                    'sl' => 'sl',       // Slovenian
                    'sr' => 'sr',       // Serbian
                    //'' => 'ta',	    // Tamil
                    'th' => 'th',       // Thai
                    'tr' => 'tr',       // Turkish
                    'ua' => 'uk',       // Ukrainian
                    'uk' => 'uk',       // Ukrainian
                    'vi' => 'vi',       // Vietnamese
                    'vn' => 'vi',       // Vietnamese
                    'hk' => 'zh',       // Chinese
                    'ch' => 'zh',       // Chinese
                    'zh' => 'zh',       // Chinese
                    //'' => 'zh-tw',	// Taiwanese
                ];
                $LANG_KEY = $LANG_KEY_MAP[$GLOBALS['LANG']->lang];
                if (!$LANG_KEY) {
                    $LANG_KEY = 'en';
                }

                // Redirect to adminer (should use absolute URL here!), setting default database
                $redirect_uri = $_SESSION['ADM_SignonURL'] . '?lang=' . $LANG_KEY . '&db='
                    . rawurlencode($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname']) . '&'
                    . rawurlencode($_SESSION['ADM_driver']) . '='
                    . rawurlencode(
                        $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['host']
                        . (
                            $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['port']
                            ? ':' . $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['port']
                            : ''
                        )
                    ) . '&username=' . rawurlencode($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user']);
                if ($_SESSION['ADM_driver'] !== 'server') {
                    $redirect_uri .= '&driver=' . rawurlencode($_SESSION['ADM_driver']);
                }

                // Build and set cache-header header
                $headers = [
                    'Expires: Mon, 26 Jul 1997 05:00:00 GMT',
                    'Pragma: no-cache',
                    'Cache-Control: private',
                    'Location: ' . $redirect_uri
                ];

                // Send all headers
                foreach ($headers as $header) {
                    header($header);
                }
            } else {
                // No configuration set
                $content = '<h3>Adminer module was not installed?</h3>';
                if ($this->MCONF['ADM_subdir'] && !@is_dir($this->MCONF['ADM_subdir'])) {
                    $content .= '<hr /><strong>ERROR: The directory, ' . $this->MCONF['ADM_subdir'] . ', was NOT found!</strong><hr />';
                }
                $this->printContent($content);
            }
        }
    }

    /**
     * Prints out the module HTML
     *
     * @param string $content Content body as formatted HTML
     */
    public function printContent($content)
    {
        $this->doc = GeneralUtility::makeInstance(DocumentTemplate::class);
        $this->doc->backPath = $GLOBALS['BACK_PATH'];
        $this->content = $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
        $this->content .= $content;
        $this->content .= $this->doc->endPage();
        // directly output content
        echo $this->content;
    }
}
