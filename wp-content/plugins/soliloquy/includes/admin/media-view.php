<?php
/**
 * Media View class.
 *
 * @since 2.5
 *
 * @package Soliloquy
 * @author  Chris Kelley
 */
class Soliloquy_Media_View {

    /**
     * Holds the class object.
     *
    * @since 2.5
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
    * @since 2.5
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
    * @since 2.5
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Base
        $this->base = Soliloquy::get_instance();

        // Modals
        add_filter( 'soliloquy_media_view_strings', array( $this, 'media_view_strings' ) );
        add_action( 'print_media_templates', array( $this, 'print_media_templates' ) );

    }

    /**
    * Adds media view (modal) strings
    *
    * @since 2.5
    *
    * @param    array   $strings    Media View Strings
    * @return   array               Media View Strings
    */
    public function media_view_strings( $strings ) {

        return $strings;

    }

    /**
    * Outputs backbone.js wp.media compatible templates, which are loaded into the modal
    * view
    *
    * @since 2.5
    */
    public function print_media_templates() {

    	// Get the Gallery Post and Config
    	global $post;

    	if ( isset( $post ) ) {
    		$post_id = absint( $post->ID );
    	} else {
    		$post_id = 0;
    	}

    	// Bail if we're not editing an soliloquy Gallery
    	if ( get_post_type( $post_id ) != 'soliloquy' ) {
    		return;
    	}

        // Meta Editor
        // Use: wp.media.template( 'soliloquy-meta-editor' )
        ?>
        <script type="text/html" id="tmpl-soliloquy-meta-editor">

			<div class="edit-media-header">

				<button class="left dashicons"><span class="screen-reader-text"><?php esc_html_e( 'Edit previous media item' ); ?></span></button>

				<button class="right dashicons"><span class="screen-reader-text"><?php esc_html_e( 'Edit next media item' ); ?></span></button>

			</div>

			<div class="media-frame-title">
				<h1><?php esc_html_e( 'Edit Item', 'soliloquy' ); ?></h1>
			</div>
			<div class="media-frame-content">

				<div class="attachment-details save-ready">
					<!-- Left -->

	                <div class="attachment-media-view portrait">

	                    <# if ( data.type  !== 'html' ) { #>

	                    <div class="thumbnail thumbnail-image">

	                        <img class="details-image" src="{{ data.src }}" draggable="false" />

								<# if ( data.type  === 'video' ) { #>

                                <!-- Choose Video Placeholder Image + Remove Video Placeholder Image -->
								<a href="#" class="soliloquy-thumbnail button button-primary" data-field="soliloquy-src" title="Choose Video Placeholder Image"><?php esc_html_e( 'Choose Video Placeholder Image', 'soliloquy' ); ?></a>
                                <a href="#" class="soliloquy-thumbnail-delete button button-secondary" data-field="soliloquy-src" title="Remove Video Placeholder Image"><?php esc_html_e( 'Remove Video Placeholder Image', 'soliloquy' ); ?></a>

								<# } #>

	                    </div>

	                    <# } #>

	                    <# if ( data.type  === 'html' ) { #>

	                    	<div class="soliloquy-code-preview">

		                    	{{ data.code }}

	                    	</div>

	                    <# } #>

	                </div>

	                <!-- Right -->
	                <div class="attachment-info">
	                    <!-- Settings -->
	                    <div class="settings">

	                    	<!-- Attachment ID -->
	                    	<input type="hidden" name="id" value="{{ data.id }}" />

	                    	<input type="hidden" name="type" value="{{ data.type }}" />

							<!-- Status -->
							<div class="soliloquy-meta">

								<!-- Status -->
                                <label class="setting">
                                    <span class="name"><?php esc_html_e( 'Status', 'soliloquy' ); ?></span>
                                    <select id="soliloquy-status" class="soliloquy-status" name="status" size="1" data-soliloquy-meta="status">
                                          <option value="pending" <# if ( data.status == 'pending' ) { #> selected <# } #>><?php esc_html_e( 'Draft', 'soliloquy' ); ?></option>
										  <option value="active" <# if ( data.status == 'active' ) { #> selected <# } #>><?php esc_html_e( 'Published', 'soliloquy' ); ?></option>
                                      </select>
			                        <span class="description"><?php esc_html_e( 'Controls whether this individual slide is Drafted or Published within the slider.', 'soliloquy' ); ?></span>
                                </label>

                            </div>
                            
	                        <!-- Title -->
	                        <div class="soliloquy-meta">

		                        <label class="setting">

		                            <span class="name"><?php esc_html_e( 'Title', 'soliloquy' ); ?></span>

		                            <input type="text" name="title" value="{{ data.title }}" />

			                        <span class="description">
										<?php esc_html_e( 'Enter the title for your slide.', 'soliloquy' ); ?>
			                        </span>

		                        </label>


	                        </div>
						
							<!-- Alt Text -->
	                  	    <div class="soliloquy-meta">

		                        <label class="setting">

		                            <span class="name"><?php esc_html_e( 'Alt Text', 'soliloquy' ); ?></span>

		                            <input type="text" name="alt" value="{{ data.alt }}" />

		                        <span class="description">
									<?php esc_html_e( 'Describes the image for search engines and screen readers. Important for SEO and accessibility.', 'soliloquy' ); ?>
		                        </span>

		                        </label>
	                        </div>

	                        <!-- Caption -->
	                        <# if ( data.type  !== 'html' ) { #>
	                        <div class="soliloquy-meta">
		                        <div class="setting">
		                            <span class="name"><?php esc_html_e( 'Caption', 'soliloquy' ); ?></span>
		                            <?php
	                                wp_editor( '', 'caption', array(
	                                	'media_buttons' => false,
	                                	'wpautop' 		=> false,
	                                	'tinymce' 		=> false,
	                                	'textarea_name' => 'caption',
	                                	'quicktags' => array(
	                                		'buttons' => 'strong,em,link,ul,ol,li,close'
	                                	),
	                                ) );

	                                ?>
			                        <span class="description"><?php esc_html_e( 'Displayed over the slide image. Field accepts any valid HTML.', 'soliloquy' ); ?></span>

		                        </div>

	                        </div>
	                        <# } #>

							<!-- Image Link -->
	                        <# if ( data.type  === 'image' ) { #>
	                        <div class="soliloquy-meta">
		                        <label class="setting">
		                            <span class="name"><?php esc_html_e( 'URL', 'soliloquy' ); ?></span>
		                            <input type="text" name="link" value="{{ data.link }}" />
			                            <span class="buttons">
			                            	<button class="button button-small media-file"><?php esc_html_e( 'Media File', 'soliloquy' ); ?></button>
											<button class="button button-small attachment-page"><?php esc_html_e( 'Attachment Page', 'soliloquy' ); ?></button>
										</span>
			                        <span class="description">
										<?php esc_html_e( 'Enter a hyperlink to link this slide to another page.', 'soliloquy' ); ?>
			                        </span>

								</label>

								<!-- Link in New Window -->
	                            <label class="setting">
	                            	<span class="name"><?php esc_html_e( 'Open URL in New Window?', 'soliloquy' ); ?></span>
									<input type="checkbox" name="linktab" value="1"<# if ( data.linktab == '1' ) { #> checked <# } #> /><span class="check-label"><?php esc_html_e('Opens your image links in a new browser window / tab.', 'soliloquy' ); ?></span>
	                            </label>

								</div>
							<# } #>
							
							<!-- Video Link -->
	                        <# if ( data.type  === 'video' ) { #>
	                        <div class="soliloquy-meta">
		                        <!-- Link -->
		                        <label class="setting">
		                            <span class="name"><?php esc_html_e( 'URL', 'soliloquy' ); ?></span>
		                            <input type="text" name="url" value="{{ data.url }}" />
								</label>
	                        </div>
							<# } #>
							
							<!-- HTML -->
	                        <# if ( data.type  === 'html' ) { #>
	                        <div class="soliloquy-meta code">
		                        <!-- Link -->
			                        <label class="code">
			                            <span class="name"><?php esc_html_e( 'Code', 'soliloquy' ); ?></span>
			                            <textarea class="soliloquy-html-slide-code" name="code">{{ data.code }}</textarea>
									</label>
	                        </div>
							<# } #>
							
							<!-- Addons can populate the UI here -->
							<div class="addons"></div>

	                    </div>
	                    <!-- /.settings -->

	                    <!-- Actions -->
	                    <div class="actions">

	                        <a href="#" class="soliloquy-meta-submit button media-button button-large button-primary media-button-insert" title="<?php esc_attr_e( 'Save Metadata', 'soliloquy' ); ?>">
	                        	<?php esc_html_e( 'Save Metadata', 'soliloquy' ); ?>
	                        </a>

							<!-- Save Spinner -->
	                        <span class="settings-save-status">
		                        <span class="spinner"></span>
		                        <span class="saved"><?php esc_html_e( 'Saved.', 'soliloquy' ); ?></span>
	                        </span>
	                    </div>
	                    <!-- /.actions -->
	                </div>
	            </div>
			</div>
		</script>

        <?php
        // Bulk Image Editor
        // Use: wp.media.template( 'soliloquy-meta-bulk-editor' )
        ?>
        <script type="text/html" id="tmpl-soliloquy-meta-bulk-editor">

			<div class="media-frame-title">
				<h1><?php esc_html_e( 'Bulk Edit', 'soliloquy' ); ?></h1>
			</div>

			<div class="media-frame-content">
				<div class="attachment-details save-ready">
					<!-- Left -->
	                <div class="attachment-media-view portrait">
	                	<ul class="attachments soliloquy-bulk-edit">
	                	</ul>
	                </div>

	                <!-- Right -->
	                <div class="attachment-info">
	                    <!-- Settings -->
	                    <div class="settings">
	                    	<!-- Attachment ID -->

	                        <!-- Title -->
	                        <div class="soliloquy-meta">

		                        <label class="setting">

		                            <span class="name"><?php esc_html_e( 'Alt Text', 'soliloquy' ); ?></span>

		                            <input type="text" name="alt" value="{{ data.alt }}" />
			                        <span class="description">
										<?php esc_html_e( 'Describes the image for search engines and screen readers. Important for SEO and accessibility.', 'soliloquy' ); ?>
			                        </span>

		                        </label>



	                        </div>

	                        <!-- Caption -->
	                        <div class="soliloquy-meta">
		                        <div class="setting">
		                            <span class="name"><?php esc_html_e( 'Caption', 'soliloquy' ); ?></span>
		                            <?php
	                                wp_editor( '', 'caption', array(
	                                	'media_buttons' => false,
	                                	'wpautop' 		=> false,
	                                	'tinymce' 		=> false,
	                                	'textarea_name' => 'caption',
	                                	'quicktags' => array(
	                                		'buttons' => 'strong,em,link,ul,ol,li,close'
	                                	),
	                                ) );

	                                ?>
			                        <span class="description">
										<?php esc_html_e( 'Displayed over the slide image. Field accepts any valid HTML.', 'soliloquy' ); ?>
			                        </span>

		                        </div>

	                        </div>

							<div class="soliloquy-meta">

								<!-- Status -->
                                <label class="setting">
                                    <span class="name"><?php esc_html_e( 'Status', 'soliloquy' ); ?></span>
                                    <select id="soliloquy-status" class="soliloquy-status" name="status" size="1" data-soliloquy-meta="status">
										  <option value="active" <# if ( data.status == 'active' ) { #> selected <# } #>><?php esc_html_e( 'Published', 'soliloquy' ); ?></option>
                                          <option value="pending" <# if ( data.status == 'pending' ) { #> selected <# } #>><?php esc_html_e( 'Draft', 'soliloquy' ); ?></option>
                                      </select>
                                </label>

                            </div>

	                         <# if ( data.type  === 'image' ) { #>
	                        <div class="soliloquy-meta">
		                        <label class="setting">
		                            <span class="name"><?php esc_html_e( 'URL', 'soliloquy' ); ?></span>
		                            <input type="text" name="link" value="{{ data.link }}" />
		                            <# if ( typeof( data.id ) === 'number' ) { #>
			                            <span class="buttons">
			                            	<button class="button button-small media-file"><?php esc_html_e( 'Media File', 'soliloquy' ); ?></button>
											<button class="button button-small attachment-page"><?php esc_html_e( 'Attachment Page', 'soliloquy' ); ?></button>
										</span>
									<# } #>
			                        <span class="description">
										<strong><?php esc_html_e( 'URL', 'soliloquy' ); ?></strong>
										<?php esc_html_e( 'Enter a hyperlink to link this slide to another page.', 'soliloquy' ); ?>
			                        </span>
								</label>



								<!-- Link in New Window -->
	                            <label class="setting">
	                            	<span class="name"><?php esc_html_e( 'Open URL in New Window?', 'soliloquy' ); ?></span>
									<input type="checkbox" name="link_new_window" value="1"<# if ( data.link_new_window == '1' ) { #> checked <# } #> />
									<span class="check-label"><?php esc_html_e('Opens your image links in a new browser window / tab.', 'soliloquy' ); ?></span>
	                            </label>

								</div>
							<# } #>

	                         <# if ( data.type  === 'video' ) { #>

	                        <div class="soliloquy-meta">
		                        <!-- URL -->
		                        <label class="setting">
		                            <span class="name"><?php esc_html_e( 'URL', 'soliloquy' ); ?></span>
		                            <input type="text" name="link" value="{{ data.url }}" />
								</label>
	                        </div>

							<# } #>

	                         <# if ( data.type  === 'html' ) { #>
	                        <div class="soliloquy-meta">
		                        <!-- Link -->
		                        <label class="code">
		                            <span class="name"><?php esc_html_e( 'Code', 'soliloquy' ); ?></span>
		                            <textarea class="soliloquy-html-slide-code" name="code">{{ data.code }}</textarea>
								</label>
	                        </div>
							<# } #>
							<!-- Addons can populate the UI here -->
							<div class="addons"></div>

	                    </div>
	                    <!-- /.settings -->

	                    <!-- Actions -->
	                    <div class="actions">
	                        <a href="#" class="soliloquy-meta-submit button media-button button-large button-primary media-button-insert" title="<?php esc_attr_e( 'Save Metadata', 'soliloquy' ); ?>">
	                        	<?php esc_html_e( 'Save Metadata', 'soliloquy' ); ?>
	                        </a>

							<!-- Save Spinner -->
	                        <span class="settings-save-status">
		                        <span class="spinner"></span>
		                        <span class="saved"><?php esc_html_e( 'Saved.', 'soliloquy' ); ?></span>
	                        </span>
	                    </div>
	                    <!-- /.actions -->
	                </div>

	            </div>
			</div>
		</script>

		<?php
        // Bulk Image Editor Image
        // Use: wp.media.template( 'soliloquy-meta-bulk-editor-image' )
        ?>
        <script type="text/html" id="tmpl-soliloquy-meta-bulk-editor-slides">
        	<div class="attachment-preview">
        		<div class="thumbnail">
        			<div class="centered">
					<# if ( data.type  !== 'html' ) { #>

        				<img src={{ data.src }} />

        			<# } #>
	                    <# if ( data.type  === 'html' ) { #>

	                    	<div class="soliloquy-code-preview">

		                    	<img src="<?php echo plugins_url( 'assets/images/html.png', $this->base->file ); ?>" />

	                    	</div>

	                    <# } #>

        			</div>
        		</div>
        	</div>
        </script>

        <?php
        // Router Bar
        // Use: wp.media.template( 'soliloquy-html-router' )
        ?>
        <script type="text/html" id="tmpl-soliloquy-html-router">
            <div class="media-toolbar">
                <div class="media-toolbar-secondary">
                    <span class="spinner"></span>
                </div>
                <div class="media-toolbar-primary search-form">
                    <button class="soliloquy-html-add button button-primary"><?php esc_html_e( 'Add Another HTML Slide', 'soliloquy' ); ?></button>
                </div>
            </div>
        </script>

        <?php
        // Side Bar
        // Use: wp.media.template( 'soliloquy-html-side-bar' )
        ?>
        <script type="text/html" id="tmpl-soliloquy-html-side-bar">
            <div class="media-sidebar">
                <div class="soliloquy-meta-sidebar">
                    <h3><?php esc_html_e( 'Helpful Tips', 'soliloquy' ); ?></h3>
					<strong><?php esc_html_e( 'Creating HTML Slides', 'soliloquy' ) ?></strong>
                    <p><?php esc_html_e( 'Each HTML slide should have its own unique name (for identification purposes) and code for outputting into the slider. The code will be inserted inside of the slide <code>&lt;li&gt;</code> tag and can be styled with custom CSS as you need.', 'soliloquy' ); ?></p>
                 </div>
            </div>
        </script>
        <?php
        // Error Message
        // Use: wp.media.template( 'soliloquy-html-error' )
        ?>
        <script type="text/html" id="tmpl-soliloquy-html-error">
            <p>
                {{ data.error }}
            </p>
        </script>

        <?php
        // Collection of HTML Slides
        // Use: wp.media.template( 'soliloquy-html-items' )
        // wp.media.template( 'soliloquy-item' ) is used to inject <li> items into this template
        ?>
        <script type="text/html" id="tmpl-soliloquy-html-items">

	        <div class="soliloquy-html-items">

            	<ul class="attachments soliloquy-html-attachments"></ul>

	        </div>

        </script>
        <?php
        // Single HTML
        // Use: wp.media.template( 'soliloquy-html-item' )
        ?>
        <script type="text/html" id="tmpl-soliloquy-html-item">
			<div class="soliloquy-html-item soliloquy-html-slide-holder">
				<a href="#" class="soliloquy-item-collapse"><?php esc_html_e( 'Collapse', 'soliloquy'); ?></a>
                <!-- Title -->
                <div class="soliloquy-item-setting title">
                    <label>
                        <strong><?php esc_html_e( 'HTML Slide Title *', 'soliloquy' ); ?></strong>
                        <input type="text" name="title" />
                    </label>
                </div>
				<div>
					<div class="soliloquy-item-setting">
				<p class="no-margin-bottom">
					<label for="soliloquy-html-slide">
						<span class="code-title"><strong><?php esc_html_e( 'HTML Slide Code' ); ?></strong></span>
						<textarea name="code" class="soliloquy-html-slide-code">&lt;!-- <?php esc_html_e( 'Enter your HTML code here for this slide (you can delete this line).', 'soliloquy' ); ?> --&gt;</textarea>

					</label>

				</p>
				</div>

				<div class="soliloquy-item-footer">

					<a href="#" class="button button-soliloquy-delete soliloquy-delete-html-slide" title="Remove"><?php esc_html_e( 'Delete This Slide','soliloquy' ); ?></a>

				</div>

			</div>
        </script>

        <?php
        // Router Bar
        // Use: wp.media.template( 'soliloquy-video-router' )
        ?>
        <script type="text/html" id="tmpl-soliloquy-video-router">
            <div class="media-toolbar">
                <div class="media-toolbar-secondary">
                    <span class="spinner"></span>
                </div>
                <div class="media-toolbar-primary search-form">
                    <button class="soliloquy-videos-add button button-primary"><?php esc_html_e( 'Add Another Video Slide', 'soliloquy' ); ?></button>
                </div>
            </div>
        </script>

        <?php
        // Side Bar
        // Use: wp.media.template( 'soliloquy-video-side-bar' )
        ?>
        <script type="text/html" id="tmpl-soliloquy-video-side-bar">
            <div class="media-sidebar">
                <div class="soliloquy-meta-sidebar">
                    <h3><?php esc_html_e( 'Helpful Tips', 'soliloquy' ); ?></h3>
                    <strong><?php esc_html_e( 'Creating Video Items', 'soliloquy' ); ?></strong>
                    <p><?php esc_html_e( 'The image for each video is automatically created from the video link you supply. Video links can be from either YouTube, Vimeo, Wistia or locally hosted video files. They <strong>must</strong> follow one of the formats listed below:', 'soliloquy' ) ?></p>

                    <div class="soliloquy-accepted-urls">
                        <span class="title"><strong><?php esc_html_e( 'YouTube URLs', 'soliloquy' ); ?></strong></span><br />
                        <span>https://youtube.com/v/{vidid}</span>
                        <span>https://youtube.com/vi/{vidid}</span>
                        <span>https://youtube.com/?v={vidid}</span>
                        <span>https://youtube.com/?vi={vidid}</span>
                        <span>https://youtube.com/watch?v={vidid}</span>
                        <span>https://youtube.com/watch?vi={vidid}</span>
                        <span>https://youtu.be/{vidid}</span><br />

                        <span class="title"><strong><?php esc_html_e( 'Vimeo URLs', 'soliloquy' ); ?></strong></span><br />
                        <span>https://vimeo.com/{vidid}</span>
                        <span>https://vimeo.com/groups/tvc/videos/{vidid}</span>
                        <span>https://player.vimeo.com/video/{vidid}</span><br />

                        <span class="title"><strong><?php esc_html_e( 'Wistia URLs', 'soliloquy' ); ?></strong></span><br />
                        <span>https://wistia.com/medias/*</span>
                        <span>https://wistia.com/embed/*</span>
                        <span>https://wi.st/medias/*</span>
                        <span>https://wi.st/embed/*</span><br />

                        <span class="title"><strong><?php esc_html_e( 'Local URLs', 'soliloquy' ); ?></strong></span><br />
                        <span><?php bloginfo('url'); ?>/path/to/video.mp4</span>
                        <span><?php bloginfo('url'); ?>/path/to/video.ogv</span>
                        <span><?php bloginfo('url'); ?>/path/to/video.webm</span>
                        <span><?php bloginfo('url'); ?>/path/to/video.3gp</span>
                    </div>
                 </div>
            </div>
        </script>

        <?php
        // Error Message
        // Use: wp.media.template( 'soliloquy-video-error' )
        ?>
        <script type="text/html" id="tmpl-soliloquy-video-error">
            <p>
                {{ data.error }}
            </p>
        </script>

        <?php
        // Collection of Videos
        // Use: wp.media.template( 'soliloquy--videoitems' )
        // wp.media.template( 'soliloquy-item' ) is used to inject <li> items into this template
        ?>
        <script type="text/html" id="tmpl-soliloquy-video-items">
	        <div class="soliloquy-video-items">
				<ul class="attachments soliloquy-videos-attachments"></ul>
	        </div>

        </script>
        <?php
        // Single Video
        // Use: wp.media.template( 'soliloquy-video-item' )
        ?>
        <script type="text/html" id="tmpl-soliloquy-video-item">
            <div class="soliloquy-videos-item soliloquy-video-slide-holder">

				<a href="#" class="soliloquy-item-collapse"><?php esc_html_e( 'Collapse', 'soliloquy'); ?></a>

                <!-- Title -->
                <div class="soliloquy-item-setting title">
                    <label>
                        <strong><?php esc_html_e( 'Title *', 'soliloquy' ); ?></strong>
                        <input type="text" name="title" />
                    </label>
                </div>

                <!-- Video URL -->
                <div class="soliloquy-item-setting">
                    <label>
                        <strong><?php esc_html_e( 'Video URL *', 'soliloquy' ); ?></strong>
                        <div class="soliloquy-input-group">

		                    <div class="soliloquy-grid-10 soliloquy-first">

	                        	<input type="text" name="url" />
		                    </div>
		                    <div class="soliloquy-grid-2 soliloquy-media-button">
	                        	<a href="#" class="button button-soliloquy-secondary soliloquy-insert-video"><?php esc_html_e( 'Upload Media', 'soliloquy' ); ?></a>

							</div>

							<div class="soliloquy-clearfix"></div>

                        </div>
                    </label>
                </div>

                <!-- Image -->
                <div class="soliloquy-item-setting image">
                    <label class="setting">
                        <strong><?php esc_html_e( 'Image URL *', 'soliloquy' ); ?></strong>

                        <div class="soliloquy-input-group">
							<div class="soliloquy-grid-10 soliloquy-first">

	                        <input type="text" name="image" />

							</div>
							<div class="soliloquy-grid-2 soliloquy-media-button">

	                       		<a href="#" class="button button-soliloquy-secondary soliloquy-insert-placeholder"><?php esc_html_e( 'Upload Media', 'soliloquy' ); ?></a>

							</div>

							<div class="soliloquy-clearfix"></div>
                        </div>
                      <p class="description"><?php esc_html_e( 'Required if specifying a local video URL.', 'soliloquy' ); ?></p>

                    </label>
                </div>

                <!-- Caption -->
                <div class="soliloquy-item-setting">
	                                    <label>

				    <span class="name"><strong><?php esc_html_e( 'Caption', 'soliloquy' ); ?></strong></span>
					<textarea name="caption" rows="10"></textarea>
				    <?php
//				    wp_editor( '', 'caption-video', array(
//				    	'media_buttons' => false,
//				    	'wpautop' 		=> false,
//				    	'tinymce' 		=> false,
//				    	'textarea_name' => 'caption',
//				    	'quicktags' => array(
//				    		'buttons' => 'strong,em,link,ul,ol,li,close'
//				    	),
//				    ) );
				    ?>
				</div>

                <!-- Alt Text -->
                <div class="soliloquy-item-setting">
                    <label>
                        <strong><?php esc_html_e( 'Alt Text', 'soliloquy' ); ?></strong>
                        <input type="text" name="alt" />
                    </label>
                </div>

				<!-- Item Footer -->
				<div class="soliloquy-item-footer">

					<a href="#" class="button button-soliloquy-delete soliloquy-delete-video-slide" title="Remove"><?php esc_html_e( 'Delete This Slide','soliloquy' ); ?></a>

				</div>

            </div>
        </script>
        <?php
		do_action( 'soliloquy_print_templates' );
    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.5
     *
     * @return object The Soliloquy_Media_View object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Soliloquy_Media_View ) ) {
            self::$instance = new Soliloquy_Media_View();
        }

        return self::$instance;

    }

}

// Load the media class.
$soliloquy_meida_view = Soliloquy_Media_View::get_instance();