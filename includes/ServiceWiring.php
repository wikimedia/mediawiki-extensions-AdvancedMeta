<?php

use MediaWiki\MediaWikiServices;

return [
	'AdvancedMetaFactory' => static function ( MediaWikiServices $services ) {
		return new \AdvancedMeta\Factory(
			$services->getService( 'AdvancedMeta._Config' ),
			$services->getDBLoadBalancer()
		);
	},
	'AdvancedMeta._Config' => static function ( MediaWikiServices $services ) {
		return $services->getConfigFactory()->makeConfig( 'adwm' );
	},
];
