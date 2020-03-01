<?php

namespace AdvancedMeta\Hook\BeforePageDisplay;

use AdvancedMeta\Hook\BeforePageDisplay;

class AddJSConfigVar extends BeforePageDisplay {
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
		$this->out->addJsConfigVars(
			'AdvancedMeta',
			$this->getFactory()->newFromTitle( $this->out->getTitle() )
		);
		return true;
	}
}
