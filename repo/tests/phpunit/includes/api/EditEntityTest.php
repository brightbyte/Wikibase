<?php

namespace Wikibase\Test\Api;

use Wikibase\ItemContent;
use Wikibase\PropertyContent;

/**
 * @covers Wikibase\Api\EditEntity
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Adam Shorland
 * @author Michal Lazowik
 *
 * @group API
 * @group Wikibase
 * @group WikibaseAPI
 * @group EditEntityTest
 * @group BreakingTheSlownessBarrier
 * @group Database
 * @group medium
 */
class EditEntityTest extends WikibaseApiTestCase {

	private static $idMap;
	private static $hasSetup;

	public function setup() {
		parent::setup();

		if( !isset( self::$hasSetup ) ){
			$this->initTestEntities( array( 'Berlin' ) );
			self::$idMap['%Berlin%'] = EntityTestHelper::getId( 'Berlin' );

			$prop = PropertyContent::newEmpty();
			$prop->getEntity()->setDataTypeId( 'string' );
			$this->assertTrue( $prop->save( 'EditEntityTestP56', null, EDIT_NEW )->isOK() );
			self::$idMap['%P56%'] = $prop->getEntity()->getId()->getSerialization();

			$prop = PropertyContent::newEmpty();
			$prop->getEntity()->setDataTypeId( 'string' );
			$this->assertTrue( $prop->save( 'EditEntityTestP72', null, EDIT_NEW )->isOK() );
			self::$idMap['%P72%'] = $prop->getEntity()->getId()->getSerialization();

			$badge = ItemContent::newEmpty();
			$this->assertTrue( $badge->save( 'EditEntityTestQ42', null, EDIT_NEW )->isOK() );
			self::$idMap['%Q42%'] = $badge->getEntity()->getId()->getSerialization();

			$badge = ItemContent::newEmpty();
			$this->assertTrue( $badge->save( 'EditEntityTestQ149', null, EDIT_NEW )->isOK() );
			self::$idMap['%Q149%'] = $badge->getEntity()->getId()->getSerialization();
		}
		self::$hasSetup = true;
	}

	/**
	 * Provide data for a sequence of requests that will work when run in order
	 * @return array
	 */
	public static function provideData() {
		return array(
			'new item' => array( // new item
				'p' => array( 'new' => 'item', 'data' => '{}' ),
				'e' => array( 'type' => 'item' ) ),
			'new property' => array( // new property (also make sure if we pass in a valid type it is accepted)
				'p' => array( 'new' => 'property', 'data' => '{"datatype":"string"}' ),
				'e' => array( 'type' => 'property' ) ),
			'new property (this is our current example in the api doc)' => array( // new property (this is our current example in the api doc)
				'p' => array( 'new' => 'property', 'data' => '{"labels":{"en-gb":{"language":"en-gb","value":"Propertylabel"}},'.
				'"descriptions":{"en-gb":{"language":"en-gb","value":"Propertydescription"}},"datatype":"string"}' ),
				'e' => array( 'type' => 'property' ) ),
			'add a sitelink..' => array( // add a sitelink.. (also makes sure if we pass in a valid id it is accepted)
				'p' => array( 'data' => '{"sitelinks":{"dewiki":{"site":"dewiki","title":"TestPage!","badges":["%Q42%","%Q149%"]}}}' ),
				'e' => array(
					'sitelinks' => array(
						array(
							'site' => 'dewiki',
							'title' => 'TestPage!',
							'badges' => array( '%Q42%', '%Q149%' )
						)
					)
				)
			),
			'add a label..' => array( // add a label..
				'p' => array( 'data' => '{"labels":{"en":{"language":"en","value":"A Label"}}}' ),
				'e' => array(
					'sitelinks' => array(
						array(
							'site' => 'dewiki',
							'title' => 'TestPage!',
							'badges' => array( '%Q42%', '%Q149%' )
						)
					),
					'labels' => array( 'en' => 'A Label' )
				)
			),
			'add a description..' => array( // add a description..
				'p' => array( 'data' => '{"descriptions":{"en":{"language":"en","value":"DESC"}}}' ),
				'e' => array(
					'sitelinks' => array(
						array(
							'site' => 'dewiki',
							'title' => 'TestPage!',
							'badges' => array( '%Q42%', '%Q149%' )
						)
					),
					'labels' => array( 'en' => 'A Label' ),
					'descriptions' => array( 'en' => 'DESC' )
				)
			),
			'remove a sitelink..' => array( // remove a sitelink..
				'p' => array( 'data' => '{"sitelinks":{"dewiki":{"site":"dewiki","title":""}}}' ),
				'e' => array( 'labels' => array( 'en' => 'A Label' ), 'descriptions' => array( 'en' => 'DESC' ) ) ),
			'remove a label..' => array( // remove a label..
				'p' => array( 'data' => '{"labels":{"en":{"language":"en","value":""}}}' ),
				'e' => array( 'descriptions' => array( 'en' => 'DESC' ) ) ),
			'remove a description..' => array( // remove a description..
				'p' => array( 'data' => '{"descriptions":{"en":{"language":"en","value":""}}}' ),
				'e' => array( 'type' => 'item' ) ),
			'clear an item with some new value' => array( // clear an item with some new value
				'p' => array( 'data' => '{"sitelinks":{"dewiki":{"site":"dewiki","title":"page"}}}', 'clear' => '' ),
				'e' => array(
					'type' => 'item',
					'sitelinks' => array(
						array(
							'site' => 'dewiki',
							'title' => 'Page',
							'badges' => array()
						)
					)
				)
			),
			'clear an item with no value' => array( // clear an item with no value
				'p' => array( 'data' => '{}', 'clear' => '' ),
				'e' => array( 'type' => 'item' ) ),
			'add 2 labels' => array( // add 2 labels
				'p' => array( 'data' => '{"labels":{"en":{"language":"en","value":"A Label"},"sv":{"language":"sv","value":"SVLabel"}}}' ),
				'e' => array( 'labels' => array( 'en' => 'A Label', 'sv' => 'SVLabel' ) ) ),
			'override and add 2 descriptions' => array( // override and add 2 descriptions
				'p' => array( 'clear' => '', 'data' => '{"descriptions":{"en":{"language":"en","value":"DESC1"},"de":{"language":"de","value":"DESC2"}}}' ),
				'e' => array( 'descriptions' => array( 'en' => 'DESC1', 'de' => 'DESC2' ) ) ),
			'override and add a 2 sitelinks..' => array( // override and add a 2 sitelinks..
				'p' => array( 'data' => '{"sitelinks":{"dewiki":{"site":"dewiki","title":"BAA"},"svwiki":{"site":"svwiki","title":"FOO"}}}' ),
				'e' => array(
					'type' => 'item',
					'sitelinks' => array(
						array(
							'site' => 'dewiki',
							'title' => 'BAA',
							'badges' => array()
						),
						array(
							'site' => 'svwiki',
							'title' => 'FOO',
							'badges' => array()
						)
					)
				)
			),
			'unset a sitelink using the other sitelink' => array( // unset a sitelink using the other sitelink
				'p' => array( 'site' => 'svwiki', 'title' => 'FOO', 'data' => '{"sitelinks":{"dewiki":{"site":"dewiki","title":""}}}' ),
				'e' => array(
					'type' => 'item',
					'sitelinks' => array(
						array(
							'site' => 'svwiki',
							'title' => 'FOO',
							'badges' => array()
						)
					)
				)
			),
			'set badges for a existing sitelink, title intact' => array( // set badges for a existing sitelink, title intact
				'p' => array( 'data' => '{"sitelinks":{"svwiki":{"site":"svwiki","badges":["%Q149%","%Q42%"]}}}' ),
				'e' => array(
					'type' => 'item',
					'sitelinks' => array(
						array(
							'site' => 'svwiki',
							'title' => 'FOO',
							'badges' => array( "%Q149%", "%Q42%" )
						)
					)
				)
			),
			'set title for a existing sitelink, badges intact' => array( // set title for a existing sitelink, badges intact
				'p' => array( 'data' => '{"sitelinks":{"svwiki":{"site":"svwiki","title":"FOO2"}}}' ),
				'e' => array(
					'type' => 'item',
					'sitelinks' => array(
						array(
							'site' => 'svwiki',
							'title' => 'FOO2',
							'badges' => array( "%Q149%", "%Q42%" )
						)
					)
				)
			),
			'delete sitelink by providing neither title nor badges' => array( // delete sitelink by providing neither title nor badges
				'p' => array( 'data' => '{"sitelinks":{"svwiki":{"site":"svwiki"}}}' ),
				'e' => array(
					'type' => 'item',
				)
			),
			'add a claim' => array( // add a claim
				'p' => array( 'data' => '{"claims":[{"mainsnak":{"snaktype":"value","property":"%P56%","datavalue":{"value":"imastring","type":"string"}},"type":"statement","rank":"normal"}]}' ),
				'e' => array( 'claims' => array(
					'%P56%' => array(
						'mainsnak' => array( 'snaktype' => 'value', 'property' => '%P56%',
							'datavalue' => array(
								'value' => 'imastring',
								'type' => 'string' ) ),
						'type' => 'statement',
						'rank' => 'normal' ) ) ) ),

			'change the claim' => array( // change the claim
				'p' => array( 'data' => array (
					'claims' => array (
							array (
								'id' => '%lastClaimId%',
								'mainsnak' => array (
										'snaktype' => 'value',
										'property' => '%P56%',
										'datavalue' => array ( 'value' => 'diffstring', 'type' => 'string' ),
									),
								'type' => 'statement',
								'rank' => 'normal',
							),
						),
					) ),
				'e' => array( 'claims' => array(
					'%P56%' => array(
						'mainsnak' => array( 'snaktype' => 'value', 'property' => '%P56%',
							'datavalue' => array(
								'value' => 'diffstring',
								'type' => 'string' ) ),
						'type' => 'statement',
						'rank' => 'normal' ) ) ) ),

			'remove the claim' => array( // remove the claim
				'p' => array( 'data' => '{"claims":[{"id":"%lastClaimId%","remove":""}]}' ),
				'e' => array( 'claims' => array() ) ),

			'add multiple claims' => array( // add multiple claims
				'p' => array(
					'data' => '{"claims":[{"mainsnak":{"snaktype":"value","property":"%P56%","datavalue":{"value":"imastring1","type":"string"}},"type":"statement","rank":"normal"},'.
					'{"mainsnak":{"snaktype":"value","property":"%P56%","datavalue":{"value":"imastring2","type":"string"}},"type":"statement","rank":"normal"}]}' ),
				'e' => array( 'claims' => array(
					array(
						'mainsnak' => array(
							'snaktype' => 'value', 'property' => '%P56%',
							'datavalue' => array(
								'value' => 'imastring1',
								'type' => 'string' ) ),
						'type' => 'statement',
						'rank' => 'normal' ),
					array(
						'mainsnak' => array(
							'snaktype' => 'value', 'property' => '%P56%',
							'datavalue' => array(
								'value' => 'imastring2',
								'type' => 'string' ) ),
						'type' => 'statement',
						'rank' => 'normal' )
				) )
			),

			'clear and add complex claim with qualifiers and references' => array( // clear and add complex claim with qualifiers and references
				'p' => array( 'clear' => '', 'data' => '{"claims": [{"mainsnak": {"snaktype": "value", "property": "%P56%", "datavalue": { "value": "str", "type": "string" } },'.
					'"qualifiers": { "%P56%": [ { "snaktype": "value", "property": "%P56%", "datavalue": { "value": "qual", "type": "string" } } ] }, "type": "statement", "rank": "normal",'.
					'"references": [ { "snaks": { "%P56%": [ { "snaktype": "value", "property": "%P56%", "datavalue": { "value": "src", "type": "string" } } ] } } ]}]}' ),
				'e' => array( 'claims' => array(
					'%P56%' => array(
						'mainsnak' => array(
							'snaktype' => 'value', 'property' => '%P56%',
							'datavalue' => array(
								'value' => 'str',
								'type' => 'string' ) ),
						'qualifiers' => array(
							'%P56%' => array(
								'snaktype' => 'value', 'property' => '%P56%',
								'datavalue' => array(
									'value' => 'qual',
									'type' => 'string' ) ),
						),
						'type' => 'statement',
						'rank' => 'normal',
						'references' => array(
							'snaks' => array(
								'%P56%' => array(
									'snaktype' => 'value', 'property' => '%P56%',
									'datavalue' => array(
										'value' => 'src',
										'type' => 'string' ) ),
							),
							'snaks-order' => array( '%P56%' ),
						),
					)
				) )
			),

			'clear and add multiple claims within property groups' => array( // clear and add multiple claims within property groups
				'p' => array( 'clear' => '',
					'data' => '{"claims":{"%P56%":[{"mainsnak":{"snaktype":"value","property":"%P56%","datavalue":{"value":"imastring56","type":"string"}},"type":"statement","rank":"normal"}],'.
							'"%P72%":[{"mainsnak":{"snaktype":"value","property":"%P72%","datavalue":{"value":"imastring72","type":"string"}},"type":"statement","rank":"normal"}]}}' ),
				'e' => array( 'claims' => array(
					array(
						'mainsnak' => array(
							'snaktype' => 'value', 'property' => '%P56%',
							'datavalue' => array(
								'value' => 'imastring56',
								'type' => 'string' ) ),
						'type' => 'statement',
						'rank' => 'normal' ),
					array(
						'mainsnak' => array(
							'snaktype' => 'value', 'property' => '%P72%',
							'datavalue' => array(
								'value' => 'imastring72',
								'type' => 'string' ) ),
						'type' => 'statement',
						'rank' => 'normal' )
				) )
			),
		);
	}

	/**
	 * Applies self::$idMap to all data in the given data structure, recursively.
	 *
	 * @param $data
	 */
	protected function injectIds( &$data ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $key => &$value ) {
				$this->injectIds( $value );

				$newKey = $key;
				$this->injectIds( $newKey );

				if ( $newKey !== $key ) {
					$data[$newKey] = $value;
					unset( $data[$key] );
				}
			}
		} elseif ( is_string( $data ) ) {
			$data = str_replace( array_keys( self::$idMap ), array_values( self::$idMap ), $data );
		}
	}

	/**
	 * @dataProvider provideData
	 */
	public function testEditEntity( $params, $expected ) {
		$this->injectIds( $params );
		$this->injectIds( $expected );

		$p56 = '%P56%';
		$this->injectIds( $p56 );

		if ( isset( $params['data'] ) && is_array( $params['data'] ) ) {
			$params['data'] = json_encode( $params['data'] );
		}

		// -- set any defaults ------------------------------------
		$params['action'] = 'wbeditentity';
		if( !array_key_exists( 'id', $params )
			&& !array_key_exists( 'new', $params )
			&& !array_key_exists( 'site', $params )
			&& !array_key_exists( 'title', $params) ){
			$params['id'] = self::$idMap['!lastEntityId!'];
		}

		// -- do the request --------------------------------------------------
		list($result,,) = $this->doApiRequestWithToken( $params );

		// -- steal ids for later tests -------------------------------------
		if( array_key_exists( 'new', $params ) && stristr( $params['new'], 'item' ) ){
			self::$idMap['!lastEntityId!'] = $result['entity']['id'];
		}
		if( array_key_exists( 'claims', $result['entity'] ) && array_key_exists( $p56, $result['entity']['claims'] ) ){
			foreach( $result['entity']['claims'][$p56] as $claim ){
				if( array_key_exists( 'id', $claim ) ){
					self::$idMap['%lastClaimId%'] = $claim['id'];
				}
			}
		}

		// -- check the result ------------------------------------------------
		$this->assertArrayHasKey( 'success', $result, "Missing 'success' marker in response." );
		$this->assertResultHasEntityType( $result );
		$this->assertArrayHasKey( 'entity', $result, "Missing 'entity' section in response." );
		$this->assertArrayHasKey( 'id', $result['entity'], "Missing 'id' section in entity in response." );
		$this->assertEntityEquals( $expected, $result['entity'] );

		// -- check the item in the database -------------------------------
		$dbEntity = $this->loadEntity( $result['entity']['id'] );
		$this->assertEntityEquals( $expected, $dbEntity, false );

		// -- check the edit summary --------------------------------------------
		if( !array_key_exists( 'warning', $expected ) || $expected['warning'] != 'edit-no-change' ){
			$this->assertRevisionSummary( array( 'wbeditentity' ), $result['entity']['lastrevid'] );
			if( array_key_exists( 'summary', $params) ){
				$this->assertRevisionSummary( "/{$params['summary']}/" , $result['entity']['lastrevid'] );
			}
		}
	}

	/**
	 * Provide data for requests that will fail with a set exception, code and message
	 * @return array
	 */
	public static function provideExceptionData() {
		return array(
			'no entity id given' => array( // no entity id given
				'p' => array( 'id' => '', 'data' => '{}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'no-such-entity-id' ) ) ),
			'invalid id' => array( // invalid id
				'p' => array( 'id' => 'abcde', 'data' => '{}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'no-such-entity-id' ) ) ),
			'invalid explicit id' => array( // invalid explicit id
				'p' => array( 'id' => '1234', 'data' => '{}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'no-such-entity-id' ) ) ),
			'non existent sitelink' => array( // non existent sitelink
				'p' => array( 'site' => 'dewiki','title' => 'NonExistent', 'data' => '{}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'no-such-entity-link' ) ) ),
			'missing site (also bad title)' => array( // missing site (also bad title)
				'p' => array( 'title' => 'abcde', 'data' => '{}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'param-missing' ) ) ),
			'cant have id and new' => array( // cant have id and new
				'p' => array( 'id' => 'q666', 'new' => 'item' ),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'param-missing' ) ) ),
			'when clearing must also have data!' => array( // when clearing must also have data!
				'p' => array( 'site' => 'enwiki', 'new' => 'Berlin', 'clear' => '' ),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'param-illegal' ) ) ),
			'bad site' => array( // bad site
				'p' => array( 'site' => 'abcde', 'data' => '{}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'unknown_site' ) ) ),
			'no data provided' => array( // no data provided
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin' ),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'no-data' ) ) ),
			'malformed json' => array( // malformed json
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin' , 'data' => '{{{}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'invalid-json' ) ) ),
			'must be a json object (json_decode s this an an int)' => array( // must be a json object (json_decode s this an an int)
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin' , 'data' => '1234'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'not-recognized-array' ) ) ),
			'must be a json object (json_decode s this an an indexed array)' => array( // must be a json object (json_decode s this an an indexed array)
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin' , 'data' => '[ "xyz" ]'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'not-recognized-string' ) ) ),
			'must be a json object (json_decode s this an a string)' => array( // must be a json object (json_decode s this an a string)
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin' , 'data' => '"string"'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'not-recognized-array' ) ) ),
			'inconsistent site in json' => array( // inconsistent site in json
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin' , 'data' => '{"sitelinks":{"ptwiki":{"site":"svwiki","title":"TestPage!"}}}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'inconsistent-site' ) ) ),
			'inconsistent lang in json' => array( // inconsistent lang in json
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin' , 'data' => '{"labels":{"de":{"language":"pt","value":"TestPage!"}}}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'inconsistent-language' ) ) ),
			'inconsistent unknown site in json' => array( // inconsistent unknown site in json
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin' , 'data' => '{"sitelinks":{"BLUB":{"site":"BLUB","title":"TestPage!"}}}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'not-recognized-site' ) ) ),
			'inconsistent unknown languages' => array( // inconsistent unknown languages
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin' , 'data' => '{"lables":{"BLUB":{"language":"BLUB","value":"ImaLabel"}}}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'not-recognized' ) ) ),
			//@todo the error codes in the overly long string tests make no sense and should be corrected...
			'overly long label' => array( // overly long label
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin' ,
					'data' => '{"lables":{"en":{"language":"en","value":"'.TermTestHelper::makeOverlyLongString().'"}}}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException' ) ) ),
			'overly long description' => array( // overly long description
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin' ,
					'data' => '{"descriptions":{"en":{"language":"en","value":"'.TermTestHelper::makeOverlyLongString().'"}}}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException' ) ) ),
			//@todo add check for Bug:52731 once fixed
			'removing invalid claim fails' => array( // removing invalid claim fails
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin' , 'data' => '{"claims":[{"remove":""}]}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'invalid-claim', 'message' => 'Cannot remove a claim with no GUID'  ) ) ),
			'removing valid claim with no guid fails' => array( // removing valid claim with no guid fails
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin' , 'data' => '{"remove":"","claims":[{"mainsnak":{"snaktype":"value","property":"%P56%","datavalue":{"value":"imastring","type":"string"}},"type":"statement","rank":"normal"}]}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'not-recognized', 'message' => 'Unknown key in json: remove' ) ) ),
			'bad badge id' => array( // bad badge id
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin', 'data' => '{"sitelinks":{"dewiki":{"site":"dewiki","title":"TestPage!","badges":["abc","%Q149%"]}}}' ),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'no-such-entity-id' ) ) ),
			'badge id is not an item id' => array( // badge id is not an item id
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin', 'data' => '{"sitelinks":{"dewiki":{"site":"dewiki","title":"TestPage!","badges":["P2","%Q149%"]}}}' ),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'not-item' ) ) ),
			'badge item does not exist' => array( // badge item does not exist
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin', 'data' => '{"sitelinks":{"dewiki":{"site":"dewiki","title":"TestPage!","badges":["Q99999","%Q149%"]}}}' ),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'no-such-entity' ) ) ),
			'no sitelink - cannot change badges' => array( // no sitelink - cannot change badges
				'p' => array( 'site' => 'enwiki', 'title' => 'Berlin', 'data' => '{"sitelinks":{"svwiki":{"site":"svwiki","badges":["%Q42%","%Q149%"]}}}' ),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'no-such-sitelink' ) ) ),
			'bad id in serialization' => array( // no entity id given
				'p' => array( 'id' => '%Berlin%', 'data' => '{"id":"Q13244"}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'param-invalid', 'message' => 'Invalid field used in call: "id", must match id parameter' ) ) ),
			'bad type in serialization' => array( // no entity id given
				'p' => array( 'id' => '%Berlin%', 'data' => '{"id":"%Berlin%","type":"foobar"}'),
				'e' => array( 'exception' => array( 'type' => 'UsageException', 'code' => 'param-invalid', 'message' => 'Invalid field used in call: "type", must match type associated with id' ) ) ),
		);
	}

	/**
	 * @dataProvider provideExceptionData
	 */
	public function testEditEntityExceptions( $params, $expected ){
		$this->injectIds( $params );
		$this->injectIds( $expected );

		// -- set any defaults ------------------------------------
		$params['action'] = 'wbeditentity';
		$this->doTestQueryExceptions( $params, $expected['exception'] );
	}

}
