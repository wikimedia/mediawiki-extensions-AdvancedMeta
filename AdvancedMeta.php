<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is an extension to the MediaWiki software and cannot be used standalone.' );
}

/**
 * MediaWiki Advanced Meta extension
 * Add meta data to individual pages or entire namespaces
 * @version 2.1.0
 * @author Stephan Muller <mail@litso.com> (Main author)
 * @author Bart van Heukelom <b@rtvh.nl> (Objectification)
 * @author Zayoo <zayoo@126.com> (Revise & TitleAlias[1])
 *
 * [1] Refer to Extension:Add HTML Meta and Title & Extension:TitleAlias)
 */

$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'Advanced Meta',
	'author' => array( '[http://www.stephanmuller.nl Stephan Muller]', 'Bart van Heukelom, Zayoo' ),
	'descriptionmsg' => 'ameta-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:Advanced_Meta',
	'version' => '2.1.0'
);

$wgMessagesDirs['MWAdvancedMeta'] = __DIR__ . '/i18n';

$wgAutoloadClasses['MWAdvancedMeta'] = __DIR__ . '/AdvancedMeta.class.php';

MWAdvancedMeta::setup();

$wgHooks['LoadExtensionSchemaUpdates'][] = 'efAdvancedMetaSchemaUpdates';

/**
 * @param $updater DatabaseUpdater
 * @return bool
 */
function efAdvancedMetaSchemaUpdates( $updater ) {
	switch ( $updater->getDB()->getType() ) {
		case 'mysql':
			$updater->addExtensionUpdate( array( 'addTable', 'ext_meta',
				__DIR__ . '/AdvancedMeta.sql', true ) ); // Initially install tables
			break;
		default:
			print "\n" .
				"There are no table structures for the AdvancedMeta\n" .
				"extension for your data base type at the moment.\n\n";
	}

	return true;
}
