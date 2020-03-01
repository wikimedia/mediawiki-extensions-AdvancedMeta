<?php

namespace AdvancedMeta\Hook\BeforePageDisplay;

use AdvancedMeta\Hook\BeforePageDisplay;

class AddResources extends BeforePageDisplay {
	protected function skipProcessing() {
		if ( !$this->out->getTitle() ) {
			return true;
		}
		if ( $this->out->getTitle()->getArticleID() < 1 ) {
			return true;
		}
		return !$this->getServices()->getPermissionManager()->userCan(
			'advancedmeta-edit',
			$this->out->getUser(),
			$this->out->getTitle()
		);
	}

	protected function doProcess() {
		$this->out->addModules( 'ext.advancedmeta' );
		return true;
	}
}
