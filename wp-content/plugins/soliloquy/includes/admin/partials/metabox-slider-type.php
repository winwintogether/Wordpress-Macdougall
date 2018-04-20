<div id="soliloquy-uploader">
				
	<div id="soliloquy-slider-type-tabs">
					
		<a data-soliloquy-tab class="soliloquy-type-tab soliloquy-icon-soliloquy <?php echo ( ( $data['instance']->get_config( 'type', $data['instance']->get_config_default( 'type' ) ) === 'default' ) ? ' soliloquy-tab-nav-active' : '' ); ?>" href="#" data-tab-id="soliloquy-native">
			<input id="soliloquy-type-default" type="radio" name="_soliloquy[type]" value="default" <?php checked( $data['instance']->get_config( 'type', $data['instance']->get_config_default( 'type' ) ), 'default' ); ?> /> 
		
			<?php esc_html_e( 'Native Slider','soliloquy'); ?></a>
		<a data-soliloquy-tab class="soliloquy-type-tab <?php echo ( ( $data['instance']->get_config( 'type', $data['instance']->get_config_default( 'type' ) ) != 'default' ) ? ' soliloquy-tab-nav-active' : '' ); ?>" href="#" data-tab-id="soliloquy-external"><?php esc_html_e('External Slider','soliloquy'); ?></a>
					
	</div>
	
	<div class="soliloquy-tab-container">
						
		<div id="soliloquy-native" class="soliloquy-tab <?php echo ( ( $data['instance']->get_config( 'type', $data['instance']->get_config_default( 'type' ) ) === 'default' ) ? 'soliloquy-tab-active' : '' ); ?>">
							
		    <!-- Errors -->
	        <div id="soliloquy-upload-error"></div>
	
	        <!-- WP Media Upload Form -->
	        <?php media_upload_form(); ?>
	        <script type="text/javascript">
	            var post_id = <?php echo $data['post']->ID; ?>, shortform = 3;
	        </script>
	        <input type="hidden" name="post_id" id="post_id" value="<?php echo $data['post']->ID; ?>" />

		</div>	
						
		<div id="soliloquy-external" class="soliloquy-tab <?php echo ( ( $data['instance']->get_config( 'type', $data['instance']->get_config_default( 'type' ) ) != 'default' ) ? 'soliloquy-tab-active' : '' ); ?>">	
				<?php if ( count( $data['types'] ) > 1 ): ?>

			<!--<span class="spinner soliloquy-spinner"></span>	-->			
			<h2 class="soliloquy-type-label"><span><?php esc_html_e( 'Select Your Slider Type', 'soliloquy' ); ?></span></h2>
				
			   <ul id="soliloquy-types-nav" class="soliloquy-clear">
					           		            
			   <?php $i = 0; foreach ( (array)  $data['types']  as $id => $title ) : 
					// Don't output the default type as an option here
					if ( 'default' == $id ) {
						continue;
					}  ?>
			
<li id="soliloquy-type-<?php echo sanitize_html_class( $id ); ?>"<?php echo ( ( $data['instance']->get_config( 'type', $data['instance']->get_config_default( 'type' ) ) == $id ) ? ' class="soliloquy-active"' : '' ); ?>>
					
					<label for="soliloquy-type-<?php echo $id; ?>">
						<input id="soliloquy-type-<?php echo sanitize_html_class( $id ); ?>" type="radio" name="_soliloquy[type]" value="<?php echo $id; ?>" <?php checked( $data['instance']->get_config( 'type', $data['instance']->get_config_default( 'type' ) ), $id ); ?> />
						<div class="icon"></div>
						<div class="title"><?php echo $title; ?></div>
					</label>
				
				</li>
					           
				<?php $i++; endforeach; ?>
					        
		        </ul>	
			<?php else: ?>
				
				<h2 class="soliloquy-type-label"><span><?php esc_html_e( 'No External Slider Types Available.', 'soliloquy' ); ?></span></h2>
			
			
			<?php endif; ?>  				
		</div>		
					
	</div>
				
</div>	