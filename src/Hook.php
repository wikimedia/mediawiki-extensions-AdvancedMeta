<?php

namespace AdvancedMeta;

use MediaWiki\MediaWikiServices;

abstract class Hook {

	/**
	 *
	 * @var \IContextSource
	 */
	private $context = null;

	/**
	 *
	 * @var \Config
	 */
	private $config = null;


	/**
	 * Normally both parameters are NULL on instantiation. This is because we
	 * perform a lazy loading out of performance reasons. But for the sake of
	 * testablity we keep the DI here
	 * @param \IContextSource $context
	 * @param \Config $config
	 */
	public function __construct( $context, $config ) {
		$this->context = $context;
		$this->config = $config;
	}

	/**
	 *
	 * @return \IContextSource
	 */
	protected function getContext() {
		if( $this->context instanceof \IContextSource === false ) {
			$this->context = \RequestContext::getMain();
		}
		return $this->context;
	}

	/**
	 *
	 * @var string
	 */
	protected static $configName = 'adwm';

	/**
	 *
	 * @return \Config
	 */
	protected function getConfig() {
		if( $this->config instanceof \Config === false ) {
			$this->config = $this->getServices()->getConfigFactory()
				->makeConfig( static::$configName );
		}

		return $this->config;
	}

	/**
	 *
	 * @return MediaWikiServices
	 */
	protected function getServices() {
		return MediaWikiServices::getInstance();
	}

	public function process() {
		if( $this->skipProcessing() ) {
			return true;
		}

		$result = $this->doProcess();
		return $result;
	}

	protected abstract function doProcess();

	/**
	 * Allow subclasses to define a skip condition
	 * @return boolean
	 */
	protected function skipProcessing() {
		return false;
	}

	/**
	 *
	 * @return Factory
	 */
	protected function getFactory() {
		return $this->getServices()->getService( 'AdvancedMetaFactory' );
	}
}
