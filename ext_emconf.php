<?php
$EM_CONF[$_EXTKEY] = array(
	'title' => 'Adminer',
	'description' => 'Database administration tool \'Adminer\'',
	'category' => 'module',
	'author' => 'Jigal van Hemert',
	'author_email' => 'jigal.van.hemert@typo3.org',
	'author_company' => '',
	'module' => 'mod1',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '8.0.1',
	'constraints' => array(
		'depends' => array(
			'typo3' => '8.7.0-8.7.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);