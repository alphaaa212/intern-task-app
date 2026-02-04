<?php
/**
 * The development database settings. These get merged with the global settings.
 */

return array(
	'default' => array(
		'type'        => 'mysqli',
		'connection'  => array(
			'hostname'   => 'db',
			'database'   => 'alphaaa_database',
			'username'   => 'root',
			'password'   => 'root',
			'persistent' => false,
		),
		'identifier'   => '`',
		'table_prefix' => '',
		'charset'      => 'utf8mb4',
		'collation'    => 'utf8mb4_general_ci',
		'enable_cache' => true,
		'profiling'    => false,
	),
);
