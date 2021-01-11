<?php

namespace AdvancedMeta\Hook;

use AdvancedMeta\Hook;

abstract class LoadExtensionSchemaUpdates extends Hook {

	/**
	 * @var \DatabaseUpdater
	 */
	protected $updater;

	/**
	 * @param \DatabaseUpdater $updater
	 * @return bool
	 */
	public static function callback( $updater ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$updater
		);
		return $hookHandler->process();
	}

	/**
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param \DatabaseUpdater $updater
	 */
	public function __construct( $context, $config, $updater ) {
		parent::__construct( $context, $config );

		$this->updater = $updater;
	}
}
