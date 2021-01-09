<?php

namespace AdvancedMeta;

class GlobalMetaKeys {
	/**
	 *
	 * @var \Title
	 */
	protected $title = null;

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @param \Config $config
	 * @param \Title $title
	 */
	public function __construct( \Config $config, \Title $title ) {
		$this->config = $config;
		$this->title = $title;
	}

	/**
	 *
	 * @return array
	 */
	public function getKeys() {
		return $this->getMessage()->exists()
			? $this->parseMessage( $this->getMessage()->text() )
			: [];
	}

	protected function getMessage() {
		return \Message::newFromKey(
			$this->config->get( 'GlobalKeywordsMsgKey' ),
			$this->title
		);
	}

	protected function parseMessage( $msgText ) {
		if ( $msgText == '' ) {
			return [];
		}
		$keywords = [];
		foreach ( explode( ',', $msgText ) as $keyword ) {
			$keyword = trim( strip_tags( (string)$keyword ) );
			if ( empty( $keyword ) ) {
				continue;
			}
			$title = \Title::newFromText( $keyword );
			if ( !$title ) {
				continue;
			}
			$keywords[] = $title->getText();
		}
		return $keywords;
	}
}
