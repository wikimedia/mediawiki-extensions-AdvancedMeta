<?php

namespace AdvancedMeta\Hook\BeforePageDisplay;

use AdvancedMeta\Factory;
use AdvancedMeta\MetaHandler;
use Config;
use ConfigException;
use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\ParserOutputFlags;
use MediaWiki\Title\Title;

class ApplyMetaData implements BeforePageDisplayHook {

	/**
	 * @param Factory $factory
	 * @param Config $config
	 */
	public function __construct(
		private Factory $factory,
		private Config $config
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		if ( $out->getTitle() === null ) {
			return;
		}
		$metaHandler = $this->factory->newFromTitle(
			$out->getTitle()
		);
		$data = $metaHandler->getData();

		if ( !empty( $data[MetaHandler::DESCRIPTION] ) ) {
			$out->addMeta( 'description', $data[MetaHandler::DESCRIPTION] );
		}

		$keywords = $this->getKeywords( $data, $out->getTitle() );
		if ( !empty( $keywords ) ) {
			$out->addMeta( 'keywords', implode( ',', $keywords ) );
		}

		if ( !empty( $data[MetaHandler::ALIAS] ) ) {
			$out->setHTMLTitle( $data[MetaHandler::ALIAS] );
		}

		try {
			$this->addPolicies( $data, $out );
		} catch ( ConfigException $e ) {
			// NOOP
		}
	}

	/**
	 * @param array $metadata
	 * @param Title $title
	 * @return array
	 */
	private function getKeywords( array $metadata, Title $title ): array {
		$globalMetaKeys = $this->factory->newGlobalKeywordsFromTitle( $title );

		return array_merge(
			$globalMetaKeys->getKeys(),
			$metadata[MetaHandler::KEYWORDS] ?? []
		);
	}

	/**
	 * @param array $data
	 * @param OutputPage $out
	 * @return void
	 * @throws ConfigException
	 */
	private function addPolicies( array $data, OutputPage $out ): void {
		$request = $out->getContext()->getRequest();

		$action = $request->getVal( 'action', 'view' );
		$printable = $request->getVal( 'printable', 'no' );

		$current = true;
		$namespace = $out->getTitle()->getNamespace();
		if ( $namespace >= 0 && $request->getIntOrNull( 'oldid' ) !== null ) {
			$current = false;
		}
		$noOldVersions = $this->config->get( 'NoIndexOnOldVersions' );
		if ( $noOldVersions && !$current ) {
			$out->getMetadata()->setIndexPolicy( 'noindex' );
			$out->setFollowPolicy( 'nofollow' );
		} elseif ( $action === 'view' && $printable === 'no' ) {
			if ( $data[MetaHandler::INDEX] ) {
				$out->getMetadata()->setIndexPolicy( 'index' );
				$out->getMetadata()->setOutputFlag( ParserOutputFlags::NO_INDEX_POLICY, false );
			} else {
				$out->getMetadata()->setIndexPolicy( 'noindex' );
			}
			$out->setFollowPolicy(
				$data[MetaHandler::FOLLOW] ? 'follow' : 'nofollow'
			);
		} else {
			// set noindex on edit, printview etc
			$out->getMetadata()->setIndexPolicy( 'noindex' );
		}
	}
}
