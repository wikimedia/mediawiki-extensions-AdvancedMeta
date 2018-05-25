<?php

use MediaWiki\MediaWikiServices;

return [

	'AdvancedMetaFactory' => function ( MediaWikiServices $services ) {
		$lb = null;
		// not available on MW < 1.28
		if ( is_callable( $services, 'getDBLoadBalancer' ) ) {
			$lb = $services->getDBLoadBalancer();
		}
		return new \AdvancedMeta\Factory(
			$services->getConfigFactory()->makeConfig( 'adwm' ),
			$lb
		);
	},

];
