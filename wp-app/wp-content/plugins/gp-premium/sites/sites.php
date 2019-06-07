<?php
defined( 'WPINC' ) or die;

define( 'GENERATE_SITES_PATH', plugin_dir_path( __FILE__ ) );
define( 'GENERATE_SITES_URL', plugin_dir_url( __FILE__ ) );

require_once GENERATE_SITES_PATH . 'classes/class-site.php';
require_once GENERATE_SITES_PATH . 'classes/class-site-helper.php';
require_once GENERATE_SITES_PATH . 'classes/class-site-widget-importer.php';

/**
 * Checks to see if we're in the Site dashboard.
 *
 * @since 1.6
 *
 * @return bool
 */
function generate_is_sites_dashboard() {
	$screen = get_current_screen();

	if ( ! is_object( $screen ) ) {
		return false;
	}

	if ( 'appearance_page_generatepress-site-library' === $screen->id ) {
		return true;
	}

	return false;
}

add_filter( 'generate_dashboard_tabs', 'generate_sites_dashboard_tab' );
/**
 * Add the Sites tab to our Dashboard tabs.
 *
 * @since 1.6
 *
 * @param array $tabs Existing tabs.
 * @return array New tabs.
 */
function generate_sites_dashboard_tab( $tabs ) {
	$tabs['Sites'] = array(
		'name' => __( 'Site Library', 'gp-premium' ),
		'url' => admin_url( 'themes.php?page=generatepress-site-library' ),
		'class' => generate_is_sites_dashboard() ? 'active' : '',
	);

	return $tabs;
}

add_action( 'admin_menu', 'generate_site_library_register' );
/**
 * Register our Site Library page.
 *
 * @since 1.7
 */
function generate_site_library_register() {
	add_submenu_page(
		'themes.php',
		__( 'Site Library', 'gp-premium' ),
		__( 'Site Library', 'gp-premium' ),
		'manage_options',
		'generatepress-site-library',
		'generate_sites_container'
	);
}

add_action( 'admin_head', 'generate_site_library_fix_menu' );
/**
 * Set our current menu item as the GeneratePress Dashboard.
 *
 * @since 1.7
 */
function generate_site_library_fix_menu() {
	global $parent_file, $submenu_file, $post_type;

	if ( generate_is_sites_dashboard() ) {
		$parent_file = 'themes.php';
		$submenu_file = 'generate-options';
	}

	remove_submenu_page( 'themes.php', 'generatepress-site-library' );
}

add_action( 'admin_enqueue_scripts', 'generate_sites_do_enqueue_scripts' );
/**
 * Add our scripts for the site library.
 *
 * @since 1.8
 */
function generate_sites_do_enqueue_scripts() {
	wp_enqueue_script(
		'generate-sites-admin',
		GENERATE_SITES_URL . 'assets/js/admin.js',
		array( 'jquery', 'wp-util', 'updates', 'generate-sites-blazy' ),
		GP_PREMIUM_VERSION,
		true
	);

	wp_enqueue_script(
		'generate-sites-download',
		GENERATE_SITES_URL . 'assets/js/download.js',
		array( 'jquery', 'generate-sites-admin' ),
		GP_PREMIUM_VERSION,
		true
	);

	wp_enqueue_script(
		'generate-sites-blazy',
		GENERATE_SITES_URL . 'assets/js/blazy.min.js',
		array(),
		GP_PREMIUM_VERSION,
		true
	);

	wp_localize_script(
		'generate-sites-admin',
		'generate_sites_params',
		array(
			'ajaxurl'					=> admin_url( 'admin-ajax.php' ),
			'nonce'						=> wp_create_nonce( 'generate_sites_nonce' ),
			'importing_options'			=> __( 'Importing options', 'gp-premium' ),
			'backing_up_options'		=> __( 'Backing up options', 'gp-premium' ),
			'checking_demo_content'		=> __( 'Checking demo content', 'gp-premium' ),
			'downloading_content'		=> __( 'Downloading content', 'gp-premium' ),
			'importing_content'			=> __( 'Importing content', 'gp-premium' ),
			'importing_site_options'	=> __( 'Importing site options', 'gp-premium' ),
			'importing_widgets'			=> __( 'Importing widgets', 'gp-premium' ),
			'activating_plugins'		=> __( 'Activating plugins', 'gp-premium' ),
			'installing_plugins'		=> __( 'Installing plugins', 'gp-premium' ),
			'automatic_plugins'			=> __( 'Automatic', 'gp-premium' ),
			'manual_plugins'			=> __( 'Manual', 'gp-premium' ),
			'home_url'					=> home_url(),
		)
	);

	wp_enqueue_style(
		'generate-sites-admin',
		GENERATE_SITES_URL . 'assets/css/admin.css',
		array(),
		GP_PREMIUM_VERSION
	);

	wp_enqueue_style(
		'generate-premium-dashboard',
		plugin_dir_url( dirname(__FILE__) ) . 'inc/assets/dashboard.css',
		array(),
		GP_PREMIUM_VERSION
	);
}

add_filter( 'admin_body_class', 'generate_sites_do_admin_body_classes' );
/**
 * Add a body class while in the Site Library.
 *
 * @since 1.8
 *
 * @param array Current body classes.
 * @return array Existing and our new body classes
 */
function generate_sites_do_admin_body_classes( $classes ) {
	if ( generate_is_sites_dashboard() ) {
		$classes .= ' generate-sites';
	}

	return $classes;
}

add_action( 'generate_inside_site_library_container', 'generate_sites_add_tabs_wrapper_open', 4 );
/**
 * Add an opening wrapper element for our Dashboard tabs and page builder links.
 *
 * @since 1.8
 */
function generate_sites_add_tabs_wrapper_open() {
	echo '<div class="site-library-tabs-wrapper">';
}

/**
 * Adds our Site dashboard container.
 *
 * @since 1.6
 */
function generate_sites_container() {
	?>
	<div class="generate-site-library">
		<div class="site-library-header">
			<div class="site-library-container">
				<div class="library-title">
					<?php _e( 'GeneratePress Site Library', 'gp-premium' ); ?>
				</div>

				<div class="library-links">
					<a href="https://generatepress.com/support" target="_blank"><?php _e( 'Support', 'gp-premium' ); ?></a>
					<a href="https://docs.generatepress.com" target="_blank"><?php _e( 'Documentation', 'gp-premium' ); ?></a>
				</div>
			</div>
		</div>
		<div class="site-library-container">
			<?php
			do_action( 'generate_inside_site_library_container' );

			$site_data = get_transient( 'generatepress_sites' );
			$page_builders = array();

			foreach ( (array) $site_data as $data ) {
				if ( isset( $data['page_builder'][0] ) ) {
					$page_builder = $data['page_builder'][0];
					$page_builder_id = str_replace( ' ', '-', strtolower( $page_builder ) );

					if ( 'no-page-builder' !== $page_builder_id ) {
						$page_builders[ $page_builder_id ] = $page_builder;
					}
				}
			}

			echo '<div class="library-filters">';

			if ( ! empty( $page_builders ) ) : ?>
				<div class="page-builder-filter">
					<label for="page-builder" class="page-builder-label"><?php _e( 'Page Builder:', 'gp-premium' ); ?></label>
					<div class="filter-select">
						<select id="page-builder" class="page-builder-group" data-filter-group="page-builder" data-page-builder=".no-page-builder">
							<option value="no-page-builder"><?php _e( 'None', 'gp-premium' ); ?></option>
							<?php
							foreach( $page_builders as $id => $name ) {
								printf(
									'<option value="%1$s">%2$s</option>',
									$id,
									$name
								);
							}
							?>
						</select>
					</div>
				</div>
			<?php else : ?>
				<div class="page-builder-filter">
					<label for="page-builder" class="page-builder-label"><?php _e( 'Page Builder:', 'gp-premium' ); ?></label>
					<div class="filter-select">
						<select id="page-builder" class="page-builder-group" data-filter-group="page-builder" data-page-builder=".no-page-builder">
							<option value="no-page-builder"><?php _e( 'None', 'gp-premium' ); ?></option>
							<option value="beaver-builder"><?php _e( 'Beaver Builder', 'gp-premium' ); ?></option>
							<option value="elementor"><?php _e( 'Elementor', 'gp-premium' ); ?></option>
						</select>
					</div>
				</div>
			<?php endif; ?>

			</div>

			</div> <!-- .site-library-tabs-wrapper -->
			<?php // The opening wrapper for this is in generate_sites_add_tabs_wrapper_open() ?>

			<div class="generatepress-sites generatepress-admin-block" id="sites" data-page-builder=".no-page-builder">
				<?php do_action( 'generate_inside_sites_container' ); ?>
			</div>

			<?php
			printf(
				'<div class="refresh-sites">
					<a class="button" href="%1$s">%2$s</a>
				</div>',
				wp_nonce_url( admin_url( 'themes.php?page=generatepress-site-library' ), 'refresh_sites', 'refresh_sites_nonce' ),
				__( 'Refresh Sites', 'gp-premium' )
			);
			?>
		</div>
	</div>
	<?php
}

add_action( 'admin_init', 'generate_sites_refresh_list', 2 );
/**
 * Delete our sites transient if the Refresh sites link is clicked.
 *
 * @since 1.6
 */
function generate_sites_refresh_list() {
	if ( ! isset( $_GET['refresh_sites_nonce'] ) || ! wp_verify_nonce( $_GET['refresh_sites_nonce'], 'refresh_sites' ) ) {
		return;
	}

	delete_transient( 'generatepress_sites' );
}

/**
 * Get our page header meta slugs.
 *
 * @since 1.6
 *
 * @return array
 */
function generate_sites_export_page_headers() {
	$args = array(
		'post_type' => get_post_types( array( 'public' => true ) ),
		'showposts' => -1,
		'meta_query' => array(
		    array(
		        'key' => '_generate-select-page-header',
		        'compare' => 'EXISTS',
		    )
		)
	);

	$posts = get_posts( $args );
	$new_values = array();

	foreach ( $posts as $post ) {
		$page_header_id = get_post_meta( $post->ID, '_generate-select-page-header', true );

		if ( $page_header_id ) {
			$new_values[$post->ID] = $page_header_id;
		}
	}

	return $new_values;
}

/**
 * Get our Element display locations.
 *
 * @since 1.7
 *
 * @return array
 */
function generate_sites_export_elements_location() {
	$args = array(
		'post_type' => 'gp_elements',
		'showposts' => -1,
	);

	$posts = get_posts( $args );
	$new_values = array();

	foreach ( $posts as $post ) {
		$display_conditions = get_post_meta( $post->ID, '_generate_element_display_conditions', true );

		if ( $display_conditions ) {
			$new_values[$post->ID] = $display_conditions;
		}
	}

	return $new_values;
}

/**
 * Get our Element display locations.
 *
 * @since 1.7
 *
 * @return array
 */
function generate_sites_export_elements_exclusion() {
	$args = array(
		'post_type' => 'gp_elements',
		'showposts' => -1,
	);

	$posts = get_posts( $args );
	$new_values = array();

	foreach ( $posts as $post ) {
		$display_conditions = get_post_meta( $post->ID, '_generate_element_exclude_conditions', true );

		if ( $display_conditions ) {
			$new_values[$post->ID] = $display_conditions;
		}
	}

	return $new_values;
}

/**
 * List out compatible theme modules Sites can activate.
 *
 * @since 1.6
 *
 * @return array
 */
function generatepress_get_site_premium_modules() {
	return array(
		'Backgrounds' => 'generate_package_backgrounds',
		'Blog' => 'generate_package_blog',
		'Colors' => 'generate_package_colors',
		'Copyright' => 'generate_package_copyright',
		'Elements' => 'generate_package_elements',
		'Disable Elements' => 'generate_package_disable_elements',
		'Hooks' => 'generate_package_hooks',
		'Menu Plus' => 'generate_package_menu_plus',
		'Page Header' => 'generate_package_page_header',
		'Secondary Nav' => 'generate_package_secondary_nav',
		'Sections' => 'generate_package_sections',
		'Spacing' => 'generate_package_spacing',
		'Typography' => 'generate_package_typography',
		'WooCommerce' => 'generate_package_woocommerce',
	);
}

/**
 * Don't allow Sites to modify these options.
 *
 * @since 1.6
 *
 * @return array
 */
function generatepress_sites_disallowed_options() {
	return array(
		'admin_email',
		'siteurl',
		'home',
		'blog_charset',
		'blog_public',
		'current_theme',
		'stylesheet',
		'template',
		'default_role',
		'mailserver_login',
		'mailserver_pass',
		'mailserver_port',
		'mailserver_url',
		'permalink_structure',
		'rewrite_rules',
		'users_can_register',
	);
}

add_action( 'generate_export_items', 'generatepress_sites_add_export_checkbox' );
/**
 * Add our GeneratePress Site export checkbox to the Export module.
 *
 * @since 1.7
 */
function generatepress_sites_add_export_checkbox() {
	?>
	<hr style="margin:10px 0;border-bottom:0;" />

	<label>
		<input type="checkbox" name="module_group[]" value="generatepress-site" />
		<?php _ex( 'GeneratePress Site', 'Module name', 'gp-premium' ); ?>
	</label>
	<?php
}

add_filter( 'generate_export_data', 'generatepress_sites_do_site_options_export', 10, 2 );
/**
 * Add to our export .json file.
 *
 * @since 1.6
 *
 * @param array $data The current data being exported.
 * @return array Existing and extended data.
 */
function generatepress_sites_do_site_options_export( $data ) {
	// Bail if we haven't chosen to export the Site.
	if ( ! in_array( 'generatepress-site', $_POST['module_group'] ) ) {
		return $data;
	}

	// Modules
	$modules = generatepress_get_site_premium_modules();

	$data['modules'] = array();
	foreach ( $modules as $name => $key ) {
		if ( 'activated' == get_option( $key ) ) {
			$data['modules'][$name] = $key;
		}
	}

	// Site options
	$data['site_options']['nav_menu_locations'] = get_theme_mod( 'nav_menu_locations' );
	$data['site_options']['custom_logo']		= wp_get_attachment_url( get_theme_mod( 'custom_logo' ) );
	$data['site_options']['show_on_front']		= get_option( 'show_on_front' );
	$data['site_options']['page_on_front']		= get_option( 'page_on_front' );
	$data['site_options']['page_for_posts']		= get_option( 'page_for_posts' );

	// Page header
	$data['site_options']['page_header_global_locations'] = get_option( 'generate_page_header_global_locations' );
	$data['site_options']['page_headers'] = generate_sites_export_page_headers();

	// Elements
	$data['site_options']['element_locations'] = generate_sites_export_elements_location();
	$data['site_options']['element_exclusions'] = generate_sites_export_elements_exclusion();

	// Custom CSS.
	if ( function_exists( 'wp_get_custom_css_post' ) ) {
		$data['custom_css'] = wp_get_custom_css_post()->post_content;
	}

	// WooCommerce.
	if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		$data['site_options']['woocommerce_shop_page_id'] 				= get_option( 'woocommerce_shop_page_id' );
		$data['site_options']['woocommerce_cart_page_id'] 				= get_option( 'woocommerce_cart_page_id' );
		$data['site_options']['woocommerce_checkout_page_id'] 			= get_option( 'woocommerce_checkout_page_id' );
		$data['site_options']['woocommerce_myaccount_page_id'] 			= get_option( 'woocommerce_myaccount_page_id' );
		$data['site_options']['woocommerce_single_image_width'] 		= get_option( 'woocommerce_single_image_width' );
		$data['site_options']['woocommerce_thumbnail_image_width'] 		= get_option( 'woocommerce_thumbnail_image_width' );
		$data['site_options']['woocommerce_thumbnail_cropping'] 		= get_option( 'woocommerce_thumbnail_cropping' );
		$data['site_options']['woocommerce_shop_page_display'] 			= get_option( 'woocommerce_shop_page_display' );
		$data['site_options']['woocommerce_category_archive_display'] 	= get_option( 'woocommerce_category_archive_display' );
		$data['site_options']['woocommerce_default_catalog_orderby'] 	= get_option( 'woocommerce_default_catalog_orderby' );
	}

	// Elementor
	if ( is_plugin_active( 'elementor/elementor.php' ) ) {
		$data['site_options']['elementor_container_width']				= get_option( 'elementor_container_width' );
		$data['site_options']['elementor_cpt_support']					= get_option( 'elementor_cpt_support' );
		$data['site_options']['elementor_css_print_method']				= get_option( 'elementor_css_print_method' );
		$data['site_options']['elementor_default_generic_fonts']		= get_option( 'elementor_default_generic_fonts' );
		$data['site_options']['elementor_disable_color_schemes']		= get_option( 'elementor_disable_color_schemes' );
		$data['site_options']['elementor_disable_typography_schemes']	= get_option( 'elementor_disable_typography_schemes' );
		$data['site_options']['elementor_editor_break_lines']			= get_option( 'elementor_editor_break_lines' );
		$data['site_options']['elementor_exclude_user_roles']			= get_option( 'elementor_exclude_user_roles' );
		$data['site_options']['elementor_global_image_lightbox']		= get_option( 'elementor_global_image_lightbox' );
		$data['site_options']['elementor_page_title_selector']			= get_option( 'elementor_page_title_selector' );
		$data['site_options']['elementor_scheme_color']					= get_option( 'elementor_scheme_color' );
		$data['site_options']['elementor_scheme_color-picker']			= get_option( 'elementor_scheme_color-picker' );
		$data['site_options']['elementor_scheme_typography']			= get_option( 'elementor_scheme_typography' );
		$data['site_options']['elementor_space_between_widgets']		= get_option( 'elementor_space_between_widgets' );
		$data['site_options']['elementor_stretched_section_container']	= get_option( 'elementor_stretched_section_container' );
	}

	// Beaver Builder
	if ( is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' ) || is_plugin_active( 'bb-plugin/fl-builder.php' ) ) {
		$data['site_options']['_fl_builder_enabled_icons'] 		= get_option( '_fl_builder_enabled_icons' );
		$data['site_options']['_fl_builder_enabled_modules'] 	= get_option( '_fl_builder_enabled_modules' );
		$data['site_options']['_fl_builder_post_types'] 		= get_option( '_fl_builder_post_types' );
		$data['site_options']['_fl_builder_color_presets'] 		= get_option( '_fl_builder_color_presets' );
		$data['site_options']['_fl_builder_services'] 			= get_option( '_fl_builder_services' );
		$data['site_options']['_fl_builder_settings'] 			= get_option( '_fl_builder_settings' );
		$data['site_options']['_fl_builder_user_access'] 		= get_option( '_fl_builder_user_access' );
		$data['site_options']['_fl_builder_enabled_templates'] 	= get_option( '_fl_builder_enabled_templates' );
	}

	// Menu Icons
	if ( is_plugin_active( 'menu-icons/menu-icons.php' ) ) {
		$data['site_options']['menu-icons'] = get_option( 'menu-icons' );
	}

	// Ninja Forms
	if ( is_plugin_active( 'ninja-forms/ninja-forms.php' ) ) {
		$data['site_options']['ninja_forms_settings'] = get_option( 'ninja_forms_settings' );
	}

	// Social Warfare
	if ( is_plugin_active( 'social-warfare/social-warfare.php' ) ) {
		$data['site_options']['socialWarfareOptions'] = get_option( 'socialWarfareOptions' );
	}

	// Elements Plus
	if ( is_plugin_active( 'elements-plus/elements-plus.php' ) ) {
		$data['site_options']['elements_plus_settings'] = get_option( 'elements_plus_settings' );
	}

	// Ank Google Map
	if ( is_plugin_active( 'ank-google-map/ank-google-map.php' ) ) {
		$data['site_options']['ank_google_map'] = get_option( 'ank_google_map' );
	}

	// Active plugins
	$active_plugins = get_option( 'active_plugins' );
	$all_plugins = get_plugins();

	$ignore = apply_filters( 'generate_sites_ignore_plugins', array(
		'gp-premium/gp-premium.php',
		'widget-importer-exporter/widget-importer-exporter.php'
	) );

	foreach ( $ignore as $plugin ) {
		unset( $all_plugins[ $plugin ] );
	}

	$activated_plugins = array();

	foreach ( $active_plugins as $p ) {
		if ( isset( $all_plugins[$p] ) ) {
			$activated_plugins[$all_plugins[$p]['Name']] = $p;
		}
	}

	$data['plugins'] = $activated_plugins;

	return $data;

}

add_action( 'current_screen', 'generatepress_sites_init', 5 );
/**
 * Fetch our sites and trusted authors. Stores them in their own transients.
 * We use current_screen instead of admin_init so we can check what admin page we're on.
 *
 * @since 1.6
 */
function generatepress_sites_init() {
	$screen = get_current_screen();

	if ( 'appearance_page_generate-options' === $screen->id || 'appearance_page_generatepress-site-library' === $screen->id ) {
		$remote_sites = get_transient( 'generatepress_sites' );
		$trusted_authors = get_transient( 'generatepress_sites_trusted_providers' );

		if ( empty( $remote_sites ) ) {
			$sites = array();

			$data = wp_safe_remote_get( 'https://gpsites.co/wp-json/wp/v2/sites?per_page=50' );

			if ( is_wp_error( $data ) ) {
				set_transient( 'generatepress_sites', 'no results', 5 * MINUTE_IN_SECONDS );
				return;
			}

			$data = json_decode( wp_remote_retrieve_body( $data ), true );

			if ( ! is_array( $data ) ) {
				set_transient( 'generatepress_sites', 'no results', 5 * MINUTE_IN_SECONDS );
				return;
			}

			foreach( ( array ) $data as $site ) {
				$sites[$site['name']] = array(
					'name'			=> $site['name'],
					'directory' 	=> $site['directory'],
					'preview_url'	=> $site['preview_url'],
					'author_name'	=> $site['author_name'],
					'author_url'	=> $site['author_url'],
					'description'	=> $site['description'],
					'page_builder'	=> $site['page_builder'],
					'min_version'	=> $site['min_version'],
					'uploads_url'	=> $site['uploads_url'],
					'plugins'		=> $site['plugins'],
					'documentation'	=> $site['documentation'],
				);
			}

			$sites = apply_filters( 'generate_add_sites', $sites );

			set_transient( 'generatepress_sites', $sites, 24 * HOUR_IN_SECONDS );
		}

		if ( empty( $trusted_authors ) ) {
			$trusted_authors = wp_safe_remote_get( 'https://gpsites.co/wp-json/sites/site' );

			if ( is_wp_error( $trusted_authors ) || empty( $trusted_authors ) ) {
				set_transient( 'generatepress_sites_trusted_providers', 'no results', 5 * MINUTE_IN_SECONDS );
				return;
			}

			$trusted_authors = json_decode( wp_remote_retrieve_body( $trusted_authors ), true );

			$authors = array();
			foreach ( ( array ) $trusted_authors['trusted_author'] as $author ) {
				$authors[] = $author;
			}

			set_transient( 'generatepress_sites_trusted_providers', $authors, 24 * HOUR_IN_SECONDS );
		}
	}
}

add_action( 'plugins_loaded', 'generatepress_sites_output' );
/**
 * Initiate our Sites once everything has loaded.
 * We use current_screen instead of admin_init so we can check what admin page we're on.
 *
 * @since 1.6
 */
function generatepress_sites_output() {
	if ( ! class_exists( 'GeneratePress_Site' ) ) {
		return; // Bail if we don't have the needed class.
	}

	$sites = get_transient( 'generatepress_sites' );

	if ( empty( $sites ) || ! is_array( $sites ) ) {
		add_action( 'generate_inside_sites_container', 'generatepress_sites_no_results_error' );
		return;
	}

	if ( apply_filters( 'generate_sites_randomize', true ) ) {
		shuffle( $sites );
	}

	foreach( $sites as $site ) {
		new GeneratePress_Site( $site );
	}
}

/**
 * Show an error message when no sites exist.
 *
 * @since 1.8.2
 */
function generatepress_sites_no_results_error() {
	printf(
		'<div class="no-site-library-results">
			%1$s <a href="%3$s" target="_blank" rel="noopener noreferrer">%2$s</a>
		</div>',
		__( 'No sites found.', 'gp-premium' ),
		__( 'Why?', 'gp-premium' ),
		'https://docs.generatepress.com/article/site-library-unavailable/'
	);
}