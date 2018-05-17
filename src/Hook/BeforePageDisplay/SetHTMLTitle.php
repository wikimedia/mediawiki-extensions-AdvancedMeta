<?php

namespace AdvancedMeta\Hook\BeforePageDisplay;
use AdvancedMeta\Hook\BeforePageDisplay;
use AdvancedMeta\MetaHandler;

class SetHTMLTitle extends BeforePageDisplay {

	protected function skipProcessing() {
		$metaHandler = $this->getFactory()->newFromTitle(
			$this->out->getTitle()
		);
		$data = $metaHandler->getData();
		if( $data[MetaHandler::ALIAS] == '' ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$metaHandler = $this->getFactory()->newFromTitle(
			$this->out->getTitle()
		);
		$data = $metaHandler->getData();

		$this->out->setHTMLTitle( $data[MetaHandler::ALIAS] );
		return true;
	}
}
