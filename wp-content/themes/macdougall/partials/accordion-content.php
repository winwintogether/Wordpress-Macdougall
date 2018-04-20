<?php
	
	
	
			echo '<div class="accordion-content">';
			echo '<div class="accordion-item-title">';
			if(get_sub_field('item_title_description')) {
			echo '<div class="accordion-float-left"><p class="accordion-item-title-description" >' . get_sub_field('item_title_description') . '</p>';
			}
			echo '<span class="accordion-item-title-wrap">' . get_sub_field('item_title') . '</span>';
			if(get_sub_field('item_title_description')) {
			echo '</div>';
			}
			echo '<span class="accordion-button-icon fa fa-plus"></span></div>';
			
			echo '<div class="accordion-item-text"><div class="accordion-item-text-wrap"><span>' . get_sub_field('item_text') . '</span></div></div>';
			echo '</div>';
	
				
?>			