<?php
/**
 * The Sidebar_Walker_Nav_Menu_Edit class file.
 */

// Load all the nav menu interface functions
if ( ! class_exists( 'Walker_Nav_Menu_Edit' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/nav-menu.php' );
}

/**
 * Implements a new walker to display our sidebar menu item once it's in a menu
 * If WordPress ever changes Walker_Nav_Menu_Edit (defined in wp-admin/includes/nav-menu.php), we've got work to do
 *
 * @uses Walker_Nav_Menu
 */
class Sidebar_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {

	/**
	 * Start the element output.
	 *
	 * @see Walker_Nav_Menu::start_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   Not used.
	 * @param int    $id     Not used.
	 */
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $_wp_nav_menu_max_depth, $wp_registered_sidebars;
		// phpcs:ignore WordPress.Variables.GlobalVariables.OverrideProhibited
		$_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;

		ob_start();
		$item_id = $item->ID;
		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		);

		$original_title = '';
		if ( 'taxonomy' === $item->type ) {
			$original_title = get_term_field( 'name', $item->object_id, $item->object, 'raw' );
			if ( is_wp_error( $original_title ) ) {
				$original_title = false;
			}
		} elseif ( 'post_type' === $item->type ) {
			$original_object = get_post( $item->object_id );
			$original_title = get_the_title( $original_object->ID );
		} elseif ( 'sidebar' === $item->type ) {
			$original_title = $wp_registered_sidebars[ $item->xfn ]['name'];
			$item->type_label = __( 'Sidebar' );
		}

		$classes = array(
			'menu-item menu-item-depth-' . $depth,
			'menu-item-' . esc_attr( $item->object ),
			// phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && $item_id === $_GET['edit-menu-item'] ) ? 'active' : 'inactive'),
		);

		// if the menu item is a sidebar and it belongs to a theme other than the active one, it's invalid
		if ( 'sidebar' === $item->type && basename( get_stylesheet_directory() ) !== $item->object ) {
			// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
			$item->_invalid = __( 'This sidebar cannot be displayed as it is not from the currently active theme', CSHP_WM_TEXTDOMAIN );
		}

		$title = $item->title;

		if ( ! empty( $item->_invalid ) ) {
			$classes[] = 'menu-item-invalid';
			/* translators: %s: title of menu item which is invalid */
			$title = sprintf( __( '%s (Invalid)' ), $item->title );
		} elseif ( isset( $item->post_status ) && 'draft' === $item->post_status ) {
			$classes[] = 'pending';
			/* translators: %s: title of menu item in draft status */
			$title = sprintf( __( '%s (Pending)' ), $item->title );
		}

		$title = ( ! isset( $item->label ) || '' === $item->label ) ? $title : $item->label;

		$submenu_text = '';
		if ( 0 === $depth ) {
			$submenu_text = 'style="display: none;"';
		}

		?>
		<li id="menu-item-<?php echo esc_attr( $item_id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<dl class="menu-item-bar">
				<dt class="menu-item-handle">
					<span class="item-title"><span class="menu-item-title"><?php echo esc_html( $title ); ?></span> <span class="is-submenu" <?php /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ echo $submenu_text; ?>><?php esc_html_e( 'sub item' ); ?></span></span>
					<span class="item-controls">
						<span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
						<span class="item-order hide-if-js">
							<a href="
							<?php
								echo esc_url(
									wp_nonce_url(
										add_query_arg(
											array(
												'action' => 'move-up-menu-item',
												'menu-item' => $item_id,
											),
											remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) )
										),
										'move-menu_item'
									)
								);
							?>
							" class="item-move-up"><abbr title="<?php esc_attr_e( 'Move up' ); ?>">&#8593;</abbr></a>
							|
							<a href="
							<?php
								echo esc_url(
									wp_nonce_url(
										add_query_arg(
											array(
												'action' => 'move-down-menu-item',
												'menu-item' => $item_id,
											),
											remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) )
										),
										'move-menu_item'
									)
								);
							?>
							" class="item-move-down"><abbr title="<?php esc_attr_e( 'Move down' ); ?>">&#8595;</abbr></a>
						</span>
						<a class="item-edit" id="edit-<?php echo esc_attr( $item_id ); ?>" href="<?php /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped,WordPress.CSRF.NonceVerification.NoNonceVerification */ echo ( isset( $_GET['edit-menu-item'] ) && $item_id === $_GET['edit-menu-item'] ) ? esc_url( admin_url( 'nav-menus.php' ) ) : esc_url( add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) ) ); ?>" aria-label="<?php esc_attr_e( 'Edit menu item' ); ?>"><span class="screen-reader-text"><?php esc_html_e( 'Edit' ); ?></span></a>
					</span>
				</dt>
			</dl>

			<div class="menu-item-settings wp-clearfix" id="menu-item-settings-<?php echo esc_attr( $item_id ); ?>">
				<?php if ( 'custom' === $item->type ) : ?>
					<p class="field-url description description-wide">
						<label for="edit-menu-item-url-<?php echo esc_attr( $item_id ); ?>">
							<?php esc_html_e( 'URL' ); ?><br />
							<input type="text" id="edit-menu-item-url-<?php echo esc_attr( $item_id ); ?>" class="widefat code edit-menu-item-url" name="menu-item-url[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->url ); ?>" />
						</label>
					</p>
				<?php endif; ?>
				<p class="description description-thin">
					<label for="edit-menu-item-title-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_html_e( 'Navigation Label' ); ?><br />
						<input type="text" id="edit-menu-item-title-<?php echo esc_attr( $item_id ); ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->title ); ?>" />
					</label>
				</p>
				<?php if ( 'sidebar' !== $item->type ) : ?>
				<p class="description description-thin">
					<label for="edit-menu-item-attr-title-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_html_e( 'Title Attribute' ); ?><br />
						<input type="text" id="edit-menu-item-attr-title-<?php echo esc_attr( $item_id ); ?>" class="widefat edit-menu-item-attr-title" name="menu-item-attr-title[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->post_excerpt ); ?>" />
					</label>
				</p>
				<p class="field-link-target description">
					<label for="edit-menu-item-target-<?php echo esc_attr( $item_id ); ?>">
						<input type="checkbox" id="edit-menu-item-target-<?php echo esc_attr( $item_id ); ?>" value="_blank" name="menu-item-target[<?php echo esc_attr( $item_id ); ?>]"<?php checked( $item->target, '_blank' ); ?> />
						<?php esc_html_e( 'Open link in a new window/tab' ); ?>
					</label>
				</p>
				<?php else : ?>
				<p class="description description-thin">
					<label for="edit-menu-item-title-display-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_html_e( 'Label Display' ); ?><br />
						<select id="edit-menu-item-attr-title-<?php echo esc_attr( $item_id ); ?>" class="widefat" name="menu-item-attr-title[<?php echo esc_attr( $item_id ); ?>]" >
						<?php
							$options = array(
								// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
								'none' => __( 'None', CSHP_WM_TEXTDOMAIN ),
								// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
								'inside' => __( 'Inside container', CSHP_WM_TEXTDOMAIN ),
								// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
								'outside' => __( 'Outside container', CSHP_WM_TEXTDOMAIN ),
							);
							foreach ( $options as $value => $label ) :
								?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $item->attr_title, $value ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				</p>
				<?php endif; ?>
				<p class="field-css-classes description description-thin">
					<label for="edit-menu-item-classes-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_html_e( 'CSS Classes (optional)' ); ?><br />
						<input type="text" id="edit-menu-item-classes-<?php echo esc_attr( $item_id ); ?>" class="widefat code edit-menu-item-classes" name="menu-item-classes[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( implode( ' ', $item->classes ) ); ?>" />
					</label>
				</p>
				<?php if ( 'sidebar' === $item->type ) : ?>
					<input type="hidden" id="edit-menu-item-xfn-<?php echo esc_attr( $item_id ); ?>" name="menu-item-xfn[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->xfn ); ?>" />
					<p class="field-link-container-target-proxy description description-thin">
						<label for="edit-menu-item-target-<?php echo esc_attr( $item_id ); ?>">
							<?php /* phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain */ esc_html_e( 'Container Element', CSHP_WM_TEXTDOMAIN ); ?><br />
							<select id="edit-menu-item-target-<?php echo esc_attr( $item_id ); ?>" class="widefat" name="menu-item-target[<?php echo esc_attr( $item_id ); ?>]" >
							<?php
								$elements = array( 'div', 'span', 'ul', 'ol', 'article', 'section', 'aside', 'none' );
							foreach ( $elements as $elem ) :
								?>
									<option value="<?php echo esc_attr( $elem ); ?>" <?php selected( $item->target, $elem ); ?>><?php echo esc_html( $elem ); ?></option>
								<?php
								endforeach;
							?>
							</select>
						</label>
					</p>
				<?php else : ?>
				<p class="field-xfn description description-thin">
					<label for="edit-menu-item-xfn-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_html_e( 'Link Relationship (XFN)' ); ?><br />
						<input type="text" id="edit-menu-item-xfn-<?php echo esc_attr( $item_id ); ?>" class="widefat code edit-menu-item-xfn" name="menu-item-xfn[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->xfn ); ?>" />
					</label>
				</p>
				<?php endif; ?>
				<?php if ( 'sidebar' === $item->type ) : ?>
				<p class="field-stack-direction description description-thin">
					<label for="edit-menu-item-stack-direction-<?php echo esc_attr( $item_id ); ?>">
							<?php /* phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain */ esc_html_e( 'Widget Layout', CSHP_WM_TEXTDOMAIN ); ?><br />
							<select id="edit-menu-item-stack-direction-<?php echo esc_attr( $item_id ); ?>" class="widefat" name="menu-item-stack-direction[<?php echo esc_attr( $item_id ); ?>]" >
							<?php
							$elements = array(
								/* phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain */
								'vertical' => __( 'Vertical', CSHP_WM_TEXTDOMAIN ),
								/* phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain */
								'horizontal' => __( 'Horizontal', CSHP_WM_TEXTDOMAIN ),
							);
							foreach ( $elements as $value => $label ) :
								?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $item->stack_direction, $value ); ?>><?php echo esc_html( $label ); ?></option>
								<?php
							endforeach;
							?>
							</select>
					</label>
				</p>
				<?php endif; ?>
				<p class="field-description description description-wide">
					<label for="edit-menu-item-description-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_html_e( 'Description' ); ?><br />
						<textarea id="edit-menu-item-description-<?php echo esc_attr( $item_id ); ?>" class="widefat edit-menu-item-description" rows="3" cols="20" name="menu-item-description[<?php echo esc_attr( $item_id ); ?>]">
							<?php
							// textarea_escaped
							echo esc_html( $item->description );
							?>
						</textarea>
						<span class="description"><?php esc_html_e( 'The description will be displayed in the menu if the current theme supports it.' ); ?></span>
					</label>
				</p>
				<?php
				/**
				 * WP core function added in 5.4 to display custom fields on menu items.
				 * Including it here as a pre 5.4 compatibility with other plugins
				 * and due to the nature of how Widget Menuizer works.
				 * Fires just before the move buttons of a nav menu item in the menu editor.
				 *
				 * @param int      $item_id Menu item ID.
				 * @param WP_Post  $item    Menu item data object.
				 * @param int      $depth   Depth of menu item. Used for padding.
				 * @param stdClass $args    An object of menu item arguments.
				 * @param int      $id      Nav menu ID.
				 */
				do_action( 'wp_nav_menu_item_custom_fields', $item_id, $item, $depth, $args, $id );
				?>
				<p class="field-move hide-if-no-js description description-wide">
					<label>
						<span><?php esc_html_e( 'Move' ); ?></span>
						<a href="#" class="menus-move-up"><?php esc_html_e( 'Up one' ); ?></a>
						<a href="#" class="menus-move-down"><?php esc_html_e( 'Down one' ); ?></a>
						<a href="#" class="menus-move-left"></a>
						<a href="#" class="menus-move-right"></a>
						<a href="#" class="menus-move-top"><?php esc_html_e( 'To the top' ); ?></a>
					</label>
				</p>

				<div class="menu-item-actions description-wide submitbox">
					<?php if ( 'sidebar' === $item->type ) : ?>
						<p class="link-to-original">
							<?php
							printf(
								// tranlators: sidebar link name
								/* phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain, WordPress.XSS.EscapeOutput.OutputNotEscaped, WordPress.WP.I18n.MissingTranslatorsComment */
								__( 'Sidebar Shown: %s', CSHP_WM_TEXTDOMAIN ), '<a href="' . esc_url( admin_url( 'widgets.php' ) ) . '">' . esc_html( $original_title ) . '</a>'
							);
							?>
						</p>
						<?php

						// flag invalid reasons
						if ( ! empty( $item->_invalid ) ) :
							?>
							<p class="warning invalid">
								<?php echo esc_html( $item->_invalid ); ?>
							</p>
						<?php endif; ?>

						<?php
						// if necessary, flag for potential recursion
						$current_widgets = get_option( 'sidebars_widgets' );
						if ( ! empty( $current_widgets ) && ! empty( $current_widgets[ $item->xfn ] ) ) :
							$found_menu = false;
							foreach ( $current_widgets[ $item->xfn ] as $widget_type ) {
								if ( strpos( $widget_type, 'nav_menu' ) === 0 ) {
									$found_menu = true;
									break;
								}
							}
							if ( $found_menu ) :
								?>
								<p class="warning recursion">
									<?php esc_html_e( 'This sidebar contains a menu widget! Please ensure the widget doesn’t contain this menu or an infinite loop will result.' ); ?>
								</p>
								<?php
							endif;
						endif;
						?>
					<?php elseif ( 'custom' !== $item->type && false !== $original_title ) : ?>
						<p class="link-to-original">
							<?php
							printf(
								// tranlators: sidebar link name
								/* phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain, WordPress.XSS.EscapeOutput.OutputNotEscaped, WordPress.WP.I18n.MissingTranslatorsComment */
								__( 'Original: %s' ), '<a href="' . esc_url( $item->url ) . '">' . esc_html( $original_title ) . '</a>'
							);
							?>
						</p>
					<?php endif; ?>
					<a class="item-delete submitdelete deletion" id="delete-<?php echo esc_attr( $item_id ); ?>" href="
																						<?php
																						echo esc_url(
																							wp_nonce_url(
																								add_query_arg(
																									array(
																										'action' => 'delete-menu-item',
																										'menu-item' => $item_id,
																									),
																									admin_url( 'nav-menus.php' )
																								), 'delete-menu_item_' . $item_id
																							)
																						);
																						?>
					"><?php esc_html_e( 'Remove' ); ?></a> <span class="meta-sep hide-if-no-js"> | </span> <a class="item-cancel submitcancel hide-if-no-js" id="cancel-<?php echo esc_attr( $item_id ); ?>" href="
					<?php
					echo esc_url(
						add_query_arg(
							array(
								'edit-menu-item' => $item_id,
								'cancel' => time(),
							), admin_url( 'nav-menus.php' )
						)
					);
					?>
						#menu-item-settings-<?php echo esc_attr( $item_id ); ?>"><?php esc_html_e( 'Cancel' ); ?></a>
				</div>

				<input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item_id ); ?>" />
				<input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->object_id ); ?>" />
				<input class="menu-item-data-object" type="hidden" name="menu-item-object[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->object ); ?>" />
				<input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->menu_item_parent ); ?>" />
				<input class="menu-item-data-position" type="hidden" name="menu-item-position[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->menu_order ); ?>" />
				<input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->type ); ?>" />
			</div><!-- .menu-item-settings-->
			<ul class="menu-item-transport"></ul>
		<?php
		$output .= ob_get_clean();
	}

} // Sidebar_Walker_Nav_Menu_Edit
