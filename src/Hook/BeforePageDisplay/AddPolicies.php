<?php

namespace AdvancedMeta\Hook\BeforePageDisplay;

use AdvancedMeta\Hook\BeforePageDisplay;
use AdvancedMeta\MetaHandler;

class AddPolicies extends BeforePageDisplay {

	protected function doProcess() {
		$metaData = $this->getFactory()->newFromTitle( $this->out->getTitle() )->getData();

		$request = $this->out->getContext()->getRequest();

		$action = $request->getVal( 'action', 'view' );
		$printable = $request->getVal( 'printable', 'no' );

		$current = true;
		$namespace = $this->out->getTitle()->getNamespace();
		if ( $namespace >= 0 && $request->getIntOrNull( 'oldid' ) !== null ) {
			$current = false;
		}
		$noOldVersions = $this->getConfig()->get( 'NoIndexOnOldVersions' );
		if ( $noOldVersions && !$current ) {
			$this->out->setIndexPolicy( 'noindex' );
			$this->out->setFollowPolicy( 'nofollow' );
		} elseif ( $action === 'view' && $printable === 'no' ) {
			$this->out->setIndexPolicy(
				$metaData[MetaHandler::INDEX] ? 'index' : 'noindex'
			);
			$this->out->setFollowPolicy(
				$metaData[MetaHandler::FOLLOW] ? 'follow' : 'nofollow'
			);
		} else {
			// set noindex on edit, printview etc
			$this->out->setIndexPolicy( 'noindex' );
		}
		return true;
	}
}
