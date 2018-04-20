<?php
	
				add_image_size( 'feature', 600, 400, true );
				
				$image1 = get_sub_field('item1_image');
				$image2 = get_sub_field('item2_image');
				$size = 'feature'; 
				
				// begin item loop

				echo '<div class="featured-content">'; 
				
				echo '<div class="one-half first">';
				
				
				echo '<a href="'.get_sub_field('item1_link').'">';
				echo '<div class="featured-content-item">';
			
				
				echo wp_get_attachment_image( $image1, $size ); 
				
				echo '<div class="item-text"><div class="text-wrap">';
				
				echo '<h4>'.get_sub_field('item1_title').'</h4>';
				
				//the_sub_field('item1_text');
				
				//echo '<p class="learn-more">Learn more</p>';
		
				echo '</div></div>'; // end of item-text & text-wrap
			
			
				echo '</div>';  // end of featured-content-item
				echo '</a>';
				
				echo '</div>';  // end of column
				
        		// item divider
				
				echo '<div class="one-half">';
				
				echo '<a href="'.get_sub_field('item2_link').'">';
				
				echo '<div class="featured-content-item">';
			
				echo wp_get_attachment_image( $image2, $size ); 
				
				echo '<div class="item-text"><div class="text-wrap">';
				
				echo '<h4>'.get_sub_field('item2_title').'</h4>';
				
				//the_sub_field('item2_text');
				
				//echo '<p class="learn-more">Learn more</p>';
		
				echo '</div></div>';  // end of item-text & text-wrap
			
			
				echo '</div>';  // end of featured-content-item
				echo '</a>';
				
				echo '</div>'; // end of column 2
				
				echo '</div>';  // end of featured content section
				
?>			