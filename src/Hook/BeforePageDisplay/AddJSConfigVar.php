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
		if ( !$this->out->getTitle()->userCan( 'advancedmeta-edit' ) ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->out->addJsConfigVars(
			'AdvancedMeta',
			$this->getFactory()->newFromTitle( $this->out->getTitle() )
		);
		return true;
	}
}
