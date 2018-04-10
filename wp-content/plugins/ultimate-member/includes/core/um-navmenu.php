<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'UM_Menu_Item_Custom_Fields_Editor' ) ) {


	/**
	 * Class UM_Menu_Item_Custom_Fields_Editor
	 */
	class UM_Menu_Item_Custom_Fields_Editor {


		/**
		 * @var array
		 */
		protected static $fields = array();


		/**
		 * Initialize plugin
		 */
		public static function init() {

			self::$fields = array(
				'um_nav_public' => __( 'Display Mode' ),
				'um_nav_roles'  => __( 'By Role' )
			);

			add_action( 'wp_update_nav_menu_item', array( __CLASS__, '_save' ), 10, 3 );
			//add_filter( 'manage_nav-menus_columns', array( __CLASS__, '_columns' ), 99 );

			add_action( 'load-nav-menus.php', array( __CLASS__, 'enqueue_nav_menus_scripts' ) );
			add_action( 'admin_footer-nav-menus.php', array( __CLASS__, '_wp_template' ) );
		}


		/**
		 * @param $menu_id
		 * @param $menu_item_db_id
		 * @param $menu_item_args
		 */
		public static function _save( $menu_id, $menu_item_db_id, $menu_item_args ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return;
			}

			if ( empty( $_POST['menu-item-db-id'] ) || ! in_array( $menu_item_db_id, $_POST['menu-item-db-id'] ) ) {
				return;
			}

			foreach ( self::$fields as $_key => $label ) {

				$key = sprintf( 'menu-item-%s', $_key );

				// Sanitize
				if ( ! empty( $_POST[ $key ][ $menu_item_db_id ] ) ) {
					// Do some checks here...
					$value = $_POST[ $key ][ $menu_item_db_id ];
				} else {
					$value = null;
				}

				// Update
				if ( ! is_null( $value ) ) {
					update_post_meta( $menu_item_db_id, $key, $value );
				} else {
					delete_post_meta( $menu_item_db_id, $key );
				}
			}
		}


		/**
		 * @param $columns
		 *
		 * @return array
		 */
		public static function _columns( $columns ) {
			$columns = array_merge( $columns, self::$fields );

			return $columns;
		}


		/**
		 *
		 */
		public static function enqueue_nav_menus_scripts() {
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
		}


		/**
		 *
		 */
		public static function admin_enqueue_scripts() {
			UM()->admin_enqueue()->load_nav_manus_scripts();

			$menus = get_posts( 'post_type=nav_menu_item&numberposts=-1' );
			$menu_restriction_data = array();

			foreach ( $menus as $data ) {
				$_nav_roles_meta = get_post_meta( $data->ID, 'menu-item-um_nav_roles', true );

				$um_nav_roles = array();
				if ( $_nav_roles_meta ) {
					foreach ( $_nav_roles_meta as $key => $value ) {
						if ( is_int( $key ) ) {
							$um_nav_roles[] = $value;
						}
					}
				}

				$menu_restriction_data[ $data->ID ] = array(
					'um_nav_public' => get_post_meta( $data->ID, 'menu-item-um_nav_public', true ),
					'um_nav_roles'  => $um_nav_roles,
				);
			}
			wp_localize_script( 'um_admin_nav_manus', 'um_menu_restriction_data', $menu_restriction_data );
		}


		/**
		 *
		 */
		public static function _wp_template() {
			?>
			<script type="text/html" id="tmpl-um-nav-menus-fields">
				<div class="um-nav-edit">

					<div class="clear"></div>

					<h4 style="margin-bottom: 0.6em;"><?php _e( "Ultimate Member Menu Settings", 'ultimate-member' ) ?></h4>

					<p class="description description-wide um-nav-mode">
						<label for="edit-menu-item-um_nav_public-{{data.menuItemID}}">
							<?php _e( "Who can see this menu link?", 'ultimate-member' ); ?><br/>
							<select id="edit-menu-item-um_nav_public-{{data.menuItemID}}"
							        name="menu-item-um_nav_public[{{data.menuItemID}}]" style="width:100%;">
								<option value="0" <# if( data.restriction_data.um_nav_public == '0' ){ #>selected="selected"<# } #>>
									<?php _e( 'Everyone', 'ultimate-member' ) ?>
								</option>
								<option value="1" <# if( data.restriction_data.um_nav_public == '1' ){ #>selected="selected"<# } #>>
									<?php _e( 'Logged Out Users', 'ultimate-member' ) ?>
								</option>
								<option value="2" <# if( data.restriction_data.um_nav_public == '2' ){ #>selected="selected"<# } #>>
									<?php _e( 'Logged In Users', 'ultimate-member' ) ?>
								</option>
							</select>
						</label>
					</p>
					<p class="description description-wide um-nav-roles" <# if( data.restriction_data.um_nav_public == '2' ){ #>style="display: block;"<# } #>>
						<?php _e( "Select the member roles that can see this link", 'ultimate-member' ) ?><br/>

						<?php $options = UM()->roles()->get_roles();
						$i = 0;
						$html = '';
						$columns = 2;
						while ( $i < $columns ) {
							$per_page = ceil( count( $options ) / $columns );
							$section_fields_per_page = array_slice( $options, $i * $per_page, $per_page );
							$html .= '<span class="um-form-fields-section" style="width:' . floor( 100 / $columns ) . '% !important;">';

							foreach ( $section_fields_per_page as $k => $title ) {
								$id_attr = ' id="edit-menu-item-um_nav_roles-{{data.menuItemID}}_' . $k . '" ';
								$for_attr = ' for="edit-menu-item-um_nav_roles-{{data.menuItemID}}_' . $k . '" ';
								$html .= "<label $for_attr>
		                            <input type='checkbox' {$id_attr} name='menu-item-um_nav_roles[{{data.menuItemID}}][]' value='{$k}' <# if( _.contains( data.restriction_data.um_nav_roles,'{$k}' ) ){ #>checked='checked'<# } #> />
		                            <span>{$title}</span>
		                        </label>";
							}

							$html .= '</span>';
							$i++;
						}

						echo $html; ?>
					</p>

					<div class="clear"></div>
				</div>
			</script>
			<?php
		}

	}

}

UM_Menu_Item_Custom_Fields_Editor::init();