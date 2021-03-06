<?php

namespace Wikibase\Test\Api;

use DataValues\StringValue;
use FormatJson;
use UsageException;
use Wikibase\Item;
use Wikibase\ItemContent;
use Wikibase\Lib\Serializers\SerializerFactory;
use Wikibase\PropertyContent;
use Wikibase\PropertyNoValueSnak;
use Wikibase\PropertySomeValueSnak;
use Wikibase\PropertyValueSnak;
use Wikibase\Reference;
use Wikibase\SnakList;

/**
 * @covers Wikibase\Api\SetReference
 *
 * @since 0.3
 *
 * @group API
 * @group Database
 * @group Wikibase
 * @group WikibaseAPI
 * @group WikibaseRepo
 * @group SetReferenceTest
 *
 * @group medium
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Katie Filbert < aude.wiki@gmail.com >
 * @author Daniel Kinzler
 * @author H. Snater < mediawiki@snater.com >
 * @author Adam Shorland
 */
class SetReferenceTest extends WikibaseApiTestCase {

	public function setUp() {
		static $hasProperties = false;
		if ( !$hasProperties ) {
			$this->createProperty( 100 );
			$this->createProperty( 4200 );
			$this->createProperty( 4300 );
			$this->createProperty( 6600 );

			$hasProperties = true;
		}

		parent::setUp();
	}

	/**
	 * @param int $id
	 * @param string $dataTypeId
	 */
	public function createProperty( $id, $dataTypeId = 'string' ) {
		$prop = PropertyContent::newEmpty();
		$prop->getEntity()->setId( $id );
		$prop->getEntity()->setDataTypeId( $dataTypeId );
		$prop->save( 'testing' );
	}

	// TODO: clean this up so more of the input space can easily be tested
	// semi-blocked by cleanup of GUID handling in claims
	// can perhaps tseal from RemoveReferencesTest
	public function testRequests() {
		$item = Item::newEmpty();
		$content = new ItemContent( $item );
		$content->save( '', null, EDIT_NEW );

		$statement = $item->newClaim( new PropertyNoValueSnak( 4200 ) );
		$statement->setGuid( $item->getId()->getPrefixedId() . '$D8505CDA-25E4-4334-AG93-A3290BCD9C0P' );

		$reference = new Reference( new SnakList(
			array( new PropertySomeValueSnak( 100 ) )
		) );

		$statement->getReferences()->addReference( $reference );

		$item->addClaim( $statement );

		$content->save( '' );

		$referenceHash = $reference->getHash();

		$reference = new Reference( new SnakList(
			array( new PropertyNoValueSnak( 4200 ) )
		) );

		$serializedReference = $this->makeValidRequest(
			$statement->getGuid(),
			$referenceHash,
			$reference
		);

		// Since the reference got modified, the hash should no longer match
		$this->makeInvalidRequest(
			$statement->getGuid(),
			$referenceHash,
			$reference
		);

		$referenceHash = $serializedReference['hash'];

		$reference = new Reference( new SnakList(
			array(
				new PropertyNoValueSnak( 4200 ),
				new PropertyNoValueSnak( 4300 ),
			)
		) );

		// Set reference with two snaks:
		$serializedReference = $this->makeValidRequest(
			$statement->getGuid(),
			$referenceHash,
			$reference
		);

		$referenceHash = $serializedReference['hash'];

		// Reorder reference snaks by moving the last property id to the front:
		$firstPropertyId = array_shift( $serializedReference['snaks-order'] );
		array_push( $serializedReference['snaks-order'], $firstPropertyId );

		// Make another request with reordered snaks-order:
		$this->makeValidRequest(
			$statement->getGuid(),
			$referenceHash,
			$serializedReference
		);
	}

	public function testRequestWithInvalidProperty() {
		$item = Item::newEmpty();
		$content = new ItemContent( $item );
		$content->save( '', null, EDIT_NEW );

		// Create a statement to act upon:
		$statement = $item->newClaim( new PropertyNoValueSnak( 4200 ) );
		$statement->setGuid(
			$item->getId()->getPrefixedId() . '$D8505CDA-25E4-4334-AG93-A3290BCD9C0P'
		);

		$item->addClaim( $statement );

		$content->save( '' );

		$reference = new Reference( new SnakList( array( new PropertySomeValueSnak( 9999 ) ) ) );

		$this->makeInvalidRequest( $statement->getGuid(), null, $reference, 'invalid-snak-value' );
	}

	public function testSettingIndex() {
		$item = Item::newEmpty();
		$content = new ItemContent( $item );
		$content->save( '', null, EDIT_NEW );

		// Create a statement to act upon:
		$statement = $item->newClaim( new PropertyNoValueSnak( 4200 ) );
		$statement->setGuid(
			$item->getId()->getPrefixedId() . '$D8505CDA-25E4-4334-AG93-A3290BCD9C0P'
		);

		// Pre-fill statement with three references:
		$references = array(
			new Reference( new SnakList( array( new PropertySomeValueSnak( 4200 ) ) ) ),
			new Reference( new SnakList( array( new PropertySomeValueSnak( 4300 ) ) ) ),
			new Reference( new SnakList( array( new PropertySomeValueSnak( 6600 ) ) ) ),
		);

		foreach( $references as $reference ) {
			$statement->getReferences()->addReference( $reference );
		}

		$item->addClaim( $statement );

		$content->save( '' );

		$this->makeValidRequest(
			$statement->getGuid(),
			$references[2]->getHash(),
			$references[2],
			0
		);

		$this->assertEquals( $statement->getReferences()->indexOf( $references[0] ), 0 );
	}

	/**
	 * @param string|null $statementGuid
	 * @param string $referenceHash
	 * @param Reference|array $reference Reference object or serialized reference
	 * @param int|null $index
	 *
	 * @return array Serialized reference
	 */
	protected function makeValidRequest( $statementGuid, $referenceHash, $reference, $index = null ) {
		$serializedReference = $this->serializeReference( $reference );
		$reference = $this->unserializeReference( $reference );

		$params = $this->generateRequestParams(
			$statementGuid,
			$referenceHash,
			$serializedReference,
			$index
		);

		list( $resultArray, ) = $this->doApiRequestWithToken( $params );

		$this->assertInternalType( 'array', $resultArray, 'top level element is an array' );
		$this->assertArrayHasKey( 'reference', $resultArray, 'top level element has a reference key' );

		$serializedReference = $resultArray['reference'];

		unset( $serializedReference['lastrevid'] );

		$this->assertArrayEquals( $this->serializeReference( $reference ), $serializedReference );

		return $serializedReference;
	}

	protected function makeInvalidRequest(
		$statementGuid,
		$referenceHash,
		Reference $reference,
		$expectedErrorCode = 'no-such-reference'
	) {
		$serializedReference = $this->serializeReference( $reference );

		$params = $this->generateRequestParams( $statementGuid, $referenceHash, $serializedReference );

		try {
			$this->doApiRequestWithToken( $params );
			$this->assertFalse( true, 'Invalid request should raise an exception' );
		}
		catch ( UsageException $e ) {
			$this->assertEquals( $expectedErrorCode, $e->getCodeString(), 'Invalid request raised correct error' );
		}
	}

	/**
	 * Serializes a Reference object (if not serialized already).
	 *
	 * @param Reference|array $reference
	 * @return array
	 */
	protected function serializeReference( $reference ) {
		if( !is_a( $reference, '\Wikibase\Reference' ) ) {
			return $reference;
		} else {
			$serializerFactory = new SerializerFactory();
			$serializer = $serializerFactory->newSerializerForObject( $reference );
			return $serializer->getSerialized( $reference );
		}
	}

	/**
	 * Unserializes a serialized Reference object (if not unserialized already).
	 *
	 * @param array|Reference $reference
	 * @return Reference Reference
	 */
	protected function unserializeReference( $reference ) {
		if( is_a( $reference, '\Wikibase\Reference' ) ) {
			return $reference;
		} else {
			unset( $reference['hash'] );
			$serializerFactory = new SerializerFactory();
			$unserializer = $serializerFactory->newUnserializerForClass( '\Wikibase\Reference' );
			return $unserializer->newFromSerialization( $reference );
		}
	}

	/**
	 * Generates the parameters for a 'wbsetreference' API request.
	 *
	 * @param string $statementGuid
	 * @param string $referenceHash
	 * @param array $serializedReference
	 * @param int|null $index
	 *
	 * @return array
	 */
	protected function generateRequestParams(
		$statementGuid,
		$referenceHash,
		$serializedReference,
		$index = null
	) {
		$params = array(
			'action' => 'wbsetreference',
			'statement' => $statementGuid,
			'snaks' => FormatJson::encode( $serializedReference['snaks'] ),
			'snaks-order' => FormatJson::encode( $serializedReference['snaks-order'] ),
		);

		if( !is_null( $referenceHash ) ) {
			$params['reference'] = $referenceHash;
		}

		if( !is_null( $index ) ) {
			$params['index'] = $index;
		}

		return $params;
	}

	/**
	 * @dataProvider invalidClaimProvider
	 */
	public function testInvalidClaimGuid( $claimGuid, $snakHash, $refHash, $expectedError ) {
		$params = array(
			'action' => 'wbsetreference',
			'statement' => $claimGuid,
			'snaks' => $snakHash,
			'reference' => $refHash,
		);

		try {
			$this->doApiRequestWithToken( $params );
			$this->fail( "Exception with code $expectedError expected" );
		} catch ( UsageException $e ) {
			$this->assertEquals( $expectedError, $e->getCodeString(), 'Error code' );
		}
	}

	public function invalidClaimProvider() {
		$snak = new PropertyValueSnak( 4200, new StringValue( 'abc') );
		$snakHash = $snak->getHash();

		$reference = new PropertyValueSnak( 4200, new StringValue( 'def' ) );
		$refHash = $reference->getHash();

		return array(
			array( 'xyz', $snakHash, $refHash, 'invalid-guid' ),
			array( 'x$y$z', $snakHash, $refHash, 'invalid-guid' )
		);
	}

	/**
	 * Currently tests bad calender model
	 * @todo test more bad serializations...
	 */
	public function testInvalidSerialization() {
		$this->setExpectedException( 'UsageException' );
		$params = array(
			'action' => 'wbsetreference',
			'statement' => 'Foo$Guid',
			'snaks' => '{
   "P813":[
      {
         "snaktype":"value",
         "property":"P813",
         "datavalue":{
            "value":{
               "time":"+00000002013-10-05T00:00:00Z",
               "timezone":0,
               "before":0,
               "after":0,
               "precision":11,
               "calendarmodel":"FOOBAR :D"
            },
            "type":"time"
         }
      }
   ]
}',
		);
		$this->doApiRequestWithToken( $params );
	}

}
