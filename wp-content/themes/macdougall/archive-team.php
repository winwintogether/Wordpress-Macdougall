<?php

/**
 * Projects Archive Template
 **/


//* Add custom body class to the head
add_filter( 'body_class', 'custom_body_class' );
function custom_body_class( $classes ) {

	$classes[] = 'team-archive-template';

	return $classes;

}

//* Force full width content
add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );


//* Add Page Hader
add_action( 'genesis_after_header', 'archive_projects_header', 11 );

function archive_projects_header() {

	$image      = get_field( 'projects_archive_banner', 'option' );
	$size       = 'banner';
	$banner_img = wp_get_attachment_image( $image, $size );

	if ( empty( $banner_img ) ) {
		$banner_img = '<img src="' . get_stylesheet_directory_uri() . '/images/MacDougall-DefaultBanner.jpg" alt="MacDougall - Kithcen">';
	}

	echo '<div class="banner">' . $banner_img . '<div class="overlay"></div></div>';
}

//* Remove genesis detault content loop
remove_action( 'genesis_loop', 'genesis_do_loop' );

// Add custom content loop
add_action( 'genesis_loop', 'team_members_loop' );

function team_members_loop() {
	$args = array(
		'post_type' => 'team',
		'posts_per_page'	=> -1,
		'meta_key'			=> 'team-member-last-name',
		'orderby'			=> 'meta_value',
		'order'				=> 'ASC',
	);

	$the_query = new WP_Query( $args );
	if ( $the_query->have_posts() ) {
		$i = - 1;

		while ( $the_query->have_posts() ) {
			$i ++;
			$the_query->the_post();
			$extra_class = 'one-third';
			$extra_class .= 0 == $i % 3 ? ' first' : '';

			// get team member info
			$team_member_id         = get_the_ID();
			$team_member_permalink  = get_permalink( $team_member_id );
			$team_member_first_name = get_field( 'team-member-first-name', $team_member_id );
			$team_member_last_name  = get_field( 'team-member-last-name', $team_member_id );
			$team_member_full_name  = $team_member_first_name . ' ' . $team_member_last_name;
			$team_member_position   = get_field( 'team-member-position', $team_member_id );
			$team_member_headshot   = get_field( 'team-member-headshot', $team_member_id );
			$team_member_biography  = get_the_content();


			echo '<div class="team_member-item ' . $extra_class . '">';

			echo '<img class="team_member-image" src="' . $team_member_headshot . '"/>';
			echo '<a href="' . $team_member_permalink . '" class="team_member-overlay">';
			echo '<div class="team_member-center">';
			echo '<h2 class="team_member-name">' . $team_member_full_name . '</h2>';
			echo '<p class="team_member-position">' . $team_member_position . '</p>';
			echo '</div>';
			echo '</a>'; /* end of team_member-overlay*/

			echo '<div class="team_member__popup-overlay"></div>';
			echo '<div class="team_member__popup">';
			echo '<div class="team_member__popup-inner">';
			echo '<div class="team_member__popup-close"></div>';
			echo '<div class="team_member__popup-content">';
			echo '<div class="team_member__popup-image">';
			echo '<div class="team_member__popup-image-back" style="background-image: url(' . $team_member_headshot . ');"></div>';
			echo '</div>';
			echo '<div class="team_member__popup-description">';
			echo '<h3 class="team_member__popup-description-name">' . $team_member_full_name . '</h3>';
			echo '<p class="team_member__popup-description-position">' . $team_member_position . '</p>';
			echo '<div class="team_member__popup-description-biography">' . $team_member_biography . '</div>';
			echo '</div>';
			echo '</div>';     /* end of team_member__popup-content*/
			echo '</div>';     /* end of team_member__popup-inner */
			echo '</div>';     /* end of team_member__popup */

			echo '</div>';     /* end of team_member-item */

		}
	}
	wp_reset_query();
}

genesis();


