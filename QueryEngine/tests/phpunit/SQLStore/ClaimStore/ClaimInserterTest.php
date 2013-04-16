<?php

namespace Wikibase\Tests\QueryEngine\SQLStore\ClaimStore;

use DataValues\StringValue;
use Wikibase\Claim;
use Wikibase\EntityId;
use Wikibase\PropertyNoValueSnak;
use Wikibase\PropertyValueSnak;
use Wikibase\QueryEngine\SQLStore\ClaimStore\ClaimInserter;
use Wikibase\Reference;
use Wikibase\ReferenceList;
use Wikibase\SnakList;
use Wikibase\Statement;

/**
 * Unit tests for the Wikibase\QueryEngine\SQLStore\ClaimStore\ClaimInserter class.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @since 0.1
 *
 * @ingroup WikibaseQueryEngineTest
 *
 * @group Wikibase
 * @group WikibaseQueryEngine
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ClaimInserterTest extends \PHPUnit_Framework_TestCase {

	public function claimProvider() {
		/**
		 * @var Claim[] $claims
		 */
		$claims = array();

		$claims[] = new Claim(
			new PropertyValueSnak( 42, new StringValue( 'NyanData' ) )
		);

		$claims[] = new Claim(
			new PropertyNoValueSnak( 23 ),
			new SnakList( array(
				new PropertyValueSnak( 1337, new StringValue( 'NyanData' ) ),
				new PropertyNoValueSnak( 9001 )
			) )
		);

		$claims[] = new Statement(
			new PropertyNoValueSnak( 1 ),
			new SnakList( array(
				new PropertyValueSnak( 2, new StringValue( 'NyanData' ) ),
				new PropertyNoValueSnak( 3 )
			) ),
			new ReferenceList( array(
				new Reference( new SnakList( array(
					new PropertyValueSnak( 3, new StringValue( 'NyanData' ) ),
				) ) ),
				new Reference( new SnakList( array(
					new PropertyValueSnak( 4, new StringValue( 'NyanData' ) ),
					new PropertyValueSnak( 5, new StringValue( 'NyanData' ) ),
				) ) )
			) )
		);

		$argLists = array();

		foreach ( $claims as $claim ) {
			$claim->setGuid( 'some-claim-guid' );
			$argLists[] = array( $claim );
		}

		return $argLists;
	}

	/**
	 * @dataProvider claimProvider
	 */
	public function testInsertClaim( Claim $claim ) {
		$claimTable = $this->getMockBuilder( 'Wikibase\QueryEngine\SQLStore\ClaimStore\ClaimsTable' )
			->disableOriginalConstructor()->getMock();

		$claimTable->expects( $this->once() )->method( 'insertClaimRow' );

		$snakInserter = $this->getMockBuilder( 'Wikibase\QueryEngine\SQLStore\SnakStore\SnakInserter' )
			->disableOriginalConstructor()->getMock();

		$idFinder = $this->getMock( 'Wikibase\QueryEngine\SQLStore\EntityIdMap' );
		$idFinder->expects( $this->any() )
			->method( 'getInternalIdForEntity' )
			->will( $this->returnValue( 42 ) );


		$claimInserter = new ClaimInserter( $claimTable, $snakInserter, $idFinder );

		$claimInserter->insertClaim( $claim, new EntityId( 'item', 1 ) );

		$this->assertTrue( true );
	}

}