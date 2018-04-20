;(function ($, window, document ) {
	
	/**
	* Model: A Video
	* Collection: A number of Models
	*/
	
	/**
	* View: Video Error
	*/
	wp.media.view.SoliloquyHTMLError = wp.Backbone.View.extend( {
	
		// The outer tag and class name to use. The item is wrapped in this
	    tagName   : 'div',
	    className : 'soliloquy-error soliloquy-html-error',
	
	    render: function() {
	
	    	// Load the template to render
	    	this.template = wp.media.template( 'soliloquy-html-error' );
	
	    	// Define the HTML for the template
	       	this.$el.html( this.template( this.model ) );
	
	       	// Return the template
	        return this;
	
	    }
	
	} );
	
	/**
	* Model: Video Item
	*/
	wp.media.model.SoliloquyHTML = Backbone.Model.extend( {
	
		/**
		* Define defaults
		*/
		defaults: {
			title: 	'',
			code: 	'', // HTML Code
		}
	
	} );
	
	/**
	* View: Videos Item
	*/
	wp.media.view.SoliloquyHTMLItem = wp.Backbone.View.extend( {
	
		// The outer tag and class name to use. The item is wrapped in this
	    tagName   : 'li',
	    className : 'attachment soliloquy-html-attachment',
	
	    /**
	    * Events
	    */
	    events: {
		    
	    	// Update Model on input change
	    	'keyup input': 'updateItem',
	    	'keyup textarea': 'updateItem',
	    	'blur .Codemirror': 'updateCode',
	    	
	    	// Delete Model on view deletion
			'click .soliloquy-delete-html-slide': 'deleteItem',
			'click .soliloquy-item-collapse': 		'collapse',
	
	  	},
	
	  	/**
	  	* Initialize
	  	*/
	  	initialize: function() {
	  		
	  		this.model.view = this;
	
	  	},
	  	collapse: function( event ){
	
			var $this = $( this.$el );
				$text = $this.find('.soliloquy-item-collapse' );
			
			event.preventDefault();
			if ( $this.find('.soliloquy-item-setting').not('.title').is(':visible') ) {
				$text.text( soliloquy_metabox.expand );
				$this.find('.soliloquy-item-setting').not('.title').fadeOut();
				
			}else{
				
				$text.text( soliloquy_metabox.collapse );
	
				$this.find('.soliloquy-item-setting').not('.title').fadeIn();
				
			}			
	  	},
	  	updateCode: function(event){
		  	
	  	  	//this.model.set( event.target.name, event.target.value, { silent: true } );
	
			//$('#' + id).text(obj.getValue());
	 	
	  	},
	  	updateItem: function( event ) {
		  	
	  		this.model.set( event.target.name, event.target.value, { silent: true } );
	
	    		
	 
	  	},
	  	/**
	  	* Destroys the model and view when deleted
	  	*/
	  	deleteItem: function( event ) {
	
	  		// Trigger the loading event
			this.trigger( 'loading' );
	
			// Delete the view from the modal
			var item = jQuery( event.target );
			
			//Remove the LI Element
			item.parent().parent().parent().remove();
			
			// Delete the model
			// This will automatically remove the model from the collection
			this.model.destroy();
	
			// Trigger the loaded event
			this.trigger( 'loaded loaded:success' );
	
	  	},
	  	updateCode: function( event ){
	  	
	  	},
	  	/**
	  	* Render the HTML output
	  	*/
	    render: function() {
	
	    	// Load the template to render
	    	this.template = wp.media.template( 'soliloquy-html-item' );
	
	    	// Define the HTML for the template
	       	this.$el.html( this.template( this.model.toJSON() ) );
			
			var $textarea = this.$el.find('.soliloquy-html-slide-code');
	
			this.editor = CodeMirror.fromTextArea( $textarea[0] , {
	    			enterMode: 		'keep',
	    			indentUnit: 	4,
	    			electricChars:  false,
	    			lineNumbers: 	true,
	    			lineWrapping: 	true,
	    			matchBrackets: 	true,
	    			mode: 			'php',
	    			smartIndent:    false,
	    			tabMode: 		'shift',
	    			theme:			'ttcn'
				});
	
				this.editor.on('blur', function(obj){
					
		  			this.model.set( 'code', obj.getValue(), { silent: true } );
				
	    			$textarea.text(obj.getValue());
	
	    		});	
	    					
	        return this;
	
	    }
	
	} );
	
	/**
	* View: Bottom Toolbar
	*/
	wp.media.view.Toolbar.SoliloquyHTML = wp.media.view.Toolbar.extend( {
	
		/**
		* Initialize
		*/
		initialize: function() {
			_.defaults( this.options, {
	
			    event: 'soliloquy_html_insert',
			    close: false,
				items: {
					/**
					* Insert Button
					*/
				    soliloquy_html_insert: {
				    	id: 'soliloquy-html-button',
				    	style: 'primary',
				        text: wp.media.view.l10n.insertIntoPost, // Will read "Insert into Gallery", as we modify this in Soliloquy metaboxes.php::media_view_strings()
				        priority: 80,
				        requires: false,
	
				        /**
				        * On Click
				        */
				        click: function() {
				        	// Insert the selected videos into the Gallery
				        	this.controller.state().SoliloquyHTMLInsert();
				        }
				    }
				}
			});
	
			// Initialize the bottom toolbar
			wp.media.view.Toolbar.prototype.initialize.apply( this, arguments );
	
		},
	
	    /**
	    * Refreshes the toolbar items (i.e. button) enable/disabled state, depending on whether any items were selected
	    * Fired by the main controller when an item is selected or deselected
	    */
		refresh: function() {
	
			// Disable the Insert into Gallery Button if nothing was selected
			this.get( 'soliloquy_html_insert' ).model.set( 'disabled', false );
	
			// Apply the refresh
			wp.media.view.Toolbar.prototype.refresh.apply( this, arguments );
	
		}
	
	} );
	
	/**
	* View: Media Content Area
	*/
	wp.media.view.SoliloquyHTML = wp.media.View.extend({
		
		/**
		* Define any events you want to watch for here, for example
		* a search, item selection / deselection etc.
		*/
		events: {
			// Add
			'click .soliloquy-html-add': 'addItem',
	
			// Change Video URL
			'change': 'refreshView',
		},
	
		/**
		* Initialize
		* - Fired when the main UI view is loaded by clicking "Insert Videos"
		*/
		initialize: function() {
	
			// Define a collection, which will store the items (folders and images)
			this.collection = new Backbone.Collection();
	
			// Define some flags
			this.is_loading = false; // Tells us whether we're making an AJAX request or doing something
	
			// Initialise the view, comprising of a sidebar and attachment (items) area
			this.$el.prepend( wp.media.template( 'soliloquy-html-router' ) );
			this.$el.prepend( wp.media.template( 'soliloquy-html-side-bar' ) );
			this.$el.prepend( wp.media.template( 'soliloquy-html-items' ) );
			
			this.addItem();
			
	        setTimeout( function() {
			
				$('.CodeMirror').each(function(i, el){
				    el.CodeMirror.refresh();
				});
				
	        }, 100 );
	        		
			// Define events
			this.on( 'loading',       this.loading, this );
			this.on( 'loaded',        this.loaded, this );
	
		},
	
		/**
		* Displays the loading spinner
		*/
		loading: function() {
	
			// Set a flag so we know we're loading data
			this.is_loading = true;
	
			// Show the spinner
			this.$el.find( '.spinner' ).addClass( 'is-active' );
	
		},
	
		/**
		* Hides the loading spinner
		*/
		loaded: function( response ) {
	
			// Set a flag so we know we're not loading anything now
			this.is_loading = false;
	
			// Hide the spinner
			this.$el.find( '.spinner' ).removeClass( 'is-active' );
	
			// Remove any existing errors
			this.$el.find( 'div.soliloquy-error' ).remove();
	
			// Extract the error message
			if ( typeof response == 'object' ) {
				response = response.responseText;
			}
	
			// Display the error message, if it's provided
			if ( typeof response !== 'undefined' ) {
				this.$el.find( 'div.media-toolbar-secondary' ).append( this.renderError( response ) );
				this.$el.find( 'ul.attachments.soliloquy-html-attachments' ).css( 'margin-top', this.$el.find( 'div.soliloquy-error' ).height() + 20 );
			}
	
			
			// Update toolbar
			this.controller.toolbar.get().refresh();
	
		},
	
		/**
		* Clears items from the media view
		*/
		clearItems: function() {
	
			this.$el.find( 'ul.soliloquy-html-attachments' ).empty();
	
		},
	
		/**
		* Renders an individual error, by calling
		* wp.media.view.SoliloquyHTMLError
		*/
		renderError: function( error ) {
	
			// Define model
			var model = {};
			model.error = error;
	
			// Define view
			var view = new wp.media.view.SoliloquyHTMLError( {
				model: model
			} );
	
			// Return rendered view
			return view.render().el;
	
		},
	
		/**
		* Adds a Video to the media modal view, for the user to complete
		*/
		addItem: function( event ) { 
	
			// Trigger the loading event
			this.trigger( 'loading' );
	
			// Create a new SoliloquyVideo model
			model = new wp.media.model.SoliloquyHTML;
	
			// Add the model to the selection
			this.getSelection().add( model );
	
			// Load the view, assigning it to the model
			var view = new wp.media.view.SoliloquyHTMLItem( {
				model : model,
				controller: this,
			} );
	
			// Render the view in the modal window
			this.$el.find( 'ul.soliloquy-html-attachments' ).append( view.render().el );
			
			$('.CodeMirror').each(function(i, el){
			    el.CodeMirror.refresh();
			});
					
			// Trigger the loaded event
			this.trigger( 'loaded loaded:success' );
	
		},
	
		/**
		* Iterates through each model in the collection, checking whether
		* the hosted_video flag is true or false.  Depending on this, we then
		* show/hide a field in the view attached to that model
		*/
		refreshView: function( event ) {
	
	
		},
	
		/**
		* Get the selected items
		*/
		getSelection: function() {
	
			return this.controller.state().props;
	
		},
	
		/**
		* Clears all selected items
		*/
		clearSelection: function() {
	
			// Get selection
			this.selection = this.getSelection();
	
			// Remove from UI
			jQuery( 'li.attachment.soliloquy-html-attachment' ).remove();
	
			// Clear the selected models
			this.selection.reset();
	
		}
	
	} );
	
	/**
	* View: Media Frame
	*/
	var soliloquy_html_post_frame = wp.media.view.MediaFrame.Post;
	wp.media.view.MediaFrame.Post = soliloquy_html_post_frame.extend({
		
		/**
		* Init
		*/
		initialize: function() {
	
			soliloquy_html_post_frame.prototype.initialize.apply( this, arguments );
	
			// Add the Video Importer to the modal's left hand menu
			this.states.add( [
	            new wp.media.controller.SoliloquyHTML( {
	                id:         'soliloquy-html',
	                content: 	'soliloquy-html-content',
	                toolbar: 	'soliloquy-html-toolbar',
	                menu:       'default',
	                title:      wp.media.view.l10n.SoliloquyHTMLTitle,
					priority:   200,
					type: 		'link'
	            } )
	        ] );
	
	        // Main UI (where attachments are displayed)
	        this.on( 'content:render:soliloquy-html-content', this.renderSoliloquyHTMLContent, this );
	        
	        // Bottom Toolbar (where the selected items and button are displayed)
	        this.on( 'toolbar:create:soliloquy-html-toolbar', this.createSoliloquyHTMLToolbar, this );
			
		},
	
		/**
		* Main UI
		*/
		renderSoliloquyHTMLContent: function() {
			
	        this.content.set( new wp.media.view.SoliloquyHTML( {
	        	controller: this,
	        	model: this.state().props,
	        	className: 'attachments-browser soliloquy soliloquy-html'
	        } ) );
	
		},
	
		/**
		* Bottom Toolbar
		*/
		createSoliloquyHTMLToolbar: function( toolbar ) {
	
			toolbar.view = new wp.media.view.Toolbar.SoliloquyHTML({
				controller: this
			} );
	
		}
	
	});
	
	/**
	* Controller
	*/
	wp.media.controller.SoliloquyHTML = wp.media.controller.State.extend( {
	
		/**
		* Init
		*/
	    initialize: function( options ) {
	
	        this.props = new Backbone.Collection();
	
	    },
	
		/**
		* Called when the Insert button is clicked
		*/
		SoliloquyHTMLInsert: function() {
	
			// Get selected items
			var frame 		= this.frame.content.get(),
				html	  	= [],
				validated 	= true;
	
			// Get toolbar button
			this.button = this.frame.toolbar.get().get( 'soliloquy_html_insert' );
	
			// Disable button and change label
			this.button.model.set( 'text', wp.media.view.l10n.inserting );
			this.button.model.set( 'disabled', true );
	
			// Tell wp.media we're loading items
			frame.trigger( 'loading' );
	
			// Build an array of items, validating them along the way
			frame.getSelection().each( function( model ) {
	
				// Validate the model to ensure it has the required fields
				if ( model.get( 'title' ) == '' ) {
					// Cancel operation
					validated = false;
				}
				if ( model.get( 'code' ) == '' ) {
					// Cancel operation
					validated = false;
				}
	
				html.push( model.toJSON() ); // toJSON will take the model's keys and build an array for us
			}, this );
	
			// If inputs failed validation, stop
			if ( ! validated ) {
				// Tell wp.media we're finished, but there was an error
				frame.trigger( 'loaded loaded:error', wp.media.view.l10n.SoliloquyHTMLValidationError );
	
				// Revert the button back to its original state
				this.button.model.set( 'text', wp.media.view.l10n.insertIntoPost );
				this.button.model.set( 'disabled', false );
	
				// Exit
				return false;
			}
	
			// Make an AJAX request to import these items into the gallery
			wp.media.ajax( 'soliloquy_insert_slides', {
				context: this,
				data: {
	                nonce:   soliloquy_metabox.insert_nonce,
	                post_id: soliloquy_metabox.id,
					html:  html
				},
				success: function( response ) {
	
					// Set the image grid to the HTML we received
	                jQuery( '#soliloquy-output' ).html( response );
	
	                // Tell wp.media we've finished
					frame.trigger( 'loaded loaded:success' );
					
					SoliloquySlidesUpdate();
	
					// Revert the button back to its original state
					this.button.model.set( 'text', wp.media.view.l10n.insertIntoPost );
					this.button.model.set( 'disabled', false );
					
	                $(document).trigger('insertSlides');
					
					// Reset the selection
					frame.clearSelection();
	
					// Close the modal
					this.frame.close();
				},
				error: function( error_message ) {
					// Revert the button back to its original state
					this.button.model.set( 'text', wp.media.view.l10n.insertIntoPost );
					this.button.model.set( 'disabled', false );
	
					// Tell wp.media we've finished, but there was an error 
					frame.trigger( 'loaded loaded:error', error_message );
				}
			} );
	
		}
	    
	} );

})( jQuery , window, document );