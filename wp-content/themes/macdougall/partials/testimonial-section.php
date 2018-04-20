<?php



				echo '<div class="testimonial-section>';

				//the_sub_field('item_title');
				echo '<h3>What Our Clients Say</h3>';
			    echo '<p>'.get_sub_field('testimonial_section_quote').'</p>';
				echo '<h6>'.get_sub_field('testimonial_section_name').'</h6>';

				echo '</div>';


?>
