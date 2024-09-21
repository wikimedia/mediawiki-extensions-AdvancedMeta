<?php

use MediaWiki\MediaWikiServices;

return [

	'AdvancedMetaFactory' => static function ( MediaWikiServices $services ) {
		return new \AdvancedMeta\Factory(
			$services->getConfigFactory()->makeConfig( 'adwm' ),
			$services->getDBLoadBalancer()
		);
	},

];
