<?php

namespace AdvancedMeta;

class MetaHandler implements \JsonSerializable {
	const DESCRIPTION = 'description';
	const FOLLOW = 'follow';
	const INDEX = 'index';
	const ALIAS = 'alias';
	const KEYWORDS = 'keywords';

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
	 * @var \Wikimedia\Rdbms\IDatabase
	 */
	protected $db = null;

	/**
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 *
	 * @var bool
	 */
	protected $exists = false;

	/**
	 *
	 * @param \Config $config
	 * @param \Title $title
	 * @param \Wikimedia\Rdbms\IDatabase $db
	 */
	public function __construct( \Config $config, \Title $title, $db ) {
		$this->config = $config;
		$this->title = $title;
		$this->db = $db;
		$this->load();
	}

	/**
	 *
	 * @return bool
	 */
	public function exists() {
		return $this->exists;
	}

	protected function load() {
		$this->loadDefaults();

		if ( $this->getTitle()->getArticleID() < 1 ) {
			return true;
		}

		$res = $this->db->selectRow(
			'ext_meta',
			[
				static::DESCRIPTION,
				static::INDEX => 'rindex',
				static::FOLLOW => 'rfollow',
				static::KEYWORDS,
				static::ALIAS => 'titlealias',
			],
			[ 'pageid' => $this->getTitle()->getArticleID() ],
			__METHOD__
		);
		if ( !$res ) {
			return true;
		}
		$this->exists = true;
		$this->params[static::ALIAS] = $res->{static::ALIAS};
		$this->params[static::INDEX] = !empty( $res->{static::INDEX} );
		$this->params[static::FOLLOW] = !empty( $res->{static::FOLLOW} );
		$this->params[static::KEYWORDS] = explode(
			',',
			$res->{static::KEYWORDS}
		);
		$this->params[static::DESCRIPTION] = $res->{static::DESCRIPTION};
		return true;
	}

	protected function loadDefaults() {
		$this->params[ static::INDEX ] = false;
		$this->params[ static::FOLLOW ] = false;
		$this->params[ static::KEYWORDS ] = [];
		$this->params[ static::ALIAS ] = '';
		$this->params[ static::DESCRIPTION ] = '';
		$policies = explode( ',', $this->config->get( 'DefaultRobotPolicy' ) );

		if ( in_array( 'index', $policies ) ) {
			$this->params[ static::INDEX ] = true;
		}
		if ( in_array( 'follow', $policies ) ) {
			$this->params[ static::FOLLOW ] = true;
		}
		$nsPolicies = $this->config->get( 'NamespaceRobotPolicies' );
		if ( !empty( $nsPolicies[$this->title->getNamespace()] ) ) {
			$policies = explode(
				',',
				$nsPolicies[$this->title->getNamespace()]
			);
			if ( in_array( 'follow', $policies ) ) {
				$this->params[ static::FOLLOW ] = true;
			} elseif ( in_array( 'nofollow', $policies ) ) {
				$this->params[ static::FOLLOW ] = false;
			}
			if ( in_array( 'index', $policies ) ) {
				$this->params[ static::INDEX ] = true;
			} elseif ( in_array( 'noindex', $policies ) ) {
				$this->params[ static::INDEX ] = false;
			}
		}

		$articlePolicies = $this->config->get( 'ArticleRobotPolicies' );
		if ( !empty( $articlePolicies[$this->title->getFullText()] ) ) {
			$policies = explode(
				',',
				$articlePolicies[$this->title->getFullText()]
			);
			if ( in_array( 'follow', $policies ) ) {
				$this->params[ static::FOLLOW ] = true;
			} elseif ( in_array( 'nofollow', $policies ) ) {
				$this->params[ static::FOLLOW ] = false;
			}
			if ( in_array( 'index', $policies ) ) {
				$this->params[ static::INDEX ] = true;
			} elseif ( in_array( 'noindex', $policies ) ) {
				$this->params[ static::INDEX ] = false;
			}
		}
	}

	/**
	 *
	 * @return \Title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 *
	 * @param array $params
	 * @param \User|null $user
	 * @return \Status
	 */
	public function save( array $params = [], \User $user = null ) {
		// TODO: logging

		$data = [];
		foreach ( $this->getData() as $name => $value ) {
			try {
				$value = $this->prepareSave( $name, $value, $params );
			} catch ( \Exception $e ) {
				return \Status::newFatal( $e->getMessage() );
			}
			if ( $name == static::ALIAS ) {
				$name = 'titlealias';
			}
			if ( $name == static::FOLLOW ) {
				$name = 'rfollow';
			}
			if ( $name == static::INDEX ) {
				$name = 'rindex';
			}
			$data[$name] = $value;
		}

		if ( !$this->exists() ) {
			$res = $this->db->insert(
				'ext_meta',
				$data + [ 'pageid' => $this->getTitle()->getArticleID() ],
				__METHOD__
			);
		} else {
			$res = $this->db->update(
				'ext_meta',
				$data,
				[ 'pageid' => $this->getTitle()->getArticleID() ],
				__METHOD__
			);
		}
		$this->invalidate();
		return \Status::newGood( $this );
	}

	protected function prepareSave( $name, $value, $params ) {
		if ( isset( $params[$name] ) ) {
			$value = $params[$name];
		}
		if ( $name == static::ALIAS || $name == static::DESCRIPTION ) {
			$value = trim( strip_tags( (string)$value ) );
			if ( $name !== static::ALIAS || $value == '' ) {
				return $value;
			}
			$title = \Title::newFromText( $value );
			if ( !$title ) {
				throw new \MWException(
					"invalid value or param $name: " . __METHOD__
				);
			}
			return $value;
		}
		if ( $name === static::INDEX || $name === static::FOLLOW ) {
			return isset( $value ) && $value === true;
		}
		if ( $name === static::KEYWORDS ) {
			if ( !is_array( $value ) ) {
				throw new \MWException(
					"invalid value or param $name: " . __METHOD__
				);
			}
			$keywords = [];
			foreach ( $value as $keyword ) {
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
			return implode( ',', $keywords );
		}

		throw new \MWException(
			"unknown param $name: " . __METHOD__
		);
	}

	/**
	 *
	 * @param \User|null $user
	 * @return \Status
	 */
	public function delete( \User $user = null ) {
		// TODO: logging
		if ( !$this->exists() ) {
			return \Status::newGood( $this );
		}
		$res = $this->db->delete(
			'ext_meta',
			[ 'pageid' => $this->getTitle()->getArticleID() ],
			__METHOD__
		);
		$this->invalidate();
		return \Status::newGood( $this );
	}

	public function jsonSerialize() {
		return $this->getData();
	}

	/**
	 *
	 * @return array
	 */
	public function getData() {
		return [
			static::INDEX => $this->params[ static::INDEX ],
			static::FOLLOW => $this->params[ static::FOLLOW ],
			static::KEYWORDS => $this->params[ static::KEYWORDS ],
			static::ALIAS => $this->params[ static::ALIAS ],
			static::DESCRIPTION => $this->params[ static::DESCRIPTION ],
		];
	}

	public function invalidate() {
		$this->getTitle()->invalidateCache();
		// \DeferredUpdates::doUpdates();
		$this->load();
	}

}
