<?php

namespace Wikibase\Test;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\EntityContentFactory;
use Wikibase\EntityId;
use Wikibase\Item;
use Wikibase\Property;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers Wikibase\EntityContentFactory
 *
 * @since 0.2
 *
 * @group Wikibase
 * @group WikibaseEntity
 * @group WikibaseContent
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EntityContentFactoryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider contentModelsProvider
	 */
	public function testGetEntityContentModels( array $contentModelIds ) {
		$factory = new EntityContentFactory(
			$this->newMockIdFormatter(),
			$contentModelIds
		);

		$this->assertEquals( $contentModelIds, $factory->getEntityContentModels() );
	}

	protected function newMockIdFormatter() {
		$idFormatter = $this->getMockBuilder( 'Wikibase\Lib\EntityIdFormatter' )
			->disableOriginalConstructor()->getMock();

		$idFormatter->expects( $this->any() )
			->method( 'format' )
			->will( $this->returnValue( 'Nyan' ) );

		return $idFormatter;
	}

	public function contentModelsProvider() {
		$argLists = array();

		$argLists[] = array( array() );
		$argLists[] = array( array( 0 ) );
		$argLists[] = array( array( 42, 1337, 9001 ) );
		$argLists[] = array( array( 0, 1, 2, 3, 4, 5, 6, 7 ) );

		return $argLists;
	}

	public function testIsEntityContentModel() {
		$factory = $this->newFactory();

		foreach ( $factory->getEntityContentModels() as $type ) {
			$this->assertTrue( $factory->isEntityContentModel( $type ) );
		}

		$this->assertFalse( $factory->isEntityContentModel( 'this-does-not-exist' ) );
	}

	protected function newFactory() {
		return new EntityContentFactory(
			$this->newMockIdFormatter(),
			array( 42, 1337, 9001 )
		);
	}

	public function testGetTitleForId() {
		$factory = $this->newFactory();

		$title = $factory->getTitleForId( new ItemId( 'q42' ) );

		$this->assertEquals( 'Nyan', $title->getText() );
	}

	public function testGetWikiPageForId() {
		$entityId = new ItemId( 'q42' );

		$factory = $this->newFactory();

		$expectedTitle = $factory->getTitleForId( $entityId );

		$wikiPage = $factory->getWikiPageForId( $entityId );

		$this->assertEquals( $expectedTitle, $wikiPage->getTitle() );
	}

	public function entityTypesProvider() {
		$argLists = array();

		$argLists[] = array( Item::ENTITY_TYPE );
		$argLists[] = array( Property::ENTITY_TYPE );

		return $argLists;
	}

	public function invalidEntityTypesProvider() {
		$argLists = array();

		$argLists[] = array( 42 );
		$argLists[] = array( 'foo' );

		return $argLists;
	}

	/**
	 * @dataProvider entityTypesProvider
	 */
	public function testNewFromType( $type ) {
		$entityContentFactory = WikibaseRepo::getDefaultInstance()->getEntityContentFactory();
		$entityContent = $entityContentFactory->newFromType( $type );
		$this->assertEquals( $type, $entityContent->getEntity()->getType() );
	}

	/**
	 * @dataProvider invalidEntityTypesProvider
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidNewFromType( $type ) {
		$entityContentFactory = WikibaseRepo::getDefaultInstance()->getEntityContentFactory();
		$entityContent = $entityContentFactory->newFromType( $type );
	}

}
