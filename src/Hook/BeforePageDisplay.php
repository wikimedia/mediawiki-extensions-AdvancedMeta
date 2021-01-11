<?php

namespace AdvancedMeta\Hook;

use AdvancedMeta\Hook;

abstract class BeforePageDisplay extends Hook {

	/**
	 * @var \OutputPage
	 */
	protected $out;

	/**
	 * @var \Skin
	 */
	protected $skin;

	/**
	 * @param \OutputPage $out
	 * @param \Skin $skin
	 * @return bool
	 */
	public static function callback( $out, $skin ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$out,
			$skin
		);
		return $hookHandler->process();
	}

	/**
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param \OutputPage $out
	 * @param \Skin $skin
	 */
	public function __construct( $context, $config, $out, $skin ) {
		parent::__construct( $context, $config );

		$this->out = $out;
		$this->skin = $skin;
	}
}
