<?php

namespace Wikibase\Test\Api;

use OutOfBoundsException;

/**
 * @since 0.4
 *
 * @licence GNU GPL v2+
 * @author John Erling Blad < jeblad@gmail.com >
 * @author Daniel Kinzler
 * @author Adam Shorland
 */
class EntityTestHelper {

	//@todo allow data to be defined dynamically in tests
	//this way this class need not contain data but only a way to
	//manage it for the tests

	/**
	 * @var array of currently active handles and their current ids
	 */
	private static $activeHandles = array();
	/**
	 * @var array of currently active ids and their current handles
	 */
	private static $activeIds;
	/**
	 * @var array handles and any registered default output data
	 */
	private static $entityOutput = array();

	/**
	 * @var array of pre defined entity data for use in tests
	 */
	private static $entityData = array(
		'Empty' => array(
			"new" => "item",
			"data" => array(),
		),
		'Empty2' => array(
			"new" => "item",
			"data" => array(),
		),
		'Berlin' => array(
			"new" => "item",
			"data" => array(
				"sitelinks" => array(
					array( "site" => "dewiki", "title" => "Berlin" ),
					array( "site" => "enwiki", "title" => "Berlin" ),
					array( "site" => "nlwiki", "title" => "Berlin" ),
					array( "site" => "nnwiki", "title" => "Berlin" ),
				),
				"labels" => array(
					array( "language" => "de", "value" => "Berlin" ),
					array( "language" => "en", "value" => "Berlin" ),
					array( "language" => "nb", "value" => "Berlin" ),
					array( "language" => "nn", "value" => "Berlin" ),
				),
				"aliases" => array(
					array( array( "language" => "de", "value" => "Dickes B" ) ),
					array( array( "language" => "en", "value" => "Dickes B" ) ),
					array( array( "language" => "nl", "value" => "Dickes B" ) ),
				),
				"descriptions" => array(
					array( "language" => "de", "value" => "Bundeshauptstadt und Regierungssitz der Bundesrepublik Deutschland." ),
					array( "language" => "en", "value" => "Capital city and a federated state of the Federal Republic of Germany." ),
					array( "language" => "nb", "value" => "Hovedsted og delstat og i Forbundsrepublikken Tyskland." ),
					array( "language" => "nn", "value" => "Hovudstad og delstat i Forbundsrepublikken Tyskland." ),
				),
			)
		),
		'London' => array(
			"new" => "item",
			"data" => array(
				"sitelinks" => array(
					array( "site" => "enwiki", "title" => "London" ),
					array( "site" => "dewiki", "title" => "London" ),
					array( "site" => "nlwiki", "title" => "London" ),
					array( "site" => "nnwiki", "title" => "London" ),
				),
				"labels" => array(
					array( "language" => "de", "value" => "London" ),
					array( "language" => "en", "value" => "London" ),
					array( "language" => "nb", "value" => "London" ),
					array( "language" => "nn", "value" => "London" ),
				),
				"aliases" => array(
					array(
						array( "language" => "de", "value" => "City of London" ),
						array( "language" => "de", "value" => "Greater London" ),
					),
					array(
						array( "language" => "en", "value" => "City of London" ),
						array( "language" => "en", "value" => "Greater London" ),
					),
					array(
						array( "language" => "nl", "value" => "City of London" ),
						array( "language" => "nl", "value" => "Greater London" ),
					),
				),
				"descriptions" => array(
					array( "language" => "de", "value" => "Hauptstadt Englands und des Vereinigten Königreiches." ),
					array( "language" => "en", "value" => "Capital city of England and the United Kingdom." ),
					array( "language" => "nb", "value" => "Hovedsted i England og Storbritannia." ),
					array( "language" => "nn", "value" => "Hovudstad i England og Storbritannia." ),
				),
			)
		),
		'Oslo' => array(
			"new" => "item",
			"data" => array(
				"sitelinks" => array(
					array( "site" => "dewiki", "title" => "Oslo" ),
					array( "site" => "enwiki", "title" => "Oslo" ),
					array( "site" => "nlwiki", "title" => "Oslo" ),
					array( "site" => "nnwiki", "title" => "Oslo" ),
				),
				"labels" => array(
					array( "language" => "de", "value" => "Oslo" ),
					array( "language" => "en", "value" => "Oslo" ),
					array( "language" => "nb", "value" => "Oslo" ),
					array( "language" => "nn", "value" => "Oslo" ),
				),
				"aliases" => array(
					array(
						array( "language" => "nb", "value" => "Christiania" ),
						array( "language" => "nb", "value" => "Kristiania" ),
					),
					array(
						array( "language" => "nn", "value" => "Christiania" ),
						array( "language" => "nn", "value" => "Kristiania" ),
					),
					array( "language" => "de", "value" => "Oslo City" ),
					array( "language" => "en", "value" => "Oslo City" ),
					array( "language" => "nl", "value" => "Oslo City" ),
				),
				"descriptions" => array(
					array( "language" => "de", "value" => "Hauptstadt der Norwegen." ),
					array( "language" => "en", "value" => "Capital city in Norway." ),
					array( "language" => "nb", "value" => "Hovedsted i Norge." ),
					array( "language" => "nn", "value" => "Hovudstad i Noreg." ),
				),
			)
		),
		'Episkopi' => array(
			"new" => "item",
			"data" => array(
				"sitelinks" => array(
					array( "site" => "dewiki", "title" => "Episkopi Cantonment" ),
					array( "site" => "enwiki", "title" => "Episkopi Cantonment" ),
					array( "site" => "nlwiki", "title" => "Episkopi Cantonment" ),
				),
				"labels" => array(
					array( "language" => "de", "value" => "Episkopi Cantonment" ),
					array( "language" => "en", "value" => "Episkopi Cantonment" ),
					array( "language" => "nl", "value" => "Episkopi Cantonment" ),
				),
				"aliases" => array(
					array( "language" => "de", "value" => "Episkopi" ),
					array( "language" => "en", "value" => "Episkopi" ),
					array( "language" => "nl", "value" => "Episkopi" ),
				),
				"descriptions" => array(
					array( "language" => "de", "value" => "Sitz der Verwaltung der Mittelmeerinsel Zypern." ),
					array( "language" => "en", "value" => "The capital of Akrotiri and Dhekelia." ),
					array( "language" => "nl", "value" => "Het bestuurlijke centrum van Akrotiri en Dhekelia." ),
				),
			)
		),
		'Leipzig' => array(
			"new" => "item",
			"data" => array(
				"labels" => array(
					array( "language" => "de", "value" => "Leipzig" ),
				),
				"descriptions" => array(
					array( "language" => "de", "value" => "Stadt in Sachsen." ),
					array( "language" => "en", "value" => "City in Saxony." ),
				),
			)
		),
		'Guangzhou' => array(
			"new" => "item",
			"data" => array(
				"labels" => array(
					array( "language" => "de", "value" => "Guangzhou" ),
					array( "language" => "yue", "value" => "廣州" ),
					array( "language" => "zh-cn", "value" => "广州市" ),
				),
				"descriptions" => array(
					array( "language" => "en", "value" => "Capital of Guangdong." ),
					array( "language" => "zh-hk", "value" => "廣東的省會。" ),
				),
			)
		),

	);

	/**
	 * Get the entity with the given handle
	 * @param $handle string of entity to get data for
	 * @throws OutOfBoundsException
	 * @return array of entity data
	 */
	public static function getEntity( $handle ){
		if( !array_key_exists( $handle, self::$entityData ) ){
			throw new OutOfBoundsException( "No entity defined with handle {$handle}" );
		}
		$entity = self::$entityData[ $handle ];

		if ( !is_string( $entity['data'] ) ) {
			$entity['data'] = json_encode( $entity['data'] );
		}

		return $entity;
	}

	/**
	 * Get the data to pass to the api to clear the entity with the given handle
	 * @param $handle string of entity to get data for
	 * @throws OutOfBoundsException
	 * @return array|null
	 */
	public static function getEntityClear( $handle ){
		if( !array_key_exists( $handle, self::$activeHandles ) ){
			throw new OutOfBoundsException( "No entity clear data defined with handle {$handle}" );
		}
		$id = self::$activeHandles[ $handle ];
		self::unRegisterEntity( $handle );
		return array( 'id' => $id, 'data' => '{}', 'clear' => '' );
	}

	/**
	 * Get the data to pass to the api to create the entity with the given handle
	 * @param $handle
	 * @return mixed
	 * @throws OutOfBoundsException
	 */
	public static function getEntityData( $handle ){
		if( !array_key_exists( $handle, self::$entityData ) ){
			throw new OutOfBoundsException( "No entity defined with handle {$handle}" );
		}
		return self::$entityData[ $handle ]['data'];
	}

	/**
	 * Get the data of the entity with the given handle we received after creation
	 * @param string $handle
	 * @param null|array $props array of props we want the output to have
	 * @param null|array $langs array of langs we want the output to have
	 * @throws OutOfBoundsException
	 * @return mixed
	 */
	public static function getEntityOutput( $handle, $props = null, $langs = null ){
		if( !array_key_exists( $handle, self::$entityOutput ) ){
			throw new OutOfBoundsException( "No entity output defined with handle {$handle}" );
		}
		if( !is_array( $props ) ){
			return self::$entityOutput[ $handle ];
		} else {
			return self::stripUnwantedOutputValues( self::$entityOutput[ $handle ], $props, $langs );
		}
	}

	/**
	 * Remove props and langs that are not included in $props or $langs from the $entityOutput array
	 * @param array $entityOutput Array of entity output
	 * @param null|array $props Props to keep in the output
	 * @param null|array $langs Languages to keep in the output
	 * @return array Array of entity output with props and langs removed
	 */
	protected static function stripUnwantedOutputValues( $entityOutput, $props = null, $langs = null  ){
		$entityProps = array();
		foreach( $props as $prop ){
			if( array_key_exists( $prop, $entityOutput ) ){
				$entityProps[ $prop ] = $entityOutput[ $prop ] ;
			}
		}
		foreach( $entityProps as $prop => $value ){
			if( ( $prop == 'aliases' || $prop == 'labels' || $prop == 'descriptions' ) && $langs != null && is_array( $langs ) ){
				$langValues = array();
				foreach( $langs as $langCode ){
					if( array_key_exists( $langCode, $value ) ){
						$langValues[ $langCode ] = $value[ $langCode ];
					}
				}
				if( $langValues === array() ){
					unset( $entityProps[ $prop ] );
				} else {
					$entityProps[ $prop ] = $langValues;
				}

			}
		}
		return $entityProps;
	}

	/**
	 * Register the entity after it has been created
	 * @param $handle
	 * @param $id
	 * @param null $entity
	 */
	public static function registerEntity( $handle, $id, $entity = null) {
		self::$activeHandles[ $handle ] = $id;
		self::$activeIds[ $id ] = $handle;
		if( $entity ){
			self::$entityOutput[ $handle ] = $entity;
		}
	}

	/**
	 * Unregister the entity after it has been cleared
	 * @param $handle
	 * @throws OutOfBoundsException
	 */
	private static function unRegisterEntity( $handle ) {
		unset( self::$activeIds[ self::$activeHandles[ $handle ] ] );
		unset( self::$activeHandles[ $handle ] );
	}

	/**
	 * Returns an array of currently activated handles
	 * @return array of currently active handles
	 */
	public static function getActiveHandles(){
		$usedHandles = self::$activeHandles;
		return $usedHandles;
	}

	/**
	 * Return the id for the entity with the given handle
	 * @param $handle string of handles
	 * @throws OutOfBoundsException
	 * @return null|string id of current handle (if active)
	 */
	public static function getId( $handle ){
		if( !array_key_exists( $handle, self::$activeHandles ) ){
			throw new OutOfBoundsException( "No entity id defined with handle {$handle}" );
		}
		return self::$activeHandles[ $handle ];
	}

	/**
	 * @param $id string of entityid
	 * @return null|string id of current handle (if active)
	 */
	public static function getHandle( $id ){
		if( array_key_exists( $id, self::$activeIds ) ){
			return self::$activeIds[ $id ];
		}
		return null;
	}

}
