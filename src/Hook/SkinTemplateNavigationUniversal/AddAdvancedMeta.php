<?php

namespace AdvancedMeta\Hook\SkinTemplateNavigationUniversal;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\Permissions\PermissionManager;

class AddAdvancedMeta implements SkinTemplateNavigation__UniversalHook {

	/**
	 * @param PermissionManager $permissionManager
	 */
	public function __construct(
		private PermissionManager $permissionManager
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		if ( !$sktemplate->getTitle() ) {
			return;
		}
		if ( $sktemplate->getTitle()->getArticleID() < 1 ) {
			return;
		}
		$userCan = $this->permissionManager->quickUserCan(
			'advancedmeta-edit',
			$sktemplate->getUser(),
			$sktemplate->getTitle()
		);
		if ( !$userCan ) {
			return;
		}
		$lnkTextMsg = \Message::newFromKey(
			'advancedmeta-navigation-contentaction-label'
		);
		$links['actions']['advancedmeta'] = [
			'class' => false,
			'text' => $lnkTextMsg->plain(),
			'href' => '#',
		];
	}
}
