<?php
 /*
    Plugin Name: Mooberry Show Latest Posts
    Plugin URI: https://wordpress.org/plugins/mooberry-show-latest-posts/
    Description: Show latest blog posts on the static front page
    Author: Mooberry Dreams
    Version: 1.1
    Author URI: http://www.mooberrydreams.com/
	Text Domain: mooberry-show-latest-posts
	Domain Path: /languages/
	
	Copyright 2015  Mooberry Dreams  (email : mooberrydreams@mooberrydreams.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    */

	require_once('admin-options.php');
	
	add_action( 'plugins_loaded', 'mbdslp_load_textdomain' );
	function mbdslp_load_textdomain() {
		load_plugin_textdomain( 'mooberry-show-latest-posts', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
	}
	
	add_action( 'init', 'mbdslp_init' );	
	function mbdslp_init() {
		// set default options in case they don't exist
		$options = get_option('mbdslp_options');
		if ($options==null) {
			$options = array();
		}
		$options = mbdslp_options_set_defaults( $options );
		update_option('mbdslp_options', $options);
	}
	
	add_filter('the_content', 'mbdslp_add_latest_posts');
	function mbdslp_add_latest_posts( $content ) {
		global $post;
		// only run if we're on the static front page
		// is_front_page returns true if the front page is the blog
		// so check for it to be a page too
		if ( get_post_type() == 'page' && is_front_page() && is_main_query() && !is_admin() ) {
			$options = get_option('mbdslp_options');
			if ($options['mbdslp_number']>0) {
				$args = array( 'posts_per_page' => $options['mbdslp_number'] );
				$lastposts = get_posts( $args );
				if (count($lastposts)==0) {
					wp_reset_postdata();
					return $content;
				}
				$width = floor(100/$options['mbdslp_number']);
					 $content .= '<!-- Latest News -->			
					<div ID="mbdslp" class="page type-page status-publish hentry">
						<div ID="mbdslp_title" class="title" style="margin-bottom:10px;" >
							<h2 class="entry-title">' . esc_html($options['mbdslp_title']) . '</h2>
						</div>';
						//<div class="entry-content">';
				if ($options['mbdslp_display'] == 'horizontal') {
					$content .= '<table ID="mbdslp_table"><tr style="vertical-align:top">';
					$counter = 0;
				}
				$args = array( 'posts_per_page' => $options['mbdslp_number'] );
				$lastposts = get_posts( $args );
				foreach($lastposts as $post) {		
					setup_postdata($post); 
					if ($options['mbdslp_display'] == 'horizontal') {
						$content .= '<td style="width:' . $width . '%">';
					}
					$content .= '<h3 class="mbdslp_post_title" class="entry-title" style="margin:20px 0 5px 0;">';
					$content .=  get_the_title(); 
					$content .= '</h3><span class="mbdslp_post_text">';
					$content .=   wp_trim_words( esc_attr(strip_shortcodes(strip_tags( stripslashes( get_the_content())))), $num_words = 55, $more = NULL ) ;
					$content .= ' <A class="mbslp_read_more" HREF="' . get_the_permalink() . '">' . esc_html($options['mbdslp_readmore']) . '</a></span>';
					if ($options['mbdslp_display'] == 'horizontal') {
						$content .= '</td>';
						$counter++;
						if ($counter == 3) {
							$content .= '</tr><tr style="vertical-align:top">';
							$counter = 0;
						}	
					}
				}
				if ($options['mbdslp_display'] == 'horizontal') {
					$content .= '</tr></table>';
				}
				//$content .= '</div>';
				$content .= '</div>';
				$content .= '<!-- end of latest news -->';
				wp_reset_postdata();
			}
		}
		return $content;
	}
	
