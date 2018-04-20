window.wp = window.wp || {};



( function( $, _ ) {

    var media = wp.media,
        l10n = media.view.l10n,
        original = {};



    _.extend( media.view.MediaFrame.emlManage.prototype, {

        createStates: function() {

            var options = this.options;

            if ( this.options.states ) {
                return;
            }

            this.states.add([

                new media.controller.Library({
                    library            : media.query( options.library ),
                    title              : options.title,
                    multiple           : options.multiple,

                    content            : 'browse',
                    toolbar            : 'bulk-edit',
                    menu               : false,
                    router             : false,

                    contentUserSetting : true,

                    searchable         : true,
                    filterable         : 'all',

                    autoSelect         : true,
                    idealColumnWidth   : $( window ).width() < 640 ? 135 : 150
                })
            ]);
        },

        bindHandlers: function() {

            media.view.MediaFrame.Select.prototype.bindHandlers.apply( this, arguments );

            this.on( 'toolbar:create:bulk-edit', this.createToolbar, this );
            this.on( 'toolbar:render:bulk-edit', this.selectionStatusToolbar, this );
            this.on( 'edit:attachment', this.openEditAttachmentModal, this );
        },

        selectionStatusToolbar: function( view ) {

            view.set( 'selection', new media.view.Selection({
                controller: this,
                collection: this.state().get('selection'),
                priority:   -40,
            }).render() );
        }
    });



    _.extend( media.view.Button.DeleteSelected.prototype, {

        click: function() {

            var view = this.controller.toolbar.get(),
                toolbar = view.get( 'selection' ),
                selection = this.controller.state().get( 'selection' );


            if ( media.view.settings.mediaTrash ) {

                if ( 'trash' !== selection.at( 0 ).get( 'status' ) ) {
                    toolbar.doBulk( 'trash' );
                }
                else {
                    toolbar.doBulk( 'restore' );
                }
            }
            else {
                emlConfirmDialog( eml.l10n.delete_warning_title, eml.l10n.delete_warning_text, eml.l10n.delete_warning_yes, eml.l10n.delete_warning_no, 'button button-primary' )
                .done( function() {
                    toolbar.doBulk( 'delete' );
                })
                .fail(function() {
                    return;
                });
            }
        }
    });



    _.extend( media.view.Button.DeleteSelectedPermanently.prototype, {

        click: function() {

            var view = this.controller.toolbar.get(),
                toolbar = view.get( 'selection' ),
                selection = this.controller.state().get( 'selection' );


            emlConfirmDialog( eml.l10n.delete_warning_title, eml.l10n.delete_warning_text, eml.l10n.delete_warning_yes, eml.l10n.delete_warning_no, 'button button-primary' )
            .done( function() {
                toolbar.doBulk( 'delete' );
            })
            .fail(function() {
                return;
            });
        }
    });



    // TODO: move to PHP side
    $('body').addClass('eml-grid');


})( jQuery, _ );
