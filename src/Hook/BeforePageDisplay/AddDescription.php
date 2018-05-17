<?php

namespace AdvancedMeta\Hook\BeforePageDisplay;
use AdvancedMeta\Hook\BeforePageDisplay;
use AdvancedMeta\MetaHandler;

class AddDescription extends BeforePageDisplay {

	protected function skipProcessing() {
		$metaHandler = $this->getFactory()->newFromTitle(
			$this->out->getTitle()
		);
		$data = $metaHandler->getData();
		if( $data[MetaHandler::DESCRIPTION] == '' ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$metaHandler = $this->getFactory()->newFromTitle(
			$this->out->getTitle()
		);
		$data = $metaHandler->getData();

		$this->out->addMeta( 'description', $data[MetaHandler::DESCRIPTION] );
		return true;
	}
}
