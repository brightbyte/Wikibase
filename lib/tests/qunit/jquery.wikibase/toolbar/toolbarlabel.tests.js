/**
 * @since 0.4
 * @file
 * @ingroup WikibaseLib
 *
 * @licence GNU GPL v2+
 * @author H. Snater < mediawiki@snater.com >
 */

( function( wb, $, QUnit ) {
	'use strict';

	/**
	 * Factory for creating a new $.wikibase.label instance.
	 *
	 * @param {Object} [options]
	 * @return {jQuery}
	 */
	var newTestLabel = function( options ) {
		options = options || {};

		return $( '<span/>' )
			.text( 'Text' )
			.addClass( 'test_label' )
			.toolbarlabel( options );
	};

	QUnit.module( 'jquery.wikibase.toolbarlabel', QUnit.newWbEnvironment( {
		teardown: function() {
			$( '.test_label' ).each( function( i, node ) {
				var $node = $( node );

				if( $node.data( 'toolbarlabel' ) ) {
					$node.data( 'toolbarlabel' ).destroy();
				}

				$node.remove();
			} );
		}
	} ) );

	QUnit.test( 'Init and destroy.', function( assert ) {
		var $node = newTestLabel(),
			label = $node.data( 'toolbarlabel' );

		assert.ok(
			$node.data( 'toolbarlabel' ) instanceof $.wikibase.toolbarlabel,
			'Initialized label.'
		);

		assert.equal(
			$node.text(),
			'Text',
			'Verified node text.'
		);

		label.destroy();

		assert.equal(
			$node.data( 'toolbarlabel' ),
			undefined,
			'Destroyed label.'
		);
	} );

	QUnit.test( 'Disable and enable', function( assert ) {
		var $node = newTestLabel(),
			label = $node.data( 'toolbarlabel' );

		assert.ok(
			!label.isDisabled(),
			'Label is enabled.'
		);

		label.disable();

		assert.ok(
			label.isDisabled(),
			'Disabled label.'
		);

		label.disable();

		assert.ok(
			label.isDisabled(),
			'Label still disabled after disabling twice.'
		);

		label.enable();

		assert.ok(
			!label.isDisabled(),
			'Enabled label.'
		);

		label.enable();

		assert.ok(
			!label.isDisabled(),
			'Label still enabled after enabling twice.'
		);

		label.option( 'stateChangeable', false );

		assert.ok(
			!label.isDisabled(),
			'Unable to disable label after settings sateChangeable to false.'
		);

		label.option( 'stateChangeable', true );

		label.disable();

		label.option( 'stateChangeable', false );

		label.enable();

		assert.ok(
			label.isDisabled(),
			'Unable to enable disabled label when sateChangeable is set to false.'
		);

	} );

}( wikibase, jQuery, QUnit ) );
