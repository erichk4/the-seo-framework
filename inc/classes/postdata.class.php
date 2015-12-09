<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class AutoDescription_PostData
 *
 * Pulls data from posts/pages
 * Returns strings/arrays
 *
 * @since 2.1.6
 */
class AutoDescription_PostData extends AutoDescription_Detect {

	/**
	 * StopWords array
	 *
	 * Holds words which hold low SEO value to be stripped or warned.
	 *
	 * Uses localisation
	 *
	 * @since ???
	 * @todo
	 */
	protected $stopwords = array();

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get the excerpt of the post
	 *
	 * @since 1.0.0
	 *
	 * @param string $excerpt the Excerpt
	 * @param int $the_id The Post ID.
	 * @param int $tt_id The Taxonomy Term ID
	 *
	 * @return string The Excerpt
	 */
	public function get_excerpt_by_id( $excerpt = '', $the_id = '', $tt_id = '' ) {

		/**
		 * Use the 2nd parameter.
		 *
		 * @since 2.2.8
		 *
		 * Now casts to array
		 * @since 2.3.3
		 */
		if ( !empty( $the_id ) ) {
			$post = get_post( $the_id, ARRAY_A );
		} else {
			global $post_id;

			$post = get_post( $post_id, ARRAY_A );
		}

		/**
		 * Match the descriptions in admin as on the front end.
		 *
		 * @since 2.3.3
		 */
		if ( !empty( $tt_id ) ) {

			$args = array(
				'posts_per_page'	=> 1,
				'offset'			=> 0,
				'category'			=> $tt_id,
				'category_name'		=> '',
				'post_type'			=> 'post',
				'post_status'		=> 'publish',
			);

			$post = get_posts( $args );
		}

		/**
		 * Get most recent post for blog page.
		 *
		 * @since 2.3.4
		 */
		if ( $the_id == get_option( 'page_for_posts' ) && !is_front_page() ) {
			$args = array(
				'posts_per_page'	=> 1,
				'offset'			=> 0,
				'category'			=> '',
				'category_name'		=> '',
				'orderby'			=> 'date',
				'order'				=> 'DESC',
				'post_type'			=> 'post',
				'post_status'		=> 'publish',
			);

			$post = get_posts( $args );
		}

		/**
		 * Cast object to array.
		 *
		 * @since 2.3.3
		 */
		if ( isset( $post[0] ) && is_object( $post[0] ) ) {
			$object = $post[0];
			$post = (array) $object;
		}

		//* Stop getting something that doesn't exists. E.g. 404
		if ( is_array( $post ) && !isset( $post['post_content'] ) || is_object( $post ) || ( isset( $post['ID'] ) && 0 == $post['ID'] ) )
			return '';

		/**
		 * No need to run esc_attr after wp_strip_all_tags
		 *
		 * @since 2.2.8
		 */
		if ( empty( $excerpt ) ) {
			$excerpt = wp_strip_all_tags( strip_shortcodes( $post['post_content'] ) );
		} else {
			$excerpt = esc_attr( $excerpt );
		}

		$excerpt = str_replace( array( "\r\n", "\r", "\n" ), "\n", $excerpt );

		$lines = explode( "\n", $excerpt );
		$new_lines = array();

		//* Remove line breaks
		foreach ( $lines as $i => $line ) {
			if ( ! empty( $line ) )
				$new_lines[] = trim( $line ) . ' ';
		}

		$output = implode( $new_lines );

		return (string) $output;
	}

}