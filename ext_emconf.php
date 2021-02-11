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
	'version' => '9.4.1',
	'constraints' => [
		'depends' => [
			'typo3' => '9.5.0-11.9.999',
            'php' => '7.2.0-7.4.999',
        ],
		'conflicts' => [],
		'suggests' => [],
    ],
];