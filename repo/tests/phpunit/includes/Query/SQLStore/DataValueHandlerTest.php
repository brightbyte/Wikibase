<?php

namespace Wikibase\Repo\Test\Query\SQLStore;

use Wikibase\Repo\Query\SQLStore\DataValueHandler;

/**
 * Unit tests for the Wikibase\Repo\Query\SQLStore\DataValueHandler implementing classes.
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
 * @since wd.qe
 *
 * @ingroup WikibaseRepoTest
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class DataValueHandlerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @since wd.qe
	 *
	 * @return DataValueHandler[]
	 */
	protected abstract function getInstances();

	/**
	 * @since wd.qe
	 *
	 * @return DataValueHandler[][]
	 */
	public function instanceProvider() {
		return $this->arrayWrap( $this->getInstances() );
	}

	protected function arrayWrap( array $elements ) {
		return array_map(
			function ( $element ) {
				return array( $element );
			},
			$elements
		);
	}

	/**
	 * @dataProvider instanceProvider
	 *
	 * @param DataValueHandler $dvHandler
	 */
	public function testGetTableDefinitionReturnType( DataValueHandler $dvHandler ) {
		$this->assertInstanceOf( 'Wikibase\Repo\Database\TableDefinition', $dvHandler->getTableDefinition() );
	}

	/**
	 * @dataProvider instanceProvider
	 *
	 * @param DataValueHandler $dvHandler
	 */
	public function testGetValueFieldNameReturnValue( DataValueHandler $dvHandler ) {
		$valueFieldName = $dvHandler->getValueFieldName();

		$this->assertInternalType( 'string', $valueFieldName );

		$this->assertTrue(
			$dvHandler->getTableDefinition()->hasFieldWithName( $valueFieldName ),
			'The value field is present in the table'
		);
	}

	/**
	 * @dataProvider instanceProvider
	 *
	 * @param DataValueHandler $dvHandler
	 */
	public function testGetSortFieldNameReturnValue( DataValueHandler $dvHandler ) {
		$sortFieldName = $dvHandler->getSortFieldName();

		$this->assertInternalType( 'string', $sortFieldName );

		$this->assertTrue(
			$dvHandler->getTableDefinition()->hasFieldWithName( $sortFieldName ),
			'The sort field is present in the table'
		);
	}

	/**
	 * @dataProvider instanceProvider
	 *
	 * @param DataValueHandler $dvHandler
	 */
	public function testNewDataValueFromDbValue( DataValueHandler $dvHandler ) {
		// TODO
	}

	/**
	 * @dataProvider instanceProvider
	 *
	 * @param DataValueHandler $dvHandler
	 */
	public function testGetLabelFieldNameReturnValue( DataValueHandler $dvHandler ) {
		$labelFieldName = $dvHandler->getLabelFieldName();

		$this->assertTrue(
			$labelFieldName === null || is_string( $labelFieldName ),
			'The label field name needs to be either string or null'
		);

		if ( is_string( $labelFieldName ) ) {
			$this->assertTrue(
				$dvHandler->getTableDefinition()->hasFieldWithName( $labelFieldName ),
				'The label field is present in the table'
			);
		}
	}

}