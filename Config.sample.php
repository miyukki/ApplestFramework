<?php

return array(
	'debug'   => true,

	'salt'    => 'salt here',

	'path'    => array(
		'type'       => dirname(__FILE__).'/type',
		'logic'      => dirname(__FILE__).'/logic',
		'template'   => dirname(__FILE__).'/template',
		'public'     => dirname(__FILE__).'/public',
		'vendor'     => dirname(__FILE__).'/vendor',
		'controller' => dirname(__FILE__).'/controller',
	),
);