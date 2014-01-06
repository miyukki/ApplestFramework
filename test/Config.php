<?php

return array(
	'debug'   => false,

	'salt'    => 'tRaV!S_C1',

	'path'    => array(
		'type'       => dirname(__FILE__).'/type',
		'logic'      => dirname(__FILE__).'/logic',
		'template'   => dirname(__FILE__).'/template',
		'public'     => dirname(__FILE__).'/public',
		'vendor'     => dirname(__FILE__).'/vendor',
		'controller' => dirname(__FILE__).'/controller',
		'log'        => dirname(__FILE__).'/log',
	),

	'use'     => array(
		'mysql',
	),

	'mysql'      => array(
		'host'     => 'localhost',
		'user'     => 'travis',
		'password' => '',
		'name'     => 'travis_test',
	),
);
