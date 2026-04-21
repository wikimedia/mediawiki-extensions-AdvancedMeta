<?php

namespace AdvancedMeta\Hook\LoadExtensionSchemaUpdates;

use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

class AddAdvancedMetaDBSchema implements LoadExtensionSchemaUpdatesHook {

	/**
	 * @inheritDoc
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		$base = dirname( dirname( dirname( __DIR__ ) ) );
		$updater->addExtensionTable(
			'ext_meta',
			"$base/maintenance/db/AdvancedMeta.sql"
		);
	}
}
