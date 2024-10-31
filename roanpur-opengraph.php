<?php
/**
 * Roanpur OpenGraph
 *
 * Add OpenGraph header to support better Facebook and Google Plus sharing.
 *
 * Copyright (C) 2016 Benjamin Kahlau
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Benjamin Kahlau <benjamin.kahlau@roanapur.de>
 * @copyright   2016 Benjamin Kahlau
 * @license     GPL-3.0
 * @link		https://github.com/Terenc3/roanpur-opengraph
 * @package     RoanpurOpenGraph
 * @version		1.0.0
 *
 * @wordpress-plugin
 * Plugin Name: Roanpur OpenGraph
 * Plugin URI: 	https://github.com/Terenc3/roanpur-opengraph
 * Description: Add OpenGraph header to support better Facebook and Google Plus sharing.
 * Version: 	1.0.0
 * Author: 		Benjamin Kahlau
 * Author URI: 	http://roanapur.de
 * Text Domain: roanpur-opengraph
 * Domain Path: /languages/
 * License:     GPL-3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 */
defined( 'ABSPATH' ) or die( 'No direct access!' );

/**
 * Add OpenGraph header to support better Facebook and Google Plus sharing.
 */
class RoanpurOpenGraph {
	static function opengraph_tags() {
		self::debug( "<!-- Roanpur OpenGraph -->\n" );
		self::tag( 'site_name', self::site_name() );
		self::tag( 'locale', self::locale() );
		self::tag( 'title', self::title() );
		self::tag( 'type', self::type() );
		self::tag( 'url', self::url() );
		self::tag( 'description', self::description() );
		self::images();
		self::debug( "<!-- %Roanpur OpenGraph end -->\n" );
	}
	private static function tag( $name, $content ) {
		echo sprintf( '<meta property="og:%1$s" name="og:%1$s" content="%2$s" />' . "\n", $name, $content  );
	}

	private static function site_name() {
		return get_bloginfo('name');
	}

	private static function locale() {
		return get_locale();
	}

	private static function title() {
		if (  is_singular() ) {
			return get_the_title( get_queried_object_id() );
		}
		if ( is_author() ) {
			$author = get_queried_object();
			return $author->display_name;
		}
		if ( is_category() && single_cat_title( '', false ) ) {
			return single_cat_title( '', false );
		}
		if ( is_tag() && single_tag_title( '', false ) ) {
			return single_tag_title( '', false );
		}
		if ( is_archive() && get_post_format()) {
			return get_post_format_string( get_post_format() );
		}
		if ( is_archive() && get_the_archive_title() ) {
			return get_the_archive_title();
		}
	}

	private static function type() {
		if ( is_singular( array('post', 'page') ) ) {
			return 'article';
		}
		if ( is_author() ) {
			return 'profile';
		}
		return 'webseite';
		
	}

	private static function url() {
		global $wp;

		if ( is_singular() ) {
			return get_permalink();
		}
		if ( is_author() ) {
			return get_author_posts_url( get_queried_object_id() );
		}
		return home_url( add_query_arg( array(), $wp->request) );
	}

	private static function description() {
		if ( is_singular() ) {
		  $post = get_queried_object();
		  if ( !empty($post->post_excerpt ) ) {
			  $description = $post->post_excerpt;
		  } else {
			  $description = $post->post_content;
		  }
		} else if ( is_author() ) {
			$description = get_user_meta( get_queried_object_id(), 'description', true );
		} else if ( is_category() && category_description() ) {
			$description = category_description();
		} else if ( is_tag() && tag_description() ) {
			$description = tag_description();
		} else if ( is_archive() && get_the_archive_description() ) {
			$description = get_the_archive_description();
		} else {
			$description = get_bloginfo( 'description' );
		}
		$description = strip_tags( strip_shortcodes ( $description ) );

		$excerpt_length = apply_filters( 'excerpt_length', 55 );
		return wp_trim_words( $description, $excerpt_length, ' [...]' );
	}

	private static function images() {
		foreach( get_attached_media('image', get_queried_object_id() ) as $image) {
			self::tag( 'image', wp_get_attachment_image_src ( $image->ID, 'medium')[0] );
		}
	}

	/**
	 * Called by plugins_loaded action
	 *
	 * @return void
	 */
	static function setup() {
		load_plugin_textdomain( 'roanpur-opengraph', false, 'roanpur-opengraph' . DIRECTORY_SEPARATOR . 'languages' );
	}

	static function init() {
		add_action( 'wp_head', self::action( 'opengraph_tags' ) );
	}

	/**
	 * Used to initilize plugin hoooks
	 *
	 * @since 1.0.0
	 *
	 * @param string $file File where plugin resists
	 * @return void
	 */
	static function load( $file ) {
		add_action( 'plugins_loaded', self::action( 'setup' ) );

		// register_activation_hook( $file, self::action( 'activate' ) );
		// register_deactivation_hook( $file, self::action( 'deactivate' ) );
		// register_uninstall_hook( $file, self::action( 'uninstall' ) );

		add_action( 'init', self::action( 'init' ) );
		// add_action( 'admin_init', self::action( 'admin_init' ) );
	}

	/** 
	 * Returns static method call for current class
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $method Method to call
	 * @return string
	 */
	private static function action( $method ) {
		return array( __CLASS__, $method );
	}

	/** 
	 * Produces message if WP_DEBUG
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $string Debug string
	 * @param boolean $echo Echo or retrun $string
	 * @return string|void
	 */
	private static function debug ( $string, $echo = true ) {
		if ( WP_DEBUG ) {
			if ( $echo ) {
				echo $string;
			} else {
				return $string;
			}
		}
	}

	/**
	 *	No operation function
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	static function noop() {}

	/**
	 *	Translate header
	 *
	 * @since 1.0.0
	 * @access private
	 * @internal
	 *
	 * @return void
	 */
	private static function _dummy_header_translation() {
		__( 'Roanpur OpenGraph', 'roanpur-opengraph' );
		__( 'Add OpenGraph header to support better Facebook and Google Plus sharing.', 'roanpur-opengraph' );
	}

}
RoanpurOpenGraph::load(__FILE__);
?>
