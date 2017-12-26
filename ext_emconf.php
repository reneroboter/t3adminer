<?php
$EM_CONF[$_EXTKEY] = [
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
	'version' => '9.0.0',
	'constraints' => [
		'depends' => [
			'typo3' => '8.7.0-9.0.99',
        ],
		'conflicts' => [],
		'suggests' => [],
    ],
];