<?php

namespace AdvancedMeta\Hook\BeforePageDisplay;

use AdvancedMeta\Hook\BeforePageDisplay;
use AdvancedMeta\MetaHandler;

class AddPolicies extends BeforePageDisplay {

	protected function doProcess() {
		$metaData = $this->getFactory()->newFromTitle( $this->out->getTitle() )->getData();

		$request = $this->out->getContext()->getRequest();

		$current = true;
		$namespace = $this->out->getTitle()->getNamespace();
		if ( $namespace >= 0 && $request->getIntOrNull( 'oldid' ) !== null ) {
			$current = false;
		}
		$noOldVersions = $this->getConfig()->get( 'NoIndexOnOldVersions' );
		if ( $noOldVersions && !$current ) {
			$this->out->setIndexPolicy( 'noindex' );
			$this->out->setFollowPolicy( 'nofollow' );
		} else {
			$this->out->setIndexPolicy(
				$metaData[MetaHandler::INDEX] ? 'index' : 'noindex'
			);
			$this->out->setFollowPolicy(
				$metaData[MetaHandler::FOLLOW] ? 'follow' : 'nofollow'
			);
		}
		return true;
	}
}
