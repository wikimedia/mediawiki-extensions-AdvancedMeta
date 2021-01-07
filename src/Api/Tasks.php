<?php

namespace AdvancedMeta\Api;

use AdvancedMeta\MetaHandler;

class Tasks extends \ApiBase {
	/**
	 * Returns an array of tasks and their required permissions
	 * array('taskname' => array('read', 'edit'))
	 * @return type
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'save' => [
				'read',
				'advancedmeta-edit'
			],
			'delete' => [
				'read',
				'advancedmeta-edit'
			],
		];
	}

	protected function task_save( $taskData, $params ) {
		$result = $this->makeStandardReturn();

		if ( empty( $taskData->articleId ) ) {
			$taskData->articleId = 0;
		}
		if ( empty( $taskData->{MetaHandler::ALIAS} ) ) {
			$taskData->{MetaHandler::ALIAS} = '';
		}
		if ( empty( $taskData->{MetaHandler::DESCRIPTION} ) ) {
			$taskData->{MetaHandler::DESCRIPTION} = '';
		}
		if ( !isset( $taskData->{MetaHandler::FOLLOW} ) ) {
			$taskData->{MetaHandler::FOLLOW} = false;
		}
		if ( !isset( $taskData->{MetaHandler::INDEX} ) ) {
			$taskData->{MetaHandler::INDEX} = false;
		}
		if ( empty( $taskData->{MetaHandler::KEYWORDS} ) ) {
			$taskData->{MetaHandler::KEYWORDS} = [];
		}

		$metaHandler = $this->getFactory()->newFromTitle(
			\Title::newFromID( $taskData->articleId )
		);
		if ( !$metaHandler ) {
			return $result;
		}
		$status = $metaHandler->save( (array)$taskData, $this->getUser() );

		if ( !$result->success = $status->isOK() ) {
			$result->message = $status->getHTML();
		}

		$result->success = true;
		return $result;
	}

	protected function task_delete( $taskData, $params ) {
		$result = $this->makeStandardReturn();

		if ( empty( $taskData->articleId ) ) {
			$taskData->articleId = 0;
		}

		$metaHandler = $this->getFactory()->newFromTitle(
			\Title::newFromID( $taskData->articleId )
		);
		if ( !$metaHandler ) {
			return $result;
		}

		$status = $metaHandler->delete( $this->getUser() );
		if ( !$result->success = $status->isOK() ) {
			$result->message = $status->getHTML();
		}

		return $result;
	}

	/**
	 * @return \AdvancedMeta\Factory
	 */
	protected function getFactory() {
		if ( $this->getServices() ) {
			return $this->getServices()->getService( 'AdvancedMetaFactory' );
		}
	}

	/**
	 *
	 * @return \MediaWiki\MediaWikiServices|false
	 */
	protected function getServices() {
		if ( !class_exists( "\\MediaWiki\\MediaWikiServices" ) ) {
			return false;
		}
		return \MediaWiki\MediaWikiServices::getInstance();
	}

	public function execute() {
		$params = $this->extractRequestParams();

		$task = $params['task'];

		$method = "task_$task";
		$result = $this->makeStandardReturn();

		if ( !is_callable( [ $this, $method ] ) ) {
			$result->errors['task'] = "Task '$task' not implemented!";
		} else {
			$res = $this->checkTaskPermission( $task );
			if ( !$res ) {
				if ( is_callable( [ $this, 'dieWithError' ] ) ) {
					$this->dieWithError(
						'apierror-permissiondenied-generic',
						'permissiondenied'
					);
				} else {
					$this->dieUsageMsg( 'badaccess-groups' );
				}
			}
			if ( wfReadOnly() ) {
				global $wgReadOnly;
				$result->message = $wgReadOnly;
			} else {
				$taskData = $this->getParameter( 'taskdata' );
				if ( empty( $result->errors ) && empty( $result->message ) ) {
					try {
						$result = $this->$method( $taskData, $params );
					} catch ( Exception $e ) {
						$result->success = false;
						$result->message = $e->getMessage();
						$mCode = method_exists( $e, 'getCodeString' )
							? $e->getCodeString()
							: $e->getCode();
						if ( $e instanceof DBError ) {
							// TODO: error code for subtypes like DBQueryError or
							// DBReadOnlyError?
							$mCode = 'dberror';
						}
						$result->errors[$mCode] = $e->getMessage();
						$result->errors[0]['code'] = 'unknown error';
					}
				}
			}
		}

		foreach ( $result as $sFieldName => $mFieldValue ) {
			if ( $mFieldValue === null ) {
				continue; // MW Api doesn't like NULL values
			}

			// Remove empty 'errors' array from respons as mw.Api in MW 1.30+
			// will interpret this field as indicator for a failed request
			if ( $sFieldName === 'errors' && empty( $mFieldValue ) ) {
				continue;
			}
			$this->getResult()->addValue( null, $sFieldName, $mFieldValue );
		}
	}

	protected function getParameterFromSettings( $paramName, $paramSettings, $parseLimit ) {
		$value = parent::getParameterFromSettings(
			$paramName,
			$paramSettings,
			$parseLimit
		);
		// Unfortunately there is no way to register custom types for parameters
		if ( $paramName == 'taskdata' ) {
			$value = \FormatJson::decode( $value );
			if ( empty( $value ) ) {
				return new \stdClass();
			}
		}
		return $value;
	}

	protected function makeStandardReturn() {
		return (object)[
			'errors' => [],
			'success' => false,
			'message' => '',
			'payload' => [],
			'payload_count' => 0
		];
	}

	/**
	 *
	 * @param string $task
	 * @return bool null if requested task not in list
	 * true if allowed
	 * false if not found in permission table of current user
	 */
	public function checkTaskPermission( $task ) {
		$taskPermissions = $this->getRequiredTaskPermissions();

		if ( empty( $taskPermissions[$task] ) ) {
			return;
		}
		// lookup permission for given task
		foreach ( $taskPermissions[$task] as $sPermission ) {
			// check if user have needed permission
			$isAllowed = $this->getServices()->getPermissionManager()->userHasRight(
				$this->getUser(),
				$sPermission
			);
			if ( $isAllowed ) {
				continue;
			}
			// TODO: Reflect permission in error message
			return false;
		}

		return true;
	}

	/**
	 * Returns an array of allowed parameters
	 * @return array
	 */
	protected function getAllowedParams() {
		return [
			'task' => [
				\ApiBase::PARAM_REQUIRED => true,
				\ApiBase::PARAM_TYPE => 'string',
			],
			'taskdata' => [
				\ApiBase::PARAM_TYPE => 'string',
				\ApiBase::PARAM_REQUIRED => false,
				\ApiBase::PARAM_DFLT => '{}',
			],
			'format' => [
				\ApiBase::PARAM_DFLT => 'json',
				\ApiBase::PARAM_TYPE => [ 'json', 'jsonfm' ],
			]
		];
	}

	public function needsToken() {
		return 'csrf';
	}
}
