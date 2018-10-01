<?php
defined('TYPO3_MODE') or die();

call_user_func(function() {
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );
    $iconRegistry->registerIcon(
        'adminer-module',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:t3adminer/Resources/Public/Icons/module-adminer.svg']
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'tools',
        'txt3adminerM1',
        '',
        null,
        [
            'routeTarget' => jigal\t3adminer\Controller\AdminerController::class . '::main',
            'access' => 'systemMaintainer',
            'name' => 'tools_txt3adminerM1',
            'labels' => 'LLL:EXT:t3adminer/Resources/Private/Language/locallang_mod.xlf',
            'iconIdentifier' => 'adminer-module',
            'ADM_subdir' => 'Resources/Public/Adminer/',
            'ADM_script' => 't3adminer.php',
        ]
    );
});
