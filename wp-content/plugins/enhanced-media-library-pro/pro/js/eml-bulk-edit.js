window.wp = window.wp || {};
window.eml = window.eml || { l10n: {} };



( function( $, _ ) {

    var media = wp.media,
        Attachments = media.model.Attachments,
        l10n = media.view.l10n,
        original = {};



    _.extend( eml.l10n, wpuxss_eml_pro_bulk_edit_l10n );



    /**
     * wp.media.model.Selection
     *
     */
    _.extend( media.model.Selection.prototype, {

        bulkSave: function( data, options ) {

            var attachments = this.models;


            return media.post( 'eml-save-attachments', _.defaults({
                nonce: eml.l10n.bulk_edit_nonce,
            }, data ) )
            .done( function( resp, status, xhr ) {

                _.each( resp['tcount'], function( count, term_id ) {

                    var $option = $( '.eml-taxonomy-filters option[value="'+term_id+'"]' ),
                        text = $option.text();

                    text = text.replace( /\(.*?\)/, '('+count+')' );
                    $option.text( text );
                });


                _.each( attachments, function( attachment ) {

                    if ( ! _.isUndefined( resp[attachment.id] ) ) {

                        var taxonomies = attachment.get( 'taxonomies' ),
                            compat = attachment.get( 'compat' ),
                            html = $("<div/>").html( compat.item );


                        _.each( resp[attachment.id]['taxonomies'], function( term_ids, taxonomy ) {

                            taxonomies[taxonomy] = term_ids;

                            $( '.term-list input[type="checkbox"][name^="tax_input['+taxonomy+']"]', html ).removeAttr('checked');

                            _.each( term_ids, function( term_id ) {

                                $( '.term-list input[type="checkbox"][name="tax_input['+taxonomy+']['+term_id+']"]', html ).attr('checked','checked');
                            });
                        });

                        compat.item = html.html();
                    }
                });
            });
        }
    });



    /**
     * wp.media.view.Attachment
     *
     */

    _.extend( media.view.Attachment.prototype, {

        // TODO: reconsider this along with whole single / unsingle / bulk mechanism
        checkClickHandler: function( event ) {

            var selection = this.options.selection;

            if ( ! selection ) {
                return;
            }

            event.stopPropagation();

            if ( selection.where( { id: this.model.get( 'id' ) } ).length ) {
                selection.remove( this.model );

                this.$el.focus();
            } else {
                selection.reset();
                selection.add( this.model );

            }
            selection.trigger( 'selection:unsingle', selection.model, selection );
            selection.trigger( 'selection:single', selection.model, selection );
        }
    });



    /**
     * wp.media.view.Selection
     *
     */
    _.extend( media.view.Selection.prototype, {

        _stopSelecting: false,

        template:  media.template('media-bulk-selection'),

        events: {
            'click .deselect'           : 'deselect',
            'click .select'             : 'select',
            'click .delete'             : 'bulk',
            'click .trash'              : 'bulk',
            'click .restore'            : 'bulk',
            'click .delete-permanently' : 'bulk'
        },

        deselect: function( event ) {

            event.preventDefault();

            this.collection.reset();
            this._stopSelecting = true;

            // Keep focus inside media modal
            if ( this.controller.modal ) {
                this.controller.modal.focusManager.focus();
            }
        },

        select: function( event ) {

            var library = this.controller.state().get('library'),
                selection = this.collection,
                spinner = this.controller.content.get().toolbar.get('spinner'),
                self = this;

            if ( event ) {
                event.preventDefault();
            }

            if ( ! library.length || spinner.spinnerTimeout ) {
                return;
            }

            this._stopSelecting = false;

            selection.reset( library.models );

            selection.trigger( 'selection:unsingle', selection.model, selection );
            selection.trigger( 'selection:single', selection.model, selection );

            if ( library.hasMore() ) {

                spinner.show();
                emlFullscreenSpinnerStart( eml.l10n.in_progress_select_text + '...' );
                loadAll();
            }

            function loadAll() {

                library.more().done( function( resp ) {

                    if ( self._stopSelecting ) {

                        selection.reset();
                        spinner.hide();
                        emlFullscreenSpinnerStop();
                    }
                    else {

                        selection.reset( this.models );

                        selection.trigger( 'selection:unsingle', selection.model, selection );
                        selection.trigger( 'selection:single', selection.model, selection );

                        if ( this._hasMore ) {
                            spinner.show();
                            loadAll();
                        }
                        else {
                            spinner.hide();
                            emlFullscreenSpinnerStop();
                        }
                    }
                });
            }
        },


        bulk: function( event ) {

            var self = this,
                action = $( event.currentTarget ).data( 'action' ),
                selection = this.collection,
                spinner = this.controller.content.get().toolbar.get('spinner');


            if ( event ) {
                event.preventDefault();
            }

            if ( ! selection.length || spinner.spinnerTimeout ) {
                return;
            }


            if ( media.view.settings.mediaTrash && ( 'trash' === action || 'restore' === action ) ) {
                this.doBulk( action );
            }
            else {
                emlConfirmDialog( eml.l10n.delete_warning_title, eml.l10n.delete_warning_text, eml.l10n.delete_warning_yes, eml.l10n.delete_warning_no, 'button button-primary' )
                .done( function() {
                    self.doBulk( action );
                })
                .fail(function() {
                    return;
                });
            }
        },

        doBulk: function( action ) {

            var data = {},
                selection = this.collection,
                controller = this.controller,
                library = controller.state().get( 'library' ),
                content = controller.content.get(),
                spinner = content.toolbar.get('spinner'),
                spinnerText,
                $errorMessage = controller.toolbar.get().primary.$el.find('#eml-bulk-save-changes-failure');


            _.each( selection.models, function( attachment ) {
                data[ 'attachments['+attachment.id+']' ] = attachment.id;
            });

            if ( _.isEmpty( data ) ) {
                return;
            }


            if ( 'trash' === action ) {
                spinnerText = eml.l10n.in_progress_trash_text + '...';
            }
            else if ( 'restore' === action ) {
                spinnerText = eml.l10n.in_progress_restore_text + '...';
            }
            else {
                spinnerText = eml.l10n.in_progress_delete_text + '...';
            }


            spinner.show();
            emlFullscreenSpinnerStart( spinnerText );


            media.post( 'eml-bulk-attachments', _.defaults({
                nonce       : eml.l10n.bulk_edit_nonce,
                bulk_action : action
            }, data ) )
            .always( function() {
                spinner.hide();
                emlFullscreenSpinnerStop();
            })
            .done( function( resp ) {

                var ids = _.keys( resp );

                _.each( resp['tcount'], function( count, term_id ) {

                    var $option = $( '.eml-taxonomy-filters option[value="'+term_id+'"]' ),
                        text = $option.text();

                    text = text.replace( /\(.*?\)/, '('+count+')' );
                    $option.text( text );
                });

                _.each( ids, function( id ) {
                    var attachment = media.attachment( id );

                    if ( 'trash' === action ) {
                        attachment.set( 'status', 'trash' );
                    }
                    else if ( 'restore' === action ) {
                        attachment.set( 'status', 'inherit' );
                    }
                    else {
                        attachment.set( 'destroyed', true );
                        // attachment.destroy();
                    }
                });

                selection.reset();

                library.remove( ids );
                library.mirroring.remove( ids );

                library.reset( library.models );
                library.reset( library.mirroring.models );

                // Clean queries' cache regardless of all or some might be deleted
                media.model.Query.cleanQueries();


            })
            .fail( function() {

                $errorMessage.fadeIn( 400 );
                setTimeout( function() {
                    $errorMessage.fadeOut( 400 );
                }, 10000 );
            });



            // Keep focus inside media modal
            if ( controller.modal ) {
                controller.modal.focusManager.focus();
            }
        }
    });



    /**
     * wp.media.view.AttachmentsBrowser
     *
     * TODO: revise all AttachmentsBrowser code
     *
     */
    original.AttachmentsBrowser = {

        createSingle: media.view.AttachmentsBrowser.prototype.createSingle
    };

    _.extend( media.view.AttachmentsBrowser.prototype, {

        createSingle: function() {

            var sidebar = this.sidebar,
                selection = this.options.selection,
                single = selection.single(),
                taxonomies = {},
                state = this.controller.state().get('id');


            if ( 'gallery' === state || 'playlist' === state || 'video-playlist' === state ) {
                original.AttachmentsBrowser.createSingle.apply( this, arguments );
                return;
            }


            if ( selection.length > 1 &&
                ( parseInt( eml.l10n.is_tax_compat ) ||
                  ! _.isUndefined( this.views.parent ) &&
                  ( this.views.parent.isModeActive( 'eml-grid' ) ||
                    this.views.parent.isModeActive( 'eml-bulk-edit' ) ) ) ) {

                sidebar.set( 'bulk-edit', new media.view.emlAttachmentsDetails({
                    controller : this.controller,
                    model      : single,
                    priority   : 80
                }) );

                // TODO: find a better solution
                if ( this.controller.isModeActive( 'select' ) ) {
                    $sidebar_el = sidebar.$el;
                    $.each( eml.l10n.compat_taxonomies_to_hide, function( id, taxonomy ) {
                        $sidebar_el.find( 'table.compat-attachment-fields tr.compat-field-'+taxonomy ).remove();
                    });
                }

                _.each( selection.models, function( attachment ) {

                    _.each( attachment.get('taxonomies'), function( term_ids, taxonomy ) {

                        if ( ! ( taxonomy in taxonomies ) )
                            taxonomies[taxonomy] = {};

                        $.each( term_ids, function( id, term_id ) {

                            if ( ! ( term_id in taxonomies[taxonomy] ) )
                                taxonomies[taxonomy][term_id] = 1;
                            else
                                taxonomies[taxonomy][term_id]++;
                        });
                    });
                });

                _.each( taxonomies, function( term_ids, taxonomy ) {

                    _.each( term_ids, function( count, term_id ) {

                        if ( count == selection.length ) {

                            $('.attachments-details .term-list input[name="tax_input['+taxonomy+']['+term_id+']"]').prop('checked',true).prop('indeterminate',false);

                        } else if ( count > 0 && count < selection.length ) {

                            $('.attachments-details .term-list input[name="tax_input['+taxonomy+']['+term_id+']"]').prop('checked',true).prop('indeterminate',true);

                        }
                    });
                });


                sidebar.$el.find( 'input[type=checkbox]' ).each( function() {

                    if ( $( this ).prop( 'checked' ) && ! $( this ).prop( 'indeterminate' ) ) {
                        $( this ).attr( 'title', eml.l10n.toolTip_all )
                        .parent( 'label' ).attr( 'title', eml.l10n.toolTip_all );
                    }

                    if ( $( this ).prop( 'checked' ) && $( this ).prop( 'indeterminate' ) ) {
                         $( this ).attr( 'title', eml.l10n.toolTip_some )
                         .parent( 'label' ).attr( 'title', eml.l10n.toolTip_some );
                    }

                    if ( ! $( this ).prop( 'checked' ) && $( this ).prop( 'indeterminate' ) ) {
                        $( this ).prop( 'indeterminate', false );
                    }

                    if ( ! $( this ).prop( 'checked' ) && ! $( this ).prop( 'indeterminate' ) ) {
                         $( this ).attr( 'title', eml.l10n.toolTip_none )
                         .parent( 'label' ).attr( 'title', eml.l10n.toolTip_none );
                    }
                });
            }
            else {

                sidebar.set( 'details', new media.view.Attachment.Details({
                    controller: this.controller,
                    model:      single,
                    priority:   80
                }) );

                sidebar.set( 'compat', new media.view.AttachmentCompat({
                    controller: this.controller,
                    model:      single,
                    priority:   120
                }) );

                if ( this.options.display ) {
                    sidebar.set( 'display', new media.view.Settings.AttachmentDisplay({
                        controller:   this.controller,
                        model:        this.model.display( single ),
                        attachment:   single,
                        priority:     160,
                        userSettings: this.model.get('displayUserSettings')
                    }) );
                }
            }

            // Show the sidebar on mobile
            if ( this.model.id === 'insert' ) {
                sidebar.$el.addClass( 'visible' );
            }
        },

        disposeSingle: function() {

            var sidebar = this.sidebar;

            sidebar.unset('details');
            sidebar.unset('compat');
            sidebar.unset('display');
            sidebar.unset('bulk-edit');

            // Hide the sidebar on mobile
            sidebar.$el.removeClass( 'visible' );
        }
    });



    /**
     * wp.media.view.emlAttachmentsDetails
     *
     * Custom view for bulk edit compat
     *
     */
    media.view.emlAttachmentsDetails = media.View.extend({

        tagName   : 'div',
        className : 'attachments-details',
        template  : media.template( 'attachments-details' ),

        attributes: {
            tabIndex: 0
        },

        events: {
            'submit'       : 'preventDefault',
            //'change input' : 'preSave',
            // using click instead of change
            // because some browsers don't change 'checked' when clicking on 'indeterminate'
            'click input'  : 'preSave',

            // possibly more fields for future
            //'change select'   : wpuxss_eml_pro_bulkedit_savebutton_off == 1 ? 'save' : '',
            //'change textarea' : wpuxss_eml_pro_bulkedit_savebutton_off == 1 ? 'save' : ''
        },

        initialize: function() {

            var $primaryToolbar = this.controller.toolbar.get().primary.$el;

            // TODO: use media.view instead
            if ( ! parseInt( eml.l10n.bulk_edit_save_button_off ) && _.isUndefined( this.$saveButton ) )
            {
                $primaryToolbar.append( '<a href="#" class="button media-button button-primary button-large eml-button-bulk-save-changes">'+eml.l10n.saveButton_text+'</a>' );
                $primaryToolbar.append( '<span id="eml-bulk-save-changes-spinner" class="spinner"></span>' );
                $primaryToolbar.append( '<div id="eml-bulk-save-changes-success" class="updated"><p><strong>'+eml.l10n.saveButton_success+'</strong></p></div>' );
                $primaryToolbar.append( '<div id="eml-bulk-save-changes-failure" class="error"><p>'+eml.l10n.saveButton_failure+'</p></div>' );

                this.$saveButton = $primaryToolbar.find('.eml-button-bulk-save-changes');
                this.$errorMessage = $primaryToolbar.find('#eml-bulk-save-changes-failure').hide();
                this.$successMessage = $primaryToolbar.find('#eml-bulk-save-changes-success').hide();
            }


            if ( parseInt( eml.l10n.bulk_edit_save_button_off ) && _.isUndefined( this.spinner ) )
            {
                $primaryToolbar.append( '<span id="eml-bulk-save-changes-spinner" class="spinner"></span>' );
            }

            this.spinner = new media.view.Spinner({
                el: $primaryToolbar.find('#eml-bulk-save-changes-spinner'),
                delay: 0
            });

            if ( ! parseInt( eml.l10n.bulk_edit_save_button_off ) )
            {
                this.$saveButton.on( 'click', _.bind( this.save, this ) );
            }

            this.on( 'ready', this.disableCheckboxes, this );

            wp.Uploader.queue.on( 'reset', this.enableCheckboxes, this );
        },

        disableCheckboxes: function() {

            if ( wp.Uploader.queue.length ) {
                this.$el.find('input').prop('disabled', true);
            }
        },

        enableCheckboxes: function() {

            if ( ! wp.Uploader.queue.length ) {
                this.$el.find('input').prop('disabled', false);
            }
        },

        remove: function() {

            var result;

            if ( ! _.isUndefined( this.$saveButton ) ) {

                this.$saveButton.off( 'click' );

                this.$saveButton.remove();
                this.spinner.$el.remove();
                this.$errorMessage.remove();
                this.$successMessage.remove();
            }

            result = media.View.prototype.remove.apply( this, arguments );

            return result;
        },

        preSave: function( event ) {

            var $checkbox = $( event.currentTarget );

            this.$checkbox = $checkbox;

            $checkbox.prop( 'indeterminate', false );
            $checkbox.prev( 'input' ).prop( 'indeterminate', false );

            if ( $checkbox.prop( 'checked' ) ) {
                $checkbox.attr( 'title', eml.l10n.toolTip_all )
                .parent( 'label' ).attr( 'title', eml.l10n.toolTip_all );
            } else {
                $checkbox.attr( 'title', eml.l10n.toolTip_none )
                .parent( 'label' ).attr( 'title', eml.l10n.toolTip_none );
            }

            if ( parseInt( eml.l10n.bulk_edit_save_button_off ) ) {
                this.save();
            }
        },

        save: function( event ) {

            var data = {},
                $form = this.$el.children('form.compat-item'),
                attachments = this.controller.state().get( 'selection' ),
                $successMessage = this.$successMessage,
                $errorMessage = this.$errorMessage,
                spinner = this.spinner,
                tt, terms2add=[], terms2remove=[];


            if ( event ) {
                event.preventDefault();
            }

            if ( ! attachments.length ) {
                return;
            }

            tt = _.countBy( $form.serializeArray(), function( t ) {
                return t.name;
            });


            _.each( tt, function( count, key ) {

                if ( 2 == count && ! $form.find('input[name="'+key+'"]').prop('indeterminate') ) {
                    terms2add.push(key);
                }
                if ( 2 != count ) {
                    terms2remove.push(key);
                }
            });


            _.each( attachments.models, function( attachment ) {

                var taxonomies = attachment.get('taxonomies');


                _.each( taxonomies, function( terms, taxonomy ) {

                    var changed = false,
                        tdata = {};


                    //to remove
                    _.each( terms, function( term_id, id ) {

                        if ( _.indexOf( terms2remove, 'tax_input['+taxonomy+']['+term_id+']' ) > -1 ) {
                            tdata[ 'attachments['+attachment.id+']['+taxonomy+']['+eml.l10n.terms[term_id]+']' ] = 'remove';
                        }
                    });


                    //to add
                    _.each( terms2add, function( value ) {

                        var matches = value.match(/[^[\]]+(?=])/g);
                            tax = matches[0],
                            t_id = parseInt( matches[1] );


                        if ( tax && t_id && tax == taxonomy && _.indexOf( taxonomies[taxonomy], t_id ) == -1 ) {
                            tdata[ 'attachments['+attachment.id+']['+taxonomy+']['+eml.l10n.terms[t_id]+']' ] = 'add';
                        }
                    });

                    _.extend( data, tdata );

                }); // each taxonomy

            }); // each attachment


            if ( _.isEmpty( data ) ) {
                return;
            }

            spinner.show();
            $( 'input', $form).prop('disabled', true);


            attachments.bulkSave( data ).always( function() {

                spinner.hide();
                $( 'input', $form).prop('disabled', false);

            }).done( function() {

                if ( ! parseInt( eml.l10n.bulk_edit_save_button_off ) )
                {
                    $successMessage.fadeIn( 400 );
                    setTimeout( function() {
                        $successMessage.fadeOut( 400 );
                    }, 4000 );
                }

            }).fail( function() {

                if ( ! parseInt( eml.l10n.bulk_edit_save_button_off ) )
                {
                    $errorMessage.fadeIn( 400 );
                    setTimeout( function() {
                        $errorMessage.fadeOut( 400 );
                    }, 4000 );
                }

            });

            // Clean queries' cache regardless of all or some might be changed
            media.model.Query.cleanQueries();
        },

        preventDefault: function( event ) {
            event.preventDefault();
        }
    });


})( jQuery, _ );
