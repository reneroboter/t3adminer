<?php
defined('TYPO3_MODE') or die();

if (TYPO3_MODE === 'BE') {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing'][] =
		\jigal\t3adminer\Hooks\T3AdminerHooks::class . '->logoffHook';
}