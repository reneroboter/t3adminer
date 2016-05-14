<?php
defined('TYPO3_MODE') or die();

if (TYPO3_MODE == 'BE') {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
		'tools',
		'txt3adminerM1',
		'',
		'',
		array (
			'routeTarget' => jigal\t3adminer\Controller\AdminerController::class . '::main',
			'access' => 'admin',
			'name' => 'tools_txt3adminerM1',
			'labels' => array(
				'tabs_images' => array(
					'tab' => 'EXT:t3adminer/Resources/Public/Icons/module-adminer.svg'
				),
				'll_ref' => 'LLL:EXT:t3adminer/mod1/locallang_mod.xml',
			),
			'ADM_subdir' => 'res/',
			'ADM_script' => 't3adminer.php',
		)
	);
}