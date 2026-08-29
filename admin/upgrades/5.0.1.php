<?php
/**
 * @package boards
 */

global $gBitInstaller;

$gBitInstaller->registerPackageUpgrade(
	[
		'package'     => 'boards',
		'version'     => '5.0.1',
		'description' => 'Widen boards_tracking.track_date/notify_date from I4 to I8 — I4 is a 32-bit signed integer, max value 19 January 2038, and notify_date may be user/system-set to a future reminder date. I8 matches the convention used everywhere else in this stack and has no such limit.',
	],
	[
		[ 'QUERY' => [
			'SQL92' => [
				"ALTER TABLE boards_tracking ALTER COLUMN track_date TYPE BIGINT",
				"ALTER TABLE boards_tracking ALTER COLUMN notify_date TYPE BIGINT",
			],
		]],
	]
);
