<?php

namespace AdvancedMeta\Hook\BeforePageDisplay;

use AdvancedMeta\Hook\BeforePageDisplay;
use AdvancedMeta\MetaHandler;

class AddKeywords extends BeforePageDisplay {

	protected $keywords = null;

	protected function skipProcessing() {
		if ( count( $this->getKeywords() ) < 1 ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->out->addMeta( 'keywords', implode( ',', $this->getKeywords() ) );
		return true;
	}

	protected function getKeywords() {
		if ( $this->keywords ) {
			return $this->keywords;
		}

		$metaHandler = $this->getFactory()->newFromTitle(
			$this->out->getTitle()
		);
		$data = $metaHandler->getData();
		$globalMetaKeys = $this->getFactory()->newGlobalKeywordsFromTitle(
			$this->out->getTitle()
		);

		$this->keywords = array_merge(
			$globalMetaKeys->getKeys(),
			$data[MetaHandler::KEYWORDS]
		);

		return $this->keywords;
	}
}
