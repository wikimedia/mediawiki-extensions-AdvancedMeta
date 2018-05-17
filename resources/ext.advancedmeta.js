
( function( mw, $ ){
	mw.advancedmeta = mw.advancedmeta || {};
	$(document).on( 'click', '#ca-advancedmeta', function( e ) {
		if( !mw.advancedmeta.dialog ) {
			return;
		}
		var windowManager = new OO.ui.WindowManager( {
			factory: mw.advancedmeta.factory
		} );
		$( 'body' ).append( windowManager.$element );

		windowManager.openWindow( 'advancedmeta' );
		e.stopPropagation();
		return false;
	});

	mw.loader.using( 'oojs-ui', function() {

		mw.advancedmeta.factory = new OO.Factory();

		mw.advancedmeta.data = mw.config.get(
			'AdvancedMeta',
			{}
		);

		mw.advancedmeta.dialog = function( config ) {
			mw.advancedmeta.dialog.super.call( this, config );
		};
		OO.inheritClass( mw.advancedmeta.dialog, OO.ui.ProcessDialog );
		OO.initClass( mw.advancedmeta.dialog );

		// Specify a symbolic name (e.g., 'simple', in this example) using the static 'name' property.
		mw.advancedmeta.dialog.static.name = 'advancedmeta';
		mw.advancedmeta.dialog.static.title = mw.message(
			'ameta-metasettings'
		).plain();
		mw.advancedmeta.dialog.static.actions = [{
			action: 'save',
			label: mw.message( 'advancedmeta-dialog-btn-label-save' ).plain(),
			flags: [ 'primary', 'constructive' ],
			disabled: false
		}, {
			action: 'cancel',
			label: mw.message( 'advancedmeta-dialog-btn-label-cancel' ).plain(),
			flags: 'safe'
		}, {
			action: 'delete',
			label: mw.message( 'advancedmeta-dialog-btn-label-delete' ).plain(),
			flags: 'destructive'
		}];

		mw.advancedmeta.dialog.prototype.initialize = function () {
			mw.advancedmeta.dialog.super.prototype.initialize.call( this );

			this.panel = new OO.ui.PanelLayout( {
				padded: true,
				expanded: false,
				id: 'advancedmeta-manager'
			});
			this.content = new OO.ui.FieldsetLayout();
			this.errorSection = new OO.ui.Layout();
			this.errorSection.$element.css( 'color', 'red' );
			this.errorSection.$element.css( 'font-weight', 'bold' );
			this.errorSection.$element.css( 'text-align', 'center' );

			this.alias = this.makeAliasInput();
			this.description = this.makeDescriptionInput();
			this.index = this.makeIndexInput();
			this.follow = this.makeFollowInput();
			this.keywords = this.makeKeywordsInput();

			this.content.addItems([
				this.errorSection,
				new OO.ui.FieldLayout( this.alias, {
					label: mw.message( 'ameta-titlealias' ).plain(),
					align: 'top'
				} ),
				new OO.ui.FieldLayout( this.index, {
					label: mw.message( 'advancedmeta-dialog-input-label-index' ).plain(),
					align: 'inline'
				} ),
				new OO.ui.FieldLayout( this.follow, {
					label: mw.message( 'advancedmeta-dialog-input-label-follow' ).plain(),
					align: 'inline'
				} ),
				new OO.ui.FieldLayout( this.description, {
					label: mw.message( 'advancedmeta-dialog-input-label-description' ).plain(),
					align: 'top'
				} ),
				new OO.ui.FieldLayout( this.keywords, {
					label: mw.message( 'advancedmeta-dialog-input-label-keywords' ).plain(),
					align: 'top',
					help: mw.message( 'advancedmeta-dialog-input-help-keywords' ).plain()
				} )
			]);

			this.panel.$element.append( this.content.$element );
			this.$body.append( this.panel.$element );
		};

		mw.advancedmeta.dialog.prototype.makeAliasInput = function() {
			return new OO.ui.TextInputWidget( {
				value: mw.advancedmeta.data.alias,
				required: false,
				disabled: false
			});
		}

		mw.advancedmeta.dialog.prototype.makeDescriptionInput = function() {
			if( mw.config.get( 'wgVersion' ) < "1.31" ) {
				return new OO.ui.TextInputWidget( {
					value: mw.advancedmeta.data.description,
					required: false,
					disabled: false,
					multiline: true
				});
			}
			return new OO.ui.MultilineTextInputWidget( {
				value: mw.advancedmeta.data.description,
				required: false,
				disabled: false
			});
		}

		mw.advancedmeta.dialog.prototype.makeIndexInput = function() {
			return new OO.ui.CheckboxInputWidget( {
				required: false,
				disabled: false,
				selected: mw.advancedmeta.data.index || false
			});
		}

		mw.advancedmeta.dialog.prototype.makeFollowInput = function() {
			return new OO.ui.CheckboxInputWidget( {
				required: false,
				disabled: false,
				selected: mw.advancedmeta.data.follow || false
			});
		}

		mw.advancedmeta.dialog.prototype.makeKeywordsInput = function() {
			if( mw.config.get( 'wgVersion' ) < "1.31" ) {
				return new OO.ui.TextInputWidget( {
					value: mw.advancedmeta.data.keywords,
					required: false,
					disabled: false,
					multiline: true
				});
			}
			return new OO.ui.MultilineTextInputWidget( {
				value: mw.advancedmeta.data.keywords,
				required: false,
				disabled: false
			});
		}

		mw.advancedmeta.dialog.prototype.save = function() {
			var api = new mw.Api();
			return api.postWithToken( 'csrf', {
				action: 'advancedmeta-tasks',
				task: 'save',
				format: 'json',
				taskdata: JSON.stringify( this.getData() )
			});
		};

		mw.advancedmeta.dialog.prototype.delete = function() {
			var api = new mw.Api();
			return api.postWithToken( 'csrf', {
				action: 'advancedmeta-tasks',
				task: 'delete',
				format: 'json',
				taskdata: JSON.stringify( this.getData() )
			});
		};

		mw.advancedmeta.dialog.prototype.getData = function() {
			var data = {};

			data.articleId = mw.config.get( 'wgArticleId', 0 );

			data.description = this.description.getValue();
			data.follow = this.follow.isSelected();
			data.index = this.index.isSelected();
			data.alias = this.alias.getValue();
			data.keywords = this.keywords.getValue().split( ',' );

			return data;
		};

		mw.advancedmeta.dialog.prototype.getActionProcess = function ( action ) {
			return mw.advancedmeta.dialog.super.prototype.getActionProcess.call( this, action )
			.next( function () {
				return 1000;
			}, this )
			.next( function () {
				var closing;
				if ( action === 'save' ) {
					if ( this.broken ) {
						this.broken = false;
						return new OO.ui.Error( 'Server did not respond' );
					}
					var me = this;
					return me.save().done( function( data ) {
						//success is just emtyed out somewhere for no reason
						if( data.message.length === 0 ) {
							closing = me.close( { action: action } );
							me.reloadPage();
							return closing;
						}
						me.showRequestErrors( [data.message] );
					});
				} else if ( action === 'cancel' ) {
					closing = this.close( { action: action } );
					return closing;
				}
				else if ( action === 'delete' ) {
					var me = this;
					return this.delete().done( function( data ) {
						//success is just emtyed out somewhere for no reason
						if( data.message.length === 0 ) {
							closing = me.close( { action: action } );
							me.reloadPage();
							return closing;
						}
						me.showRequestErrors( data.message );
					});
					return closing;
				}

				return mw.advancedmeta.dialog.super.prototype.getActionProcess.call(
					this,
					action
				);
			}, this );
		};

		mw.advancedmeta.dialog.prototype.showRequestErrors = function( errors ) {
			var errors = errors || {};

			var error = '';
			for( var i in errors ) {
				error += errors[i] + "<br />";
			}

			this.errorSection.$element.html( error );
		};

		mw.advancedmeta.dialog.prototype.reloadPage = function() {
			window.location = mw.util.getUrl(
				mw.config.get( 'wgPageName' ),
				{ 'action': 'purge' }
			);
		};

		mw.advancedmeta.factory.register( mw.advancedmeta.dialog );
	});
})( mediaWiki, jQuery );
