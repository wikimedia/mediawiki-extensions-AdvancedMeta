<?php

namespace AdvancedMeta\Hook\SkinTemplateNavigation;

use AdvancedMeta\Hook\SkinTemplateNavigation;

class AddAdvancedMeta extends SkinTemplateNavigation {
	protected function skipProcessing() {
		if ( !$this->sktemplate->getTitle() ) {
			return true;
		}
		if ( $this->sktemplate->getTitle()->getArticleID() < 1 ) {
			return true;
		}
		if ( !$this->sktemplate->getTitle()->userCan( 'advancedmeta-edit' ) ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$lnkTextMsg = \Message::newFromKey(
			'advancedmeta-navigation-contentaction-label'
		);
		$this->links['actions']['advancedmeta'] = [
			'class' => false,
			'text' => $lnkTextMsg->plain(),
			'href' => '#',
		];
	}

}
