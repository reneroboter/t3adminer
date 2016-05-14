<?php
// Make instance:
/** @var $SOBE jigal\t3adminer\Controller\AdminerController */
$SOBE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('jigal\\t3adminer\\Controller\\AdminerController');
$SOBE->init();

$SOBE->main();
$SOBE->printContent();