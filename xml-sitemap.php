<?php
/**
 * Plugin Name: XML Sitemap & Google News
 * Plugin URI: https://status301.net/wordpress-plugins/xml-sitemap-feed/
 * Description: Feed the hungry spiders in compliance with the XML Sitemap and Google News protocols. Happy with the results? Please leave me a <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=XML%20Sitemap%20Feed">tip</a></strong> for continued development and support. Thanks :)
 * Version: 5.4.9
 * Text Domain: xml-sitemap-feed
 * Requires at least: 4.4
 * Requires PHP: 5.6
 * Author: RavanH
 * Author URI: https://status301.net/
 *
 * @package XML Sitemap & Google News
 */

define( 'XMLSF_VERSION', '5.4.9' );

/**
 * Copyright 2024 RavanH
 * https://status301.net/
 * mailto: ravanhagen@gmail.com

 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * --------------------
 *  AVAILABLE HOOKS
 * --------------------
 *
 * Documented on https://premium.status301.com/knowledge-base/xml-sitemap-google-news/action-and-filter-hooks/
 *
 * ---------------------
 *  AVAILABLE FUNCTIONS
 * ---------------------
 *
 * Conditional tags https://premium.status301.com/knowledge-base/xml-sitemap-google-news/conditional-tags/
 *
 *  Feel free to request, suggest or submit more :)
 */

defined( 'WPINC' ) || die;

define( 'XMLSF_DIR', __DIR__ );

define( 'XMLSF_BASENAME', plugin_basename( __FILE__ ) );

// Main plugin init.
add_action( 'init', 'xmlsf_init', 9 );

register_activation_hook( __FILE__, 'xmlsf_activate' );

register_deactivation_hook( __FILE__, 'xmlsf_deactivate' );

/**
 * Plugin initialization
 *
 * @since 1.0
 * @return void
 */
function xmlsf_init() {
	// Prepare hooks for debugging.
	WP_DEBUG && require_once XMLSF_DIR . '/inc/functions-debugging.php';

	// Add robots.txt filter.
	add_filter( 'robots_txt', 'xmlsf_robots_txt', 0 );

	// If XML Sitemaps Manager is installed, remove its init and admin_init hooks.
	if ( function_exists( 'xmlsm_init' ) ) {
		remove_action( 'init', 'xmlsm_init', 9 );
		remove_action( 'admin_init', 'xmlsm_admin_init' );
	}

	// Upgrade/install, maybe...
	$db_version = get_option( 'xmlsf_version', 0 );
	if ( ! version_compare( XMLSF_VERSION, $db_version, '=' ) ) {
		require_once XMLSF_DIR . '/upgrade.php';
	}

	// If sitemaps enabled, do our thing. Otherwise disable core.
	if ( xmlsf_sitemaps_enabled() ) {
		// Shared functions.
		require_once XMLSF_DIR . '/inc/functions.php';

		$sitemaps = (array) get_option( 'xmlsf_sitemaps', array() );

		// Google News sitemap?
		if ( ! empty( $sitemaps['sitemap-news'] ) ) {
			global $xmlsf_sitemap_news;
			require XMLSF_DIR . '/inc/class-xmlsf-sitemap-news.php';
			$xmlsf_sitemap_news = new XMLSF_Sitemap_News();
		}

		// XML Sitemap?
		if ( ! empty( $sitemaps['sitemap'] ) ) {
			global $xmlsf_sitemap;

			require XMLSF_DIR . '/inc/class-xmlsf-sitemap.php';
			require XMLSF_DIR . '/inc/functions-sitemap.php';

			if ( xmlsf_uses_core_server() ) {
				// Extend core sitemap.
				require XMLSF_DIR . '/inc/class-xmlsf-sitemap-core.php';
				$xmlsf_sitemap = new XMLSF_Sitemap_Core();
			} else {
				// Replace core sitemap.
				remove_action( 'init', 'wp_sitemaps_get_server' );

				require XMLSF_DIR . '/inc/class-xmlsf-sitemap-plugin.php';
				$xmlsf_sitemap = new XMLSF_Sitemap_Plugin();
			}
		} else {
			// Disable core sitemap.
			add_filter( 'wp_sitemaps_enabled', '__return_false' );
		}

		// Include and instantiate main class.
		xmlsf();
	} else {
		add_filter( 'wp_sitemaps_enabled', '__return_false' );
	}

	if ( is_admin() ) {
		xmlsf_admin();
	}
}

/**
 * Plugin activation
 *
 * @since 5.0
 * @return void
 */
function xmlsf_activate() {
	// Flush rewrite rules on next init.
	delete_option( 'rewrite_rules' );
}

/**
 * Plugin de-activation
 *
 * @since 5.0
 * @return void
 */
function xmlsf_deactivate() {
	// Clear all cache metadata.
	if ( ! function_exists( 'xmlsf_clear_metacache' ) ) {
		// Needed for wp-cli.
		include_once XMLSF_DIR . '/inc/functions-sitemap.php';
	}
	xmlsf_clear_metacache();

	// Remove old rules.
	// TODO but how? remove_rewrite_rule() does not exist yet :/
	// Re-add core rules.
	function_exists( 'wp_sitemaps_get_server' ) && wp_sitemaps_get_server();
	// Then flush.
	flush_rewrite_rules( false );
}

/**
 * Get instantiated sitemap class
 *
 * @since 5.0
 *
 * @global XMLSitemapFeed $xmlsf
 * @return XMLSitemapFeed object by reference
 */
function &xmlsf() {
	global $xmlsf;

	if ( ! isset( $xmlsf ) ) {
		if ( ! class_exists( 'XMLSitemapFeed' ) ) {
			require_once XMLSF_DIR . '/inc/class-xmlsitemapfeed.php';
		}

		$xmlsf = new XMLSitemapFeed();
	}

	return $xmlsf;
}

/**
 * Get instantiated sitemap admin class
 *
 * @since 5.4
 *
 * @global XMLSF_Admin $xmlsf_admin
 * @return XMLSF_Admin object by reference
 */
function &xmlsf_admin() {
	global $xmlsf_admin;

	if ( ! isset( $xmlsf_admin ) ) {
		if ( ! class_exists( 'XMLSF_Admin' ) ) {
			require XMLSF_DIR . '/inc/class-xmlsf-admin.php';
		}

		$xmlsf_admin = new XMLSF_Admin();
	}

	return $xmlsf_admin;
}

/**
 * Filter robots.txt rules
 *
 * @param string $output Default robots.txt content.
 *
 * @return string
 */
function xmlsf_robots_txt( $output ) {

	// CUSTOM ROBOTS.
	$robots_custom = get_option( 'xmlsf_robots' );
	$output       .= $robots_custom ? $robots_custom . PHP_EOL : '';

	// SITEMAPS.

	$output .= PHP_EOL . '# XML Sitemap & Google News version ' . XMLSF_VERSION . ' - https://status301.net/wordpress-plugins/xml-sitemap-feed/' . PHP_EOL;
	if ( 1 !== (int) get_option( 'blog_public' ) ) {
		$output .= '# XML Sitemaps are disabled because of this site\'s privacy settings.' . PHP_EOL;
	} elseif ( ! xmlsf_sitemaps_enabled() ) {
		$output .= '# No XML Sitemaps are enabled.' . PHP_EOL;
	} else {
		xmlsf_uses_core_server() || xmlsf_sitemaps_enabled( 'sitemap' ) && $output .= 'Sitemap: ' . xmlsf_sitemap_url() . PHP_EOL;
		xmlsf_sitemaps_enabled( 'news' ) && $output .= 'Sitemap: ' . xmlsf_sitemap_url( 'news' );
	}

	return $output;
}

/**
 * Are any sitemaps enabled?
 *
 * @since 5.4
 *
 * @param string $which Which sitemap to check for. Default any sitemap.
 *
 * @return false|array
 */
function xmlsf_sitemaps_enabled( $which = 'any' ) {
	static $enabled;

	if ( null === $enabled ) {
		$sitemaps = (array) get_option( 'xmlsf_sitemaps', array() );

		switch ( true ) {
			case isset( $sitemaps['sitemap'] ) && isset( $sitemaps['sitemap-news'] ):
				$enabled = array( 'sitemap', 'news' );
				break;

			case isset( $sitemaps['sitemap'] ):
				$enabled = array( 'sitemap' );
				break;

			case isset( $sitemaps['sitemap-news'] ):
				$enabled = array( 'news' );
				break;

			default:
			case 1 !== (int) get_option( 'blog_public' ):
				$enabled = array();
		}
	}

	if ( 'sitemap' === $which ) {
		// Looking for regular sitemap.
		return apply_filters( 'xmlsf_sitemaps_enabled', in_array( 'sitemap', $enabled, true ), 'sitemap' );
	}
	if ( 'news' === $which ) {
		// Looking for news sitemap.
		return apply_filters( 'xmlsf_sitemaps_enabled', in_array( 'news', $enabled, true ), 'news' );
	}
	// Looking for any sitemap.
	return apply_filters( 'xmlsf_sitemaps_enabled', ! empty( $enabled ), $which );
}

/**
 * CONDITIONAL TAGS
 */

if ( ! function_exists( 'is_sitemap' ) ) {
	/**
	 * Is the query for a sitemap?
	 *
	 * @since 4.8
	 * @return bool
	 */
	function is_sitemap() {
		if ( function_exists( 'wp_sitemaps_loaded' ) ) {
			global $wp_query;
			if ( ! isset( $wp_query ) ) {
				_doing_it_wrong( __FUNCTION__, esc_html__( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' );
				return false;
			}
			return property_exists( $wp_query, 'is_sitemap' ) ? $wp_query->is_sitemap : false;
		}
		global $xmlsf;
		if ( ! is_object( $xmlsf ) || false === $xmlsf->request_filtered ) {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Conditional sitemap tags do not work before the sitemap request filter is run. Before then, they always return false.', 'xml-sitemap-feed' ), '4.8' );
			return false;
		}
		return $xmlsf->is_sitemap;
	}
}

if ( ! function_exists( 'is_news' ) ) {
	/**
	 * Is the query for a news sitemap?
	 *
	 * @since 4.8
	 * @return bool
	 */
	function is_news() {
		global $xmlsf;
		if ( ! is_object( $xmlsf ) || false === $xmlsf->request_filtered_news ) {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Conditional sitemap tags do not work before the sitemap request filter is run. Before then, they always return false.', 'xml-sitemap-feed' ), '4.8' );
			return false;
		}
		return $xmlsf->is_news;
	}
}

// Register custom meta fields
function xmlsf_register_post_meta_fields() {
    register_post_meta('post', 'keywords', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
    ));
    register_post_meta('post', 'stock_ticker', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
    ));
}
add_action('init', 'xmlsf_register_post_meta_fields');

// Add meta box to post edit screen
function xmlsf_add_custom_meta_box() {
    add_meta_box(
        'xmlsf_custom_meta_box',
        'Article Metadata for News Sitemap',
        'xmlsf_custom_meta_box_callback',
        'post',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'xmlsf_add_custom_meta_box');

// Meta box callback function
function xmlsf_custom_meta_box_callback($post) {
    wp_nonce_field('xmlsf_custom_meta_box', 'xmlsf_custom_meta_box_nonce');
    $keywords = get_post_meta($post->ID, 'keywords', true);
    $stock_ticker = get_post_meta($post->ID, 'stock_ticker', true);
    ?>
    <p>
        <label for="xmlsf_keywords">Keywords (comma-separated):</label><br>
        <input type="text" id="xmlsf_keywords" name="xmlsf_keywords" value="<?php echo esc_attr($keywords); ?>" style="width:100%">
    </p>
    <p>
        <label for="xmlsf_stock_ticker">Stock Ticker:</label><br>
        <input type="text" id="xmlsf_stock_ticker" name="xmlsf_stock_ticker" value="<?php echo esc_attr($stock_ticker); ?>" style="width:100%">
    </p>
    <?php
}

// Save meta box data
function xmlsf_save_custom_meta_box($post_id) {
    if (!isset($_POST['xmlsf_custom_meta_box_nonce']) || !wp_verify_nonce($_POST['xmlsf_custom_meta_box_nonce'], 'xmlsf_custom_meta_box')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['xmlsf_keywords'])) {
        update_post_meta($post_id, 'keywords', sanitize_text_field($_POST['xmlsf_keywords']));
    }
    if (isset($_POST['xmlsf_stock_ticker'])) {
        update_post_meta($post_id, 'stock_ticker', sanitize_text_field($_POST['xmlsf_stock_ticker']));
    }
}
add_action('save_post', 'xmlsf_save_custom_meta_box');

// Add columns to the post list table
function xmlsf_add_custom_columns($columns) {
    $columns['keywords'] = 'Keywords';
    $columns['stock_ticker'] = 'Stock Ticker';
    return $columns;
}
add_filter('manage_posts_columns', 'xmlsf_add_custom_columns');

// Populate custom columns in the post list table
function xmlsf_custom_column_content($column_name, $post_id) {
    if ($column_name == 'keywords') {
        echo esc_html(get_post_meta($post_id, 'keywords', true));
    }
    if ($column_name == 'stock_ticker') {
        echo esc_html(get_post_meta($post_id, 'stock_ticker', true));
    }
}
add_action('manage_posts_custom_column', 'xmlsf_custom_column_content', 10, 2);
