<?php

namespace Wikibase;

use Wikibase\Client\WikibaseClient;

/**
 * Represents an external change
 *
 * @since 0.5
 *
 * @licence GNU GPL v2+
 * @author Katie Filbert < aude.wiki@gmail.com >
 */
class ExternalChange {

	/**
	 * @var EntityId
	 */
	protected $entityId;

	/**
	 * @var RevisionData
	 */
	protected $rev;

	/**
	 * @var string
	 */
	protected $changeType;

	/**
	 * @param EntityId $entityId
	 * @param RevisionData $rev
	 * @param string $changeType
	 */
	public function __construct( EntityId $entityId, RevisionData $rev, $changeType ) {
		$this->entityId = $entityId;
		$this->rev = $rev;
		$this->changeType = $changeType;
	}

	/**
	 * return EntityId
	 */
	public function getEntityId() {
		return $this->entityId;
	}

	/**
	 * @return RevisionData
	 */
	public function getRev() {
		return $this->rev;
	}

	/**
	 * @return string
	 */
	public function getChangeType() {
		return $this->changeType;
	}

}
