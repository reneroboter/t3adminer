<?php
// Make instance:
use jigal\t3adminer\Controller\AdminerController;

/** @var $SOBE jigal\t3adminer\Controller\AdminerController */
$SOBE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(AdminerController::class);
$SOBE->init();

$SOBE->main();
