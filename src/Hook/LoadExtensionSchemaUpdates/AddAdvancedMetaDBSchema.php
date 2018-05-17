<?php

namespace AdvancedMeta\Hook\LoadExtensionSchemaUpdates;

use AdvancedMeta\Hook\LoadExtensionSchemaUpdates;

class AddAdvancedMetaDBSchema extends LoadExtensionSchemaUpdates {
	protected function doProcess() {
		$this->updater->addExtensionTable(
			'ext_meta',
			$this->getExtensionPath() . '/maintenance/db/AdvancedMeta.sql'
		);

		return true;
	}

	protected function getExtensionPath() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}

}
