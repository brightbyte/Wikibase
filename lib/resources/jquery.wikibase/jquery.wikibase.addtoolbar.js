/**
 * @file
 * @ingroup WikibaseLib
 *
 * @licence GNU GPL v2+
 * @author H. Snater < mediawiki@snater.com >
 */
( function( mw, wb, $ ) {
	'use strict';

	/**
	 * "Add" toolbar widget
	 * @since 0.4
	 *
	 * This widget offers an "add" button which will allow interaction with a given widget.
	 * The widget the toolbar shall interact with has to have implemented certain methods listed in
	 * the _requiredMethods attribute.
	 *
	 * @option interactionWidgetName {string} Name of the widget the toolbar shall interact with.
	 *         (That widget needs to be initialized on the same DOM node this toolbar is initialized
	 *         on.) If the interactionWidgetName option is omitted, the toolbar will be initialized
	 *         as well but proper interaction is not ensured (see _create() method).
	 *         When omitting the interactionWidgetName option, the "action" option should be set.
	 *         Default value: null (no interaction widget)
	 *
	 * @option toolbarParentSelector {string} (required) jQuery selector to find the node the actual
	 *         toolbar buttons shall be appended to.
	 *
	 * @option action {function} Custom action the add button shall trigger. The action
	 *         will be triggered only when no interaction widget via the interactionWidgetName
	 *         option is set.
	 *         Default value: null (no custom action)
	 *
	 * @option eventPrefix {string} Custom event prefix the events the toolbar will listen to will
	 *         be prefixed with. If not set but an interaction widget is defined via the
	 *         interactionWidgetName option, the interaction widget's event prefix will be used.
	 *         Default value: '' (use interaction widget's event prefix or no prefix if no
	 *         interaction widget is defined.
	 *
	 * @option addButtonLabel {string} The add button's label
	 *         Default value: mw.msg( 'wikibase-add' )
	 */
	$.widget( 'wikibase.addtoolbar', {
		widgetName: 'wikibase-addtoolbar',
		widgetBaseClass: 'wb-addtoolbar',

		/**
		 * Options
		 * @type {Object}
		 */
		options: {
			interactionWidgetName: null,
			toolbarParentSelector: null,
			action: null,
			eventPrefix: '',
			addButtonLabel: mw.msg( 'wikibase-add' )
		},

		/**
		 * Names of methods that are required in the interaction widget to ensure proper toolbar
		 * interaction.
		 * @type {string[]}
		 */
		_requiredMethods: [
			'enterNewItem'
		],

		/**
		 * The widget the toolbar interacts with.
		 * @type {Object}
		 */
		_interactionWidget: null,

		/**
		 * The visible toolbar elements' parent node.
		 * @type {jQuery}
		 */
		$toolbarParent: null,

		/**
		 * The toolbar object.
		 * @type {wb.ui.Toolbar}
		 */
		toolbar: null,

		/**
		 * @see jQuery.Widget._create
		 */
		_create: function() {
			var self = this;

			if ( !this.options.toolbarParentSelector ) {
				throw new Error( 'jquery.wikibase.addtoolbar: Missing toolbar parent selector' );
			}

			this.$toolbarParent = this.element.find( this.options.toolbarParentSelector );

			this.toolbar = new wb.ui.Toolbar();
			this.toolbar.innerGroup = new wb.ui.Toolbar.Group();
			this.toolbar.btnAdd = new wb.ui.Toolbar.Button( this.options.addButtonLabel );
			this.toolbar.innerGroup.addElement( this.toolbar.btnAdd );
			this.toolbar.addElement( this.toolbar.innerGroup );

			if ( this.options.interactionWidgetName ) {
				this._interactionWidget = this.element.data( this.options.interactionWidgetName );

				var missingMethods = this.checkRequiredMethods();
				if ( missingMethods.length > 0 ) {
					var m = missingMethods.join( ', ' );
					throw new Error( 'jquery.wikibase.addtoolbar: Missing required method(s) ' + m );
				}
			}

			/**
			 * Prefixes an event name with the custom event prefix (if set in the options) or the
			 * interaction's widget's event prefix.
			 *
			 * @param {string} eventName
			 * @return {string}
			 */
			function prefixed( eventName ) {
				var eventPrefix = self.options.eventPrefix;
				if ( eventPrefix === '' && self._interactionWidget ) {
					eventPrefix = self._interactionWidget.widgetEventPrefix;
				}
				return eventPrefix + eventName;
			}

			// Register events for focusing "add" button.
			// TODO: replace this with more generic handling and perhaps don't use those events
			//  since they are rather specific
			this.element
			.on(
				prefixed( 'itemadded ' ) + prefixed( 'itemremoved ' ) + prefixed( 'canceled' ),
				function( event, value, $node ) {
					if ( value === null ) {
						return;
					}
					if ( $node !== undefined && $node.parent()[0] !== self.element.parent()[0] ) {
						// The event does not belong to this "add" button but rather to an "add"
						// button encapsulated in a descendant node.
						// TODO: This handling is rather specific for the claim section lists that
						//  is built within the claimview widget.
						$node.trigger( event.type );
					} else {
						event.stopImmediatePropagation();
						self.toolbar.btnAdd.setFocus();
					}
				}
			);

			$( this.toolbar.btnAdd ).on( 'action', function( event ) {
				if ( self._interactionWidget ) {
					self._interactionWidget.enterNewItem();
				} else if ( self.options.action ) {
					self.options.action();
				}
			} );

			this.toolbar.appendTo(
				$( '<div/>' ).addClass( 'wb-editsection' ).appendTo( this.$toolbarParent )
			);
		},

		/**
		 * @see $.widget.destroy
		 */
		destroy: function() {
			this.toolbar.destroy();
			$.Widget.prototype.destroy.call( this );
		},

		/**
		 * Checks whether all methods required in the interaction widget are defined and will return
		 * the names of any missing methods.
		 * @since 0.4
		 *
		 * @return {string[]}
		 */
		checkRequiredMethods: function() {
			var self = this,
				missingMethods = [];
			$.each( this._requiredMethods, function( i, methodName ) {
				if ( !$.isFunction( self._interactionWidget[methodName] ) ) {
					missingMethods.push( methodName );
				}
			} );
			return missingMethods;
		}

	} );

}( mediaWiki, wikibase, jQuery ) );