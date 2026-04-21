<?php

namespace AdvancedMeta\Hook\BeforePageDisplay;

use AdvancedMeta\Factory;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\Permissions\PermissionManager;

class AddResources implements BeforePageDisplayHook {

	/**
	 * @param PermissionManager $permissionManager
	 * @param Factory $factory
	 */
	public function __construct(
		private PermissionManager $permissionManager,
		private Factory $factory
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		if ( !$out->getTitle() ) {
			return;
		}
		if ( $out->getTitle()->getArticleID() < 1 ) {
			return;
		}
		$userCan = $this->permissionManager->quickUserCan(
			'advancedmeta-edit',
			$out->getUser(),
			$out->getTitle()
		);
		if ( !$userCan ) {
			return;
		}
		$out->addModules( [ 'ext.advancedmeta' ] );
		$out->addJsConfigVars(
			'AdvancedMeta',
			$this->factory->newFromTitle( $out->getTitle() )
		);
	}
}
