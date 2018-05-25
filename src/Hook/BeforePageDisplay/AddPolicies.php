<?php

namespace AdvancedMeta\Hook\BeforePageDisplay;

use AdvancedMeta\Hook\BeforePageDisplay;
use AdvancedMeta\MetaHandler;

class AddPolicies extends BeforePageDisplay {

	protected function doProcess() {
		$metaHandler = $this->getFactory()->newFromTitle(
			$this->out->getTitle()
		);
		$data = $metaHandler->getData();

		$this->out->setIndexPolicy(
			$data[ MetaHandler::INDEX ] ? 'index' : 'noindex'
		);
		$this->out->setFollowPolicy(
			$data[ MetaHandler::FOLLOW ] ? 'follow' : 'nofollow'
		);

		return true;
	}
}
