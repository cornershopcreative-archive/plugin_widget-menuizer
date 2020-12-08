<?php
/**
Plugin Name: Widget Menuizer
Plugin URI: http://cornershopcreative.com/code/widget-menuizer
Description: Embed sidebar regions in your WordPress navigation menus.
Version: 1.1.1
Author: Cornershop Creative
Author URI: http://cornershopcreative.com
License: GPLv2 or later
Text Domain: widget-menuizer
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Main plugin class CSHP_Widget_Menuizer.
 */
class CSHP_Widget_Menuizer {

	/**
	 * Plugin version number
	 *
	 * @var version
	 */
	protected $version = '1.1.1';

	/**
	 * Setting up our class.
	 */
	public function __construct() {
		if ( ! defined( 'CSHP_WM_PATH' ) ) {
			define( 'CSHP_WM_PATH', plugin_dir_path( __FILE__ ) );
			define( 'CSHP_WM_URL', plugin_dir_url( __FILE__ ) );
			define( 'CSHP_WM_TEXTDOMAIN', 'widget-menuizer' );
		}

		$this->load_dependencies();

		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'wp_enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
		add_filter( 'wp_edit_nav_menu_walker', array( &$this, 'override_edit_nav_menu_walker' ), 99 );
		add_filter( 'walker_nav_menu_start_el', array( &$this, 'menuizer_nav_menu_start_el' ), 99, 4 );
		add_action( 'wp_update_nav_menu_item', array( &$this, 'wp_update_nav_menu_item' ), 10, 2 );
		add_filter( 'wp_setup_nav_menu_item', array( &$this, 'wp_setup_nav_menu_item' ), 10, 1 );
		add_action( 'wp_ajax_cshp_wm_add_widget_area', array( &$this, 'add_widget_area' ) );
		add_action( 'wp_ajax_cshp_wm_remove_widget_area', array( &$this, 'remove_widget_area' ) );
		add_action( 'widgets_init', array( &$this, 'widgets_init' ) );

	}

	/**
	 * Load Dependencies Step.
	 */
	private function load_dependencies() {
		$this->require_all( __DIR__ . DIRECTORY_SEPARATOR . 'inc' );

		$this->build_menu_settings();
	}

	/**
	 * Build the admin page.
	 */
	private function build_menu_settings() {
		require_once( CSHP_WM_PATH . 'views/admin/menu-settings.php' );
	}

	/**
	 * Perform actions that need to run on admin init.
	 */
	public function admin_init() {
		// Add our metabox to the nav-menus.php sidebar
		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
		add_meta_box( 'add-sidebars', __( 'Sidebars', CSHP_WM_TEXTDOMAIN ), array( &$this, 'wp_nav_menu_sidebar_meta_box' ), 'nav-menus', 'side', 'low' );
	}

	/**
	 * Add our tiny bit of CSS
	 */
	public function admin_enqueue_scripts( $hook ) {
		// register scripts and styles
		wp_register_style( 'cshp-wm-stylesheet', CSHP_WM_URL . 'assets/css/widget-menuizer.css', null, $this->version, false );
		wp_register_style( 'cshp-wm-sidebar', CSHP_WM_URL . 'assets/css/sidebars.css', null, $this->version, false );
		wp_register_script( 'cshp-wm-sidebar', CSHP_WM_URL . 'assets/js/sidebars.js', array( 'jquery' ), $this->version, true );
		wp_localize_script(
			'cshp-wm-sidebar', 'cshp_wm_sidebars_options', array(
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'cshp_wm_sidebars_nonce'    => wp_create_nonce( 'cshp_wm_sidebars_nonce' ),
			)
		);

		// Add scripts and style on the needed admin screen
		switch ( $hook ) :
			case 'widgets.php':
				wp_enqueue_script( 'cshp-wm-sidebar' );
				wp_enqueue_style( 'cshp-wm-sidebar' );
				break;
			case 'nav-menus.php':
				wp_enqueue_style( 'menuizer_stylesheet' );
		endswitch;
	}

	/**
	 * Register and enqueue scripts for front end.
	 */
	public function wp_enqueue_scripts( $hook ) {
		// bail if admin.
		if ( is_admin() ) {
			return;
		}

		// register our front stylesheet.
		wp_register_style( 'cshp-wm-front', CSHP_WM_URL . 'assets/css/widget-menuizer-front.css', null, $this->version, false );

		// if widget_menuizer_dropdown_settings_show_on_hover is set to on then proceed.
		if ( (bool) get_option( 'widget_menuizer_dropdown_settings_show_on_hover' ) ) {
			// enqueue stylesheet.
			wp_enqueue_style( 'cshp-wm-front' );
		}
	}

	/**
	 * Generate a metabox for the sidebars item.
	 *
	 * @since 3.0.0
	 */
	public function wp_nav_menu_sidebar_meta_box() {
		global $_nav_menu_placeholder, $nav_menu_selected_id, $wp_registered_sidebars;
		// phpcs:ignore WordPress.Variables.GlobalVariables.OverrideProhibited
		$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
		$theme = basename( get_stylesheet_directory() );

		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		);

		?>
		<div class="sidebardiv posttypediv" id="sidebardiv">
			<div id="sidebar-panel" class="tabs-panel tabs-panel-active">
				<ul id="sidebar-checklist" class="form-no-clear categorychecklist">
					<?php
					foreach ( $wp_registered_sidebars as $id => $sidebar ) :
						$numeric_id = hexdec( substr( md5( $theme . $id ), 0, 7 ) );
						// this is just a placeholder of a unique id to keep WP from getting confused
						?>
						<li>
						<label class="menu-item-title">
							<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo esc_attr( $_nav_menu_placeholder ); ?>][menu-item-object-id]" value="<?php echo esc_attr( $numeric_id ); ?>">
							<?php echo esc_html( $sidebar['name'] ); ?>
						</label>
						<input type="hidden" class="menu-item-db-id" name="menu-item[<?php echo esc_attr( $_nav_menu_placeholder ); ?>][menu-item-db-id]" value="0" />
						<input type="hidden" class="menu-item-object" name="menu-item[<?php echo esc_attr( $_nav_menu_placeholder ); ?>][menu-item-object]" value="<?php echo esc_attr( $theme ); ?>" />
						<input type="hidden" class="menu-item-parent-id" name="menu-item[<?php echo esc_attr( $_nav_menu_placeholder ); ?>][menu-item-parent-id]" value="0" />
						<input type="hidden" class="menu-item-type" name="menu-item[<?php echo esc_attr( $_nav_menu_placeholder ); ?>][menu-item-type]" value="sidebar" />
						<input type="hidden" class="menu-item-title" name="menu-item[<?php echo esc_attr( $_nav_menu_placeholder ); ?>][menu-item-title]" value="<?php echo esc_attr( $sidebar['name'] ); ?>" />
						<input type="hidden" class="menu-item-url" name="menu-item[<?php echo esc_attr( $_nav_menu_placeholder ); ?>][menu-item-url]" value="" />
						<input type="hidden" class="menu-item-target" name="menu-item[<?php echo esc_attr( $_nav_menu_placeholder ); ?>][menu-item-target]" value="div" />
						<input type="hidden" class="menu-item-attr_title" name="menu-item[<?php echo esc_attr( $_nav_menu_placeholder ); ?>][menu-item-attr_title]" value="" />
						<input type="hidden" class="menu-item-classes" name="menu-item[<?php echo esc_attr( $_nav_menu_placeholder ); ?>][menu-item-classes]" value="" />
						<input type="hidden" class="menu-item-xfn" name="menu-item[<?php echo esc_attr( $_nav_menu_placeholder ); ?>][menu-item-xfn]" value="<?php echo esc_attr( $id ); ?>" /></li>
						</li>
					<?php
					$_nav_menu_placeholder--;
					endforeach;
					?>
				</ul>
			</div>

			<!-- no touch! -->
			<p class="button-controls">
				<span class="list-controls">
					<a href="
					<?php
						echo esc_url(
							add_query_arg(
								array(
									'selectall' => 1,
								),
								remove_query_arg( $removed_args )
							)
						);
					?>
					#sidebardiv" class="select-all">Select All</a>
				</span>
				<span class="add-to-menu">
					<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-sidebar-menu-item" id="submit-sidebardiv" />
					<span class="spinner"></span>
				</span>
			</p>

		</div><!-- /.sidebardiv -->
		<?php
	}

	/**
	 * Tell wp_edit_nav_menu_walker to use our new class in order to display the admin sidebar menu item options.
	 * TODO: provide a user-facing alert if something got in our way...
	 */
	public function override_edit_nav_menu_walker( $class ) {
			return 'Sidebar_Walker_Nav_Menu_Edit';
	}

	/**
	 * When outputting a menu, spit out our sidebar if specified
	 */
	public function menuizer_nav_menu_start_el( $item_output, $item, $depth, $args ) {

		if ( 'sidebar' === $item->type ) {

			/**
			 * We've hacked up the normal uses of $item's properties as follows:
			 * $item->type       = sidebar
			 * $item->object_id  = an arbitrary md5-ish value to keep WP happy
			 * $item->object     = the theme this sidebar belongs to, e.g. twentyfourteen
			 * $item->target     = the container element (div, ul, ol, aside, span, etc)
			 * $item->classes    = the 'classes' textfield, as normal
			 * $item->title      = the 'title' textfield, as normal
			 * $item->xfn        = the machine name of the sidebar to show
			 * $item->attr_title = location to show the title (none|inside|outside)
			 * $item->url        = can't be used as WP only saves this if the type == 'custom'... sigh
			 */

			// output nothing if this item isn't from the currently active theme
			$theme = basename( get_stylesheet_directory() );
			if ( $theme !== $item->object ) {
				return '';
			}

			// output nothing if the given sidebar isn't active
			if ( ! is_active_sidebar( $item->xfn ) ) {
				return '';
			}

			// start assembling our output
			$output = '';

			// output the title here, if desired
			if ( 'outside' === $item->attr_title ) {
				$output = '<span class="menuizer-title">' . $item->title . '</span>';
			}

			// stringify custom classes for inclusion in container
			$classes = array();
			foreach ( $item->classes as $class ) {
				if ( strpos( $class, 'menu-item' ) === false ) {
					$classes[] = $class;
				}
			}

			if ( isset( $item->stack_direction ) ) {
				$classes[] = 'menuizer-stack-' . $item->stack_direction;
			}

			$classes = implode( ' ', $classes );

			// wrap
			if ( 'none' !== $item->target ) {
				$output .= '<' . $item->target . ' class="menuizer-container ' . $classes . '">';
			}
			// output the title here, if desired
			if ( 'inside' === $item->attr_title ) {
				$output .= '<span class="menuizer-title">' . $item->title . '</span>';
			}
			ob_start();
			dynamic_sidebar( $item->xfn );
			$output .= ob_get_clean();
			if ( 'none' !== $item->target ) {
				$output .= '</' . $item->target . '>';
			}
			$item_output = $output;

		}//end if

		return $item_output;
	}

	/**
	 * Saves new field to postmeta for navigation.
	 *
	 * @param int $menu_id         the id of the menu.
	 * @param int $menu_item_db_id the id of the menu item.
	 */
	function wp_update_nav_menu_item( $menu_id, $menu_item_db_id ) {
		// phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		if ( ! empty( $_REQUEST['menu-item-stack-direction'] ) && is_array( $_REQUEST['menu-item-stack-direction'] ) && isset( $_REQUEST['menu-item-stack-direction'][ $menu_item_db_id ] ) ) {
			// phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			$stack_direction = ! empty( $_REQUEST['menu-item-stack-direction'][ $menu_item_db_id ] ) ? $_REQUEST['menu-item-stack-direction'][ $menu_item_db_id ] : '';
			update_post_meta( $menu_item_db_id, '_menu_item_stack_direction', $stack_direction );
		}
	}

	/**
	 * Adds value of new field to $item object that will be passed to Walker_Nav_Menu_Edit.
	 *
	 * @param object $menu_item the menu item object.
	 *
	 * @return array $menu_item the menu item object.
	 */
	function wp_setup_nav_menu_item( $menu_item ) {
		if ( isset( $menu_item->ID ) ) :
			$menu_item->stack_direction = get_post_meta( $menu_item->ID, '_menu_item_stack_direction', true );
		endif;

		return $menu_item;
	}

	/**
	 * Add user-created sidebar.
	 * triggered by add sidebar button under the add a new sidebar section on the widgets area.
	 */
	public function add_widget_area() {
		// Check Nonce or die.
		if ( ! wp_verify_nonce( $_POST['cshp_wm_sidebars_nonce'], 'cshp_wm_sidebars_nonce' ) && ! isset( $_POST['cshp_wm_widget_area_name'] ) ) {
			die( -1 );
		}
		// get sidebars wp option.
		$cshp_sidebars = get_option( 'cshp_wm_widget_areas' );
		if ( empty( $cshp_sidebars ) ) {
			$cshp_sidebars = array(
				'areas' => array(),
			);
		}
		// get widget area name
		$widget_area_name = sanitize_text_field( $_POST['cshp_wm_widget_area_name'] );
		// create the slug part
		$slug = sanitize_title_with_dashes( $widget_area_name, '', 'save' );
		// Check If Sidebar already exists.
		if ( isset( $cshp_sidebars['areas'][ 'cshp-wm-widget-area-' . $slug ] ) ) {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => sprintf(
						'<strong>%1$s</strong> widget area already exists. Please use a different name.',
						esc_html( $widget_area_name )
					),
				)
			);
			die( -1 );
		}
		// get widget area description
		$widget_area_desc = isset( $_POST['cshp_wm_widget_area_desc'] ) ? sanitize_text_field( $_POST['cshp_wm_widget_area_desc'] ) : '';
		// setup sidebar in array.
		$cshp_sidebars['areas'][ 'cshp-wm-widget-area-' . $slug ]['name'] = $widget_area_name;
		$cshp_sidebars['areas'][ 'cshp-wm-widget-area-' . $slug ]['description'] = $widget_area_desc;
		// update the sidebar wp option.
		update_option( 'cshp_wm_widget_areas', $cshp_sidebars, true );
		// echo message back on AJAX.
		echo wp_json_encode(
			array(
				'status'  => 'success',
				'message' => sprintf(
					'<strong>%1$s</strong> widget area has been created. You can create more areas, once you finish update the page to see all the areas.',
					esc_html( $widget_area_name )
				),
			)
		);
		// die.
		die();
	}

	/**
	 * Remove user-created sidebar.
	 * triggered by each of the sidebars button.
	 */
	public function remove_widget_area() {
		// Check Nonce or die.
		if ( ! wp_verify_nonce( $_POST['cshp_wm_sidebars_nonce'], 'cshp_wm_sidebars_nonce' ) && ! isset( $_POST['cshp_wm_widget_area_name'] ) ) {
			die( -1 );
		}
		// save the widget area id.
		$widget_area_name = $_POST['cshp_wm_widget_area_name'];
		// get sidebars wp option.
		$cshp_sidebars = get_option( 'cshp_wm_widget_areas' );
		// if there aren't any widget areas, there's nothing left to do here
		if ( empty( $cshp_sidebars ) || empty( $cshp_sidebars['areas'] ) || ! isset( $cshp_sidebars['areas'][ $widget_area_name ] ) ) {
			die();
		}
		// unset the sidebar from the array
		unset( $cshp_sidebars['areas'][ $widget_area_name ] );
		// update the sidebars wp option
		if ( update_option( 'cshp_wm_widget_areas', $cshp_sidebars, true ) ) {
			echo wp_json_encode(
				array(
					'status'  => 'success',
					'sidebarId' => $widget_area_name,
				)
			);
			die();
		}
		// die.
		die();
	}

	/**
	 * Register All User-created sidebars
	 */
	public function widgets_init() {
		// get sidebars wp option.
		$cshp_sidebars = get_option( 'cshp_wm_widget_areas' );
		// check that there are sidebars to register.
		if ( ! empty( $cshp_sidebars ) && ! empty( $cshp_sidebars['areas'] ) ) :
			// loop through sidebars.
			foreach ( $cshp_sidebars['areas'] as $id => $sidebar ) :
				// register each sidebar.
				register_sidebar(
					array(
						'name'          => stripslashes( sanitize_text_field( $sidebar['name'] ) ),
						'id'            => sanitize_text_field( $id ),
						'description'   => stripslashes( sanitize_text_field( $sidebar['description'] ) ),
						'before_widget' => '<div id="%1$s" class="widget %2$s">',
						'after_widget'  => '</div>',
						'before_title'  => '<h4 class="widget-title">',
						'after_title'   => '</h4>',
					)
				);
			endforeach;
		endif;
	}

	/**
	 * Include other functions
	 */
	protected function require_all( $dir, $depth = 0 ) {
		// strip slashes from end of string
		$dir = rtrim( $dir, '/\\' );
		// require all php files
		$scan = glob( $dir . DIRECTORY_SEPARATOR . '*' );
		foreach ( $scan as $path ) {
			if ( preg_match( '/\.php$|\.inc$/', $path ) ) {
				require_once $path;
			} elseif ( is_dir( $path ) ) {
				$this->require_all( $path, $depth + 1 );
			}
		}
	}

	/**
	 * Get the plugin version number.
	 */
	public function get_version() {
		return $this->version;
	}
}

/**
 * Let's load our plugin class.
 */
function run_cshp_widget_menuizer() {
	$cshp_wm = new CSHP_Widget_Menuizer();
}

run_cshp_widget_menuizer();
