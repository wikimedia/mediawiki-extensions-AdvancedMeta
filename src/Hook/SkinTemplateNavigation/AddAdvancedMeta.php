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
		return !$this->getServices()->getPermissionManager()->userCan(
			'advancedmeta-edit',
			$this->sktemplate->getUser(),
			$this->sktemplate->getTitle()
		);
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
