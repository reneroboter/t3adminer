<?php
namespace jigal\t3adminer\Controller;

use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
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
class AdminerController
{
    /** @var array */
    protected $moduleConfiguration;

    /** @var string */
    protected $content;

    /**
     * @var \TYPO3\CMS\Backend\Template\DocumentTemplate
     */
    protected $documentTemplate;

    public function __construct()
    {
        $GLOBALS['LANG']->includeLLFile('EXT:t3adminer/Resources/Private/Language/locallang.xlf');
        $this->moduleConfiguration = $GLOBALS['TBE_MODULES']['_configuration']['tools_txt3adminerM1'];
    }

    /**
     * @return \TYPO3\CMS\Core\Http\HtmlResponse
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     */
    public function main(): HtmlResponse
    {

        // Set the path to adminer
        $extPath = ExtensionManagementUtility::extPath('t3adminer');
        $typo3DocumentRoot = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT');

        // Set class config for module
        $this->moduleConfiguration = $GLOBALS['TBE_MODULES']['_configuration']['tools_txt3adminerM1'];

        // Get config
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('t3adminer');

        // IP-based Access restrictions
        $devIPmask = trim($GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']);
        $remoteAddress = GeneralUtility::getIndpEnv('REMOTE_ADDR');

        // Check for devIpMask restriction
        $useDevIpMask = (bool)$extensionConfiguration['applyDevIpMask'];
        if ($useDevIpMask && $devIPmask !== '*' && !GeneralUtility::cmpIP($remoteAddress, $devIPmask)) {
            return $this->printContent(sprintf($GLOBALS['LANG']->getLL('mlang_notindevipmask'), $remoteAddress));
        }

        // Check for specified IP restrictions
        $allowedIps = trim($extensionConfiguration['IPaccess']);
        if (!empty($allowedIps) && !GeneralUtility::cmpIP($remoteAddress, $allowedIps)) {
            return $this->printContent(sprintf($GLOBALS['LANG']->getLL('mlang_notinipaccess'), $remoteAddress));
        }

        // Check export directory
        $exportDirectory = GeneralUtility::getFileAbsFileName(trim($extensionConfiguration['exportDirectory']));
        if (!is_dir($exportDirectory)) {
            $exportDirectory = GeneralUtility::getFileAbsFileName($GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir']);
        }

        // Path to install dir
        $this->moduleConfiguration['ADM_absolute_path'] = $extPath . $this->moduleConfiguration['ADM_subdir'];

        // Path to web dir
        $relativePathToAdminer = PathUtility::getAbsoluteWebPath($extPath);
        $this->moduleConfiguration['ADM_relative_path'] =
            (StringUtility::beginsWith($relativePathToAdminer, TYPO3_mainDir)
            ? substr($relativePathToAdminer, strlen(TYPO3_mainDir))
            : '../' . $relativePathToAdminer)
        . $this->moduleConfiguration['ADM_subdir'];

        // If t3adminer is configured in the conf.php script, we continue to load it...
        if ($this->moduleConfiguration['ADM_absolute_path'] && @is_dir($this->moduleConfiguration['ADM_absolute_path'])) {
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
            $host = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['host'];
            if (isset($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['driver'])) {
                switch ($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['driver']) {
                    case 'mysqli':
                        $_SESSION['ADM_driver'] = 'server';
                        if (strpos($host, 'p:') === 0) {
                            $host = substr($host, 2);
                        }
                        break;
                    case 'pdo_mysql':
                        $_SESSION['ADM_driver'] = 'server';
                        break;
                    case 'pdo_pgsql':
                        $_SESSION['ADM_driver'] = 'pgsql';
                        break;
                    case 'pdo_sqlite':
                        $_SESSION['ADM_driver'] = 'sqlite';
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
            $_SESSION['pwds'][$_SESSION['ADM_driver']][][$GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user']] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['password'];
            $_SESSION['pwds'][$_SESSION['ADM_driver']][$host . ':' . $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['port']][$GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user']] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['password'];
            $_SESSION['ADM_password'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['password'];
            $_SESSION['ADM_server'] = $host;
            $_SESSION['ADM_port'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['port'];
            $_SESSION['ADM_db'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'];

            // Configure some other parameters
            $_SESSION['ADM_extConf'] = $extensionConfiguration;
            $_SESSION['ADM_hideOtherDBs'] = $extensionConfiguration['hideOtherDBs'];

            // Store TCA in the session to have extra information later on
            $_SESSION['ADM_tca'] = $GLOBALS['TCA'];

            // Get signon uri for redirect
            $_SESSION['ADM_SignonURL'] = '/'
                . ltrim(
                    substr(
                        $extPath, strlen($typo3DocumentRoot), strlen($extPath)
                    ),
                    '/'
                )
                . $this->moduleConfiguration['ADM_subdir'] . $this->moduleConfiguration['ADM_script'];

            // Try to get the TYPO3 backend uri even if it's installed in a subdirectory
            // Compile logout path and add a slash if the returned string does not start with
            $_SESSION['ADM_LogoutURL'] = '/'
                . ltrim(
                    substr(
                        Environment::getPublicPath() . '/typo3/',
                        strlen($typo3DocumentRoot),
                        strlen(Environment::getPublicPath() . '/typo3/')
                    ),
                    '/'
                )
                . 'logout.php';

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

            // Mapping language keys for Adminer
            $LANG_KEY_MAP = [
                'cz' => 'cs',       // Czech
                'ms' => 'id',       // Malay (Indonesian)
                'my' => 'id',       // Malay (Indonesian)
                'jp' => 'ja',       // Japanese
                'kr' => 'ko',       // Korean
                'pt_BR' => 'pt-br', // Portuguese (Brazil)
                'br' => 'pt-br',    // Portuguese (Brazil)
                'si' => 'sl',       // Slovenian
                'ua' => 'uk',       // Ukrainian
                'vn' => 'vi',       // Vietnamese
                'hk' => 'zh',       // Chinese
                'ch' => 'zh',       // Chinese
            ];
            $LANG_KEY = $LANG_KEY_MAP[$GLOBALS['LANG']->lang] ?? $GLOBALS['LANG']->lang ?? 'en';

            // Redirect to adminer (should use absolute URL here!), setting default database
            $redirectUri = $_SESSION['ADM_SignonURL'] . '?lang=' . $LANG_KEY . '&db='
                . rawurlencode($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname']) . '&'
                . rawurlencode($_SESSION['ADM_driver']) . '='
                . rawurlencode(
                    $host
                    . (
                        $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['port']
                        ? ':' . $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['port']
                        : ''
                    )
                ) . '&username=' . rawurlencode($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user']);
            if ($_SESSION['ADM_driver'] !== 'server') {
                $redirectUri .= '&driver=' . rawurlencode($_SESSION['ADM_driver']);
            }

            // Build and set cache-header header
            $headers = [
                'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'private',
                'Location' => $redirectUri
            ];
            return new HtmlResponse('', 303, $headers);
        }

        // No configuration set
        $content = '<h3>Adminer module was not installed?</h3>';
        if ($this->moduleConfiguration['ADM_subdir'] && !@is_dir($this->moduleConfiguration['ADM_subdir'])) {
            $content .= '<hr /><strong>ERROR: The directory, ' . $this->moduleConfiguration['ADM_subdir'] . ', was NOT found!</strong><hr />';
        }

        return $this->printContent($content);
    }

    /**
     * Prints out the module HTML or returns it in an HtmlResponse object
     *
     * @param string $content Content body as formatted HTML
     * @return \TYPO3\CMS\Core\Http\HtmlResponse
     */
    public function printContent($content): HtmlResponse
    {
        $this->documentTemplate = GeneralUtility::makeInstance(DocumentTemplate::class);
        $this->documentTemplate->backPath = $GLOBALS['BACK_PATH'];
        $this->content = $this->documentTemplate->startPage($GLOBALS['LANG']->getLL('title'));
        $this->content .= $content;
        $this->content .= $this->documentTemplate->endPage();
        return new HtmlResponse($this->content);
    }

}
