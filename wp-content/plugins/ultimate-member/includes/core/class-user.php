<?php
namespace um\core;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'um\core\User' ) ) {


	/**
	 * Class User
	 * @package um\core
	 */
	class User {


		/**
		 * User constructor.
		 */
		function __construct() {

			$this->id = 0;
			$this->usermeta = null;
			$this->data = null;
			$this->profile = null;
			$this->cannot_edit = null;
			$this->tabs = null;

			$this->banned_keys = array(
				'metabox','postbox','meta-box',
				'dismissed_wp_pointers', 'session_tokens',
				'screen_layout', 'wp_user-', 'dismissed',
				'cap_key', 'wp_capabilities',
				'managenav', 'nav_menu','user_activation_key',
				'level_', 'wp_user_level'
			);

			add_action( 'init',  array( &$this, 'set' ), 1 );

			$this->preview = false;

			// a list of keys that should never be in wp_usermeta
			$this->update_user_keys = array(
				'user_email',
				'user_pass',
				'user_password',
				'display_name',
				'user_url',
				'role',
			);

			$this->target_id = null;

			// When the cache should be cleared
			add_action('um_delete_user_hook', array(&$this, 'remove_cached_queue') );
			add_action('um_after_user_status_is_changed_hook', array(&$this, 'remove_cached_queue') );

			// When user cache should be cleared
			add_action('um_after_user_updated', array(&$this, 'remove_cache') );
			add_action('um_after_user_account_updated', array(&$this, 'remove_cache') );
			add_action('personal_options_update', array(&$this, 'remove_cache') );
			//add_action('edit_user_profile_update', array(&$this, 'remove_cache') );
			add_action('um_when_role_is_set', array(&$this, 'remove_cache') );
			add_action('um_when_status_is_set', array(&$this, 'remove_cache') );

			add_action( 'show_user_profile', array( $this, 'profile_form_additional_section' ), 10 );
			add_action( 'user_new_form', array( $this, 'profile_form_additional_section' ), 10 );
			add_action( 'edit_user_profile', array( $this, 'profile_form_additional_section' ), 10 );
			add_filter( 'um_user_profile_additional_fields', array( $this, 'secondary_role_field' ), 1, 2 );

			//on every update of user profile (hook from wp_update_user)
			add_action( 'profile_update', array( &$this, 'profile_update' ), 10, 2 ); // user_id and old_user_data

			//on user update profile page
			//add_action( 'edit_user_profile_update', array( &$this, 'profile_update' ), 10, 1 );

			add_action( 'user_register', array( &$this, 'user_register_via_admin' ), 10, 1 );
			add_action( 'user_register', array( &$this, 'set_gravatar' ), 11, 1 );


			add_action( 'added_existing_user', array( &$this, 'add_um_role_existing_user' ), 10, 2 );
			add_action( 'wpmu_activate_user', array( &$this, 'add_um_role_wpmu_new_user' ), 10, 1 );

			add_action( 'init', array( &$this, 'check_membership' ), 10 );
		}


		/**
		 *
		 */
		function check_membership() {
			if ( ! is_user_logged_in() )
				return;

			um_fetch_user( get_current_user_id() );
			$status = um_user( 'account_status' );

			if ( 'rejected' == $status ) {
				wp_logout();
				session_unset();
				exit( wp_redirect( um_get_core_page( 'login' ) ) );
			}

			um_reset_user();
		}


		/**
		 * Multisite add existing user
		 *
		 * @param $user_id
		 * @param $result
		 */
		function add_um_role_existing_user( $user_id, $result ) {
			// Bail if no user ID was passed
			if ( empty( $user_id ) )
				return;

			if ( ! empty( $_POST['um-role'] ) ) {
				if ( ! user_can( $user_id, $_POST['um-role'] ) ) {
					UM()->roles()->set_role( $user_id, $_POST['um-role'] );
				}
			}

			$this->remove_cache( $user_id );
		}


		/**
		 * Multisite add existing user
		 *
		 * @param $user_id
		 */
		function add_um_role_wpmu_new_user( $user_id ) {
			// Bail if no user ID was passed
			if ( empty( $user_id ) )
				return;

			if ( ! empty( $_POST['um-role'] ) ) {
				if ( ! user_can( $user_id, $_POST['um-role'] ) ) {
					UM()->roles()->set_role( $user_id, $_POST['um-role'] );
				}
			}

			$this->remove_cache( $user_id );
		}


		/**
		 * Get pending users (in queue)
		 */
		function get_pending_users_count() {

			$cached_users_queue = get_option( 'um_cached_users_queue' );
			if ( $cached_users_queue > 0 && ! isset( $_REQUEST['delete_count'] ) ){
				return $cached_users_queue;
			}

			$args = array( 'fields' => 'ID', 'number' => 1 );
			$args['meta_query']['relation'] = 'OR';
			$args['meta_query'][] = array(
				'key' => 'account_status',
				'value' => 'awaiting_email_confirmation',
				'compare' => '='
			);
			$args['meta_query'][] = array(
				'key' => 'account_status',
				'value' => 'awaiting_admin_review',
				'compare' => '='
			);

			/**
			 * UM hook
			 *
			 * @type filter
			 * @title um_admin_pending_queue_filter
			 * @description Change user query arguments when get pending users
			 * @input_vars
			 * [{"var":"$args","type":"array","desc":"WP_Users query arguments"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage
			 * <?php add_filter( 'um_admin_pending_queue_filter', 'function_name', 10, 1 ); ?>
			 * @example
			 * <?php
			 * add_filter( 'um_admin_pending_queue_filter', 'my_admin_pending_queue', 10, 1 );
			 * function my_admin_pending_queue( $args ) {
			 *     // your code here
			 *     return $args;
			 * }
			 * ?>
			 */
			$args = apply_filters( 'um_admin_pending_queue_filter', $args );
			$users = new \WP_User_Query( $args );

			delete_option( 'um_cached_users_queue' );
			add_option( 'um_cached_users_queue', $users->get_total(), '', 'no' );

			return $users->get_total();
		}


		/**
		 * @param $user_id
		 *
		 * @return bool|mixed
		 */
		function get_profile_slug( $user_id ) {
			// Permalink base
			$permalink_base = UM()->options()->get( 'permalink_base' );
			$profile_slug  = get_user_meta( $user_id, "um_user_profile_url_slug_{$permalink_base}", true );

			//get default username permalink if it's empty then return false
			if ( empty( $profile_slug ) ) {
				if ( $permalink_base != 'user_login' ) {
					$profile_slug = get_user_meta( $user_id, "um_user_profile_url_slug_user_login", true );
				}

				if ( empty( $profile_slug ) ) {
					return false;
				}
			}

			return $profile_slug;
		}


		/**
		 * @param $user_id
		 *
		 * @return bool|string
		 */
		function get_profile_link( $user_id ) {
			$profile_slug = $this->get_profile_slug( $user_id );

			if ( empty( $profile_slug ) ) {
				return false;
			}

			return UM()->permalinks()->profile_permalink( $profile_slug );
		}


		/**
		 * @param $user_id
		 */
		function generate_profile_slug( $user_id, $force = false ) {
			$userdata = get_userdata( $user_id );

			if ( empty( $userdata ) ) {
				return;
			}

			delete_option( "um_cache_userdata_{$user_id}" );

			$current_profile_slug = $this->get_profile_slug( $user_id );

			$user_in_url = '';
			$permalink_base = UM()->options()->get( 'permalink_base' );

			// User ID
			if ( $permalink_base == 'user_id' ) {
				$user_in_url = $user_id;
			}

			// Username
			if ( $permalink_base == 'user_login' ) {

				$user_in_url = $userdata->user_login;

				if ( is_email( $user_in_url ) ) {

					$user_email  = $user_in_url;
					$user_in_url = str_replace( '@', '', $user_in_url );

					if ( ( $pos = strrpos( $user_in_url, '.' ) ) !== false ) {
						$search_length = strlen( '.' );
						$user_in_url   = substr_replace( $user_in_url, '-', $pos, $search_length );
					}
					update_user_meta( $user_id, "um_email_as_username_{$user_in_url}", $user_email );

				} else {

					$user_in_url = sanitize_title( $user_in_url );

				}
			}

			// Fisrt and Last name
			$full_name_permalinks = array( 'name', 'name_dash', 'name_plus' );
			if ( in_array( $permalink_base, $full_name_permalinks ) ) {
				$separated = array( 'name' => '.', 'name_dash' => '-', 'name_plus' => '+' );
				$separate = $separated[ $permalink_base ];
				$first_name = $userdata->first_name;
				$last_name  = $userdata->last_name;
				$full_name  = trim( sprintf( '%s %s', $first_name, $last_name ) );
				$full_name = preg_replace( '/\s+/', ' ', $full_name ); // Remove double spaces
				$profile_slug = UM()->permalinks()->profile_slug( $full_name, $first_name, $last_name );

				$append     = 0;
				$username   = $full_name;
				$_username   = $full_name;

				while ( 1 ) {
					$username = $_username . ( empty( $append ) ? '' : " $append" );
					$slug_exists_user_id = UM()->permalinks()->slug_exists_user_id( $profile_slug . ( empty( $append ) ? '' : "{$separate}{$append}" ) );
					if ( empty( $slug_exists_user_id ) || $user_id == $slug_exists_user_id ) {
						break;
					}
					$append++;
				}

				$user_in_url = UM()->permalinks()->profile_slug( $username, $first_name, $last_name );
				if ( empty( $user_in_url ) ) {
					$user_in_url = $userdata->user_login;

					if ( is_email( $user_in_url ) ) {

						$user_email  = $user_in_url;
						$user_in_url = str_replace( '@', '', $user_in_url );

						if ( ( $pos = strrpos( $user_in_url, '.' ) ) !== false ) {
							$search_length = strlen( '.' );
							$user_in_url   = substr_replace( $user_in_url, '-', $pos, $search_length );
						}
						update_user_meta( $user_id, "um_email_as_username_{$user_in_url}", $user_email );

					} else {

						$user_in_url = sanitize_title( $user_in_url );

					}
				}

				$user_in_url = trim( $user_in_url, $separate );
			}

			if ( $force || empty( $current_profile_slug ) || $current_profile_slug != $user_in_url ) {
				update_user_meta( $user_id, "um_user_profile_url_slug_{$permalink_base}", $user_in_url );
			}
		}


		/**
		 * Backend user creation
		 *
		 * @param $user_id
		 */
		function user_register_via_admin( $user_id ) {

			if ( empty( $user_id ) )
				return;

			if ( is_admin() ) {
				//if there custom 2 role not empty
				if ( ! empty( $_POST['um-role'] ) ) {
					$user = get_userdata( $user_id );
					$user->add_role( $_POST['um-role'] );
					UM()->user()->profile['role'] = $_POST['um-role'];
					UM()->user()->update_usermeta_info( 'role' );
				}

				/**
				 * UM hook
				 *
				 * @type action
				 * @title um_user_register
				 * @description Action on user registration
				 * @input_vars
				 * [{"var":"$user_id","type":"int","desc":"User ID"},
				 * {"var":"$submitted","type":"array","desc":"Registration form submitted"}]
				 * @change_log
				 * ["Since: 2.0"]
				 * @usage add_action( 'um_user_register', 'function_name', 10, 2 );
				 * @example
				 * <?php
				 * add_action( 'um_user_register', 'my_user_register', 10, 2 );
				 * function my_user_register( $user_id, $submitted ) {
				 *     // your code here
				 * }
				 * ?>
				 */
				do_action( 'um_user_register', $user_id, $_POST );
			}

		}


		/**
		 * On wp_update_user function complete
		 *
		 * @param int $user_id
		 * @param \WP_User $old_data
		 */
		function profile_update( $user_id, $old_data ) {
			// Bail if no user ID was passed
			if ( empty( $user_id ) ) {
				return;
			}

			$old_roles = $old_data->roles;
			$userdata  = get_userdata( $user_id );
			$new_roles = $userdata->roles;

			if ( ! empty( $_POST['um-role'] ) ) {
				$new_roles = array_merge( $new_roles, array( $_POST['um-role'] ) );
				if ( ! user_can( $user_id, $_POST['um-role'] ) ) {
					UM()->roles()->set_role( $user_id, $_POST['um-role'] );
				}
			}

			/**
			 * UM hook
			 *
			 * @type action
			 * @title um_after_member_role_upgrade
			 * @description Action on user registration
			 * @input_vars
			 * [{"var":"$new_roles","type":"array","desc":"User new roles"},
			 * {"var":"$old_roles","type":"array","desc":"Old roles"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'um_after_member_role_upgrade', 'function_name', 10, 2 );
			 * @example
			 * <?php
			 * add_action( 'um_after_member_role_upgrade', 'my_after_member_role_upgrade', 10, 2 );
			 * function my_after_member_role_upgrade( $new_roles, $old_roles ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'um_after_member_role_upgrade', $new_roles, $old_roles );

			//Update permalink
			$this->generate_profile_slug( $user_id, true );

			$this->remove_cache( $user_id );
		}


		/**
		 * Additional section for WP Profile page with UM data fields
		 *
		 * @param \WP_User $userdata User data
		 * @return void
		 */
		function profile_form_additional_section( $userdata ) {

			/**
			 * UM hook
			 *
			 * @type filter
			 * @title um_user_profile_additional_fields
			 * @description Make additional content section
			 * @input_vars
			 * [{"var":"$content","type":"array","desc":"Additional section content"},
			 * {"var":"$userdata","type":"array","desc":"Userdata"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage
			 * <?php add_filter( 'um_user_profile_additional_fields', 'function_name', 10, 2 ); ?>
			 * @example
			 * <?php
			 * add_filter( 'um_user_profile_additional_fields', 'my_admin_pending_queue', 10, 2 );
			 * function my_admin_pending_queue( $content, $userdata ) {
			 *     // your code here
			 *     return $content;
			 * }
			 * ?>
			 */
			$section_content = apply_filters( 'um_user_profile_additional_fields', '', $userdata );

			if ( ! empty( $section_content ) && ! ( is_multisite() && is_network_admin() ) ) {

				if ( $userdata !== 'add-new-user' && $userdata !== 'add-existing-user' ) { ?>
					<h3><?php esc_html_e( 'Ultimate Member', 'ultimate-member' ); ?></h3>
				<?php }

				echo $section_content;
			}
		}


		/**
		 * Default interface for setting a ultimatemember role
		 *
		 * @param string $content Section HTML
		 * @param \WP_User $userdata User data
		 * @return string
		 */
		public function secondary_role_field( $content, $userdata ) {
			$roles = array();

			$role_keys = get_option( 'um_roles' );
			if ( $role_keys ) {
				foreach ( $role_keys as $role_key ) {
					$role_meta = get_option( "um_role_{$role_key}_meta" );

					if ( $role_meta ) {
						//$role_meta['name'] = 'UM ' . $role_meta['name'];
						$roles['um_' . $role_key] = $role_meta;
					}
				}
			}

			if ( empty( $roles ) )
				return $content;

			global $pagenow;
			if ( 'profile.php' == $pagenow )
				return $content;

			$style = '';
			$user_role = false;
			if ( $userdata !== 'add-new-user' && $userdata !== 'add-existing-user' ) {
				// Bail if current user cannot edit users
				if ( ! current_user_can( 'edit_user', $userdata->ID ) )
					return $content;

				$user_role = UM()->roles()->get_um_user_role( $userdata->ID );
				if ( $user_role && ! empty( $userdata->roles ) && count( $userdata->roles ) == 1 )
					$style = 'style="display:none;"';

			}

			$class = ( $userdata == 'add-existing-user' ) ? 'um_role_existing_selector_wrapper' : 'um_role_selector_wrapper';

			ob_start(); ?>

			<div id="<?php echo $class ?>" <?php echo $style ?>>
				<table class="form-table">
					<tbody>
					<tr>
						<th><label for="um-role"><?php esc_html_e( 'Ultimate Member Role', 'ultimate-member' ); ?></label></th>
						<td>
							<select name="um-role" id="um-role">
								<option value="" <?php selected( empty( $user_role ) ) ?>><?php esc_html_e( '&mdash; No role for Ultimate Member &mdash;', 'ultimate-member' ); ?></option>
								<?php foreach ( $roles as $role_id => $details ) { ?>
									<option <?php selected( $user_role, $role_id ); ?> value="<?php echo esc_attr( $role_id ); ?>"><?php echo $details['name']; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					</tbody>
				</table>
			</div>

			<?php $content .= ob_get_clean();

			return $content;
		}


		/**
		 * Remove cached queue from Users backend
		 */
		function remove_cached_queue() {
			delete_option( 'um_cached_users_queue' );
		}


		/**
		 * Converts object to array
		 *
		 * @param $obj
		 *
		 * @return array
		 */
		function toArray( $obj ) {
			if ( is_object( $obj ) ) $obj = (array)$obj;
			if ( is_array( $obj ) ) {
				$new = array();
				foreach ( $obj as $key => $val ) {
					$new[ $key ] = $this->toArray( $val );
				}
			} else {
				$new = $obj;
			}

			return $new;
		}


		/**
		 * @param $user_id
		 *
		 * @return mixed|string|void
		 */
		function get_cached_data( $user_id ) {

			$disallow_cache = UM()->options()->get( 'um_profile_object_cache_stop' );
			if( $disallow_cache ){
				return '';
			}

			if ( is_numeric( $user_id ) && $user_id > 0 ) {
				$find_user = get_option("um_cache_userdata_{$user_id}");
				if ( $find_user ) {
					/**
					 * UM hook
					 *
					 * @type filter
					 * @title um_user_permissions_filter
					 * @description Change User Permissions
					 * @input_vars
					 * [{"var":"$permissions","type":"array","desc":"User Permissions"},
					 * {"var":"$user_id","type":"int","desc":"User ID"}]
					 * @change_log
					 * ["Since: 2.0"]
					 * @usage
					 * <?php add_filter( 'um_user_permissions_filter', 'function_name', 10, 2 ); ?>
					 * @example
					 * <?php
					 * add_filter( 'um_user_permissions_filter', 'my_user_permissions', 10, 2 );
					 * function my_user_permissions( $permissions, $user_id ) {
					 *     // your code here
					 *     return $permissions;
					 * }
					 * ?>
					 */
					$find_user = apply_filters( 'um_user_permissions_filter', $find_user, $user_id );
					return $find_user;
				}
			}
			return '';
		}


		/**
		 * @param $user_id
		 * @param $profile
		 */
		function setup_cache( $user_id, $profile ) {

			$disallow_cache = UM()->options()->get( 'um_profile_object_cache_stop' );
			if ( $disallow_cache ) {
				return;
			}

			update_option( "um_cache_userdata_{$user_id}", $profile, false );
		}


		/**
		 * @param $user_id
		 */
		function remove_cache( $user_id ) {
			delete_option( "um_cache_userdata_{$user_id}" );
		}


		/**
		 * Remove cache for all users
		 */
		function remove_cache_all_users() {
			global $wpdb;
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'um_cache_userdata_%'" );
		}


		/**
		 * This method lets you set a user. For example, to retrieve a profile or anything related to that user.
		 *
		 * @usage <?php UM()->user()->set( $user_id, $clean = false ); ?>
		 *
		 * @param null|int $user_id Which user to retrieve. A numeric user ID
		 * @param bool $clean Should be true or false. Basically, if you did not provide a user ID It will set the current logged in user as a profile
		 *
		 * @example The following example makes you set a user and retrieve their display name after that using the user API.

		<?php

		UM()->user()->set( 12 );
		$display_name = UM()->user()->profile['display_name']; // Should print user display name

		?>
		 *
		 */
		function set( $user_id = null, $clean = false ) {
			if ( isset( $this->profile ) ) {
				unset( $this->profile );
			}

			if ( $user_id ) {
				$this->id = $user_id;
			} elseif ( is_user_logged_in() && $clean == false ) {
				$this->id = get_current_user_id();
			} else {
				$this->id = 0;
			}

			if ( $this->get_cached_data( $this->id ) ) {
				$this->profile = $this->get_cached_data( $this->id );
			} else {

				if ( $user_id ) {

					$this->id = $user_id;
					$this->usermeta = get_user_meta( $user_id );
					$this->data = get_userdata( $this->id );

				} elseif ( is_user_logged_in() && $clean == false ) {

					$this->id = get_current_user_id();
					$this->usermeta = get_user_meta($this->id);
					$this->data = get_userdata($this->id);

				} else {

					$this->id = 0;
					$this->usermeta = null;
					$this->data = null;

				}

				// we have a user, populate a profile
				if ( $this->id && $this->toArray( $this->data ) ) {

					// add user data
					$this->data = $this->toArray( $this->data );

					foreach ( $this->data as $k=>$v ) {
						if ( $k == 'roles') {
							$this->profile['wp_roles'] = implode(',',$v);
						} else if ( is_array( $v ) ) {
							foreach($v as $k2 => $v2){
								$this->profile[$k2] = $v2;
							}
						} else {
							$this->profile[$k] = $v;
						}
					}

					// add account status
					if ( !isset( $this->usermeta['account_status'][0] ) )  {
						$this->usermeta['account_status'][0] = 'approved';
					}

					if ( $this->usermeta['account_status'][0] == 'approved' ) {
						$this->usermeta['account_status_name'][0] = __('Approved','ultimate-member');
					}

					if ( $this->usermeta['account_status'][0] == 'awaiting_email_confirmation' ) {
						$this->usermeta['account_status_name'][0] = __('Awaiting E-mail Confirmation','ultimate-member');
					}

					if ( $this->usermeta['account_status'][0] == 'awaiting_admin_review' ) {
						$this->usermeta['account_status_name'][0] = __('Pending Review','ultimate-member');
					}

					if ( $this->usermeta['account_status'][0] == 'rejected' ) {
						$this->usermeta['account_status_name'][0] = __('Membership Rejected','ultimate-member');
					}

					if ( $this->usermeta['account_status'][0] == 'inactive' ) {
						$this->usermeta['account_status_name'][0] = __('Membership Inactive','ultimate-member');
					}

					// add user meta
					foreach( $this->usermeta as $k=>$v ) {
						if ( $k == 'display_name') continue;
						$this->profile[$k] = $v[0];
					}

					// add permissions
					$user_role = UM()->roles()->get_priority_user_role( $this->id );
					$this->profile['role'] = $user_role;
					$this->profile['roles'] = UM()->roles()->get_all_user_roles( $this->id );

					$role_meta = UM()->roles()->role_data( $user_role );
					/**
					 * UM hook
					 *
					 * @type filter
					 * @title um_user_permissions_filter
					 * @description Change User Permissions
					 * @input_vars
					 * [{"var":"$permissions","type":"array","desc":"User Permissions"},
					 * {"var":"$user_id","type":"int","desc":"User ID"}]
					 * @change_log
					 * ["Since: 2.0"]
					 * @usage
					 * <?php add_filter( 'um_user_permissions_filter', 'function_name', 10, 2 ); ?>
					 * @example
					 * <?php
					 * add_filter( 'um_user_permissions_filter', 'my_user_permissions', 10, 2 );
					 * function my_user_permissions( $permissions, $user_id ) {
					 *     // your code here
					 *     return $permissions;
					 * }
					 * ?>
					 */
					$role_meta = apply_filters( 'um_user_permissions_filter', $role_meta, $this->id );

					/*$role_meta = array_map( function( $key, $item ) {
                        if ( strpos( $key, '_um_' ) === 0 )
                            $key = str_replace( '_um_', '', $key );

                        return array( $key => $item );
                    }, array_keys( $role_meta ), $role_meta );*/

					$this->profile = array_merge( $this->profile, (array)$role_meta );

					$this->profile['super_admin'] = ( is_super_admin( $this->id ) ) ? 1 : 0;

					// clean profile
					$this->clean();

					// Setup cache
					$this->setup_cache( $this->id, $this->profile );

				}

			}

		}


		/**
		 * Reset user data
		 *
		 * @param bool $clean
		 */
		function reset( $clean = false ){
			$this->set(0, $clean);
		}


		/**
		 * Clean user profile
		 */
		function clean() {
			foreach($this->profile as $key => $value){
				foreach($this->banned_keys as $ban){
					if (strstr($key, $ban) || is_numeric($key) )
						unset($this->profile[$key]);
				}
			}
		}


		/**
		 * This method lets you auto sign-in a user to your site.
		 *
		 * @usage <?php UM()->user()->auto_login( $user_id, $rememberme = false ); ?>
		 *
		 * @param int $user_id Which user ID to sign in automatically
		 * @param int|bool $rememberme Should be true or false. If you want the user sign in session to use cookies, use true
		 *
		 * @example The following example lets you sign in a user automatically by their ID.

		<?php UM()->user()->auto_login( 2 ); ?>
		 *
		 *
		 * @example The following example lets you sign in a user automatically by their ID and makes the plugin remember their session.

		<?php UM()->user()->auto_login( 10, true ); ?>
		 *
		 */
		function auto_login( $user_id, $rememberme = 0 ) {

			wp_set_current_user( $user_id );

			wp_set_auth_cookie( $user_id, $rememberme );

			$user = get_user_by('ID', $user_id );

			do_action( 'wp_login', $user->user_login, $user );

		}


		/**
		 * Set user's registration details
		 *
		 * @param $submitted
		 */
		function set_registration_details( $submitted ) {

			if ( isset( $submitted['user_pass'] ) ) {
				unset( $submitted['user_pass'] );
			}

			if ( isset( $submitted['user_password'] ) ) {
				unset( $submitted['user_password'] );
			}

			if ( isset( $submitted['confirm_user_password'] ) ) {
				unset( $submitted['confirm_user_password'] );
			}

			/**
			 * UM hook
			 *
			 * @type filter
			 * @title um_before_save_filter_submitted
			 * @description Change submitted data before save usermeta "submitted" on registration process
			 * @input_vars
			 * [{"var":"$submitted","type":"array","desc":"Submitted data"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage
			 * <?php add_filter( 'um_before_save_filter_submitted', 'function_name', 10, 1 ); ?>
			 * @example
			 * <?php
			 * add_filter( 'um_before_save_filter_submitted', 'my_before_save_filter_submitted', 10, 1 );
			 * function my_before_save_filter_submitted( $submitted ) {
			 *     // your code here
			 *     return $submitted;
			 * }
			 * ?>
			 */
			$submitted = apply_filters( 'um_before_save_filter_submitted', $submitted );

			/**
			 * UM hook
			 *
			 * @type action
			 * @title um_before_save_registration_details
			 * @description Action on user registration before save details
			 * @input_vars
			 * [{"var":"$user_id","type":"int","desc":"User ID"},
			 * {"var":"$submitted","type":"array","desc":"Registration form submitted"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'um_before_save_registration_details', 'function_name', 10, 2 );
			 * @example
			 * <?php
			 * add_action( 'um_before_save_registration_details', 'my_before_save_registration_details', 10, 2 );
			 * function my_before_save_registration_details( $user_id, $submitted ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'um_before_save_registration_details', $this->id, $submitted );

			update_user_meta( $this->id, 'submitted', $submitted );

			$this->update_profile( $submitted );
			/**
			 * UM hook
			 *
			 * @type action
			 * @title um_after_save_registration_details
			 * @description Action on user registration after save details
			 * @input_vars
			 * [{"var":"$user_id","type":"int","desc":"User ID"},
			 * {"var":"$submitted","type":"array","desc":"Registration form submitted"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'um_after_save_registration_details', 'function_name', 10, 2 );
			 * @example
			 * <?php
			 * add_action( 'um_after_save_registration_details', 'my_after_save_registration_details', 10, 2 );
			 * function my_after_save_registration_details( $user_id, $submitted ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'um_after_save_registration_details', $this->id, $submitted );

		}


		/**
		 * Set last login for new registered users
		 */
		function set_last_login() {
			update_user_meta(  $this->id, '_um_last_login', current_time( 'timestamp' ) );
		}


		/**
		 * Set user's account status
		 *
		 * @param $status
		 */
		function set_status( $status ) {

			/**
			 * UM hook
			 *
			 * @type action
			 * @title um_when_status_is_set
			 * @description Action on user status changed
			 * @input_vars
			 * [{"var":"$user_id","type":"int","desc":"User ID"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'um_when_status_is_set', 'function_name', 10, 1 );
			 * @example
			 * <?php
			 * add_action( 'um_when_status_is_set', 'my_when_status_is_set', 10, 1 );
			 * function my_when_status_is_set( $user_id ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'um_when_status_is_set', um_user( 'ID' ) );

			$this->profile['account_status'] = $status;

			$this->update_usermeta_info( 'account_status' );

			/**
			 * UM hook
			 *
			 * @type action
			 * @title um_after_user_status_is_changed_hook
			 * @description Action after user status changed
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'um_after_user_status_is_changed_hook', 'function_name', 10 );
			 * @example
			 * <?php
			 * add_action( 'um_after_user_status_is_changed_hook', 'my_after_user_status_is_changed', 10 );
			 * function my_after_user_status_is_changed() {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'um_after_user_status_is_changed_hook' );

			/**
			 * UM hook
			 *
			 * @type action
			 * @title um_after_user_status_is_changed
			 * @description Action after user status changed
			 * @input_vars
			 * [{"var":"$status","type":"string","desc":"User Status"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'um_after_user_status_is_changed', 'function_name', 10, 1 );
			 * @example
			 * <?php
			 * add_action( 'um_after_user_status_is_changed', 'my_after_user_status_is_changed', 10, 1 );
			 * function my_after_user_status_is_changed( $status ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'um_after_user_status_is_changed', $status );

		}


		/**
		 * Set user's hash for password reset
		 */
		function password_reset_hash() {
			$this->profile['reset_pass_hash'] = UM()->validation()->generate();
			$this->update_usermeta_info('reset_pass_hash');
		}


		/**
		 * Set user's hash
		 */
		function assign_secretkey() {
			/**
			 * UM hook
			 *
			 * @type action
			 * @title um_before_user_hash_is_changed
			 * @description Action before user hash is changed
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'um_before_user_hash_is_changed', 'function_name', 10 );
			 * @example
			 * <?php
			 * add_action( 'um_before_user_hash_is_changed', 'my_before_user_hash_is_changed', 10 );
			 * function my_before_user_hash_is_changed() {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'um_before_user_hash_is_changed' );

			$this->profile['account_secret_hash'] = UM()->validation()->generate();
			$this->update_usermeta_info( 'account_secret_hash' );
			/**
			 * UM hook
			 *
			 * @type action
			 * @title um_after_user_hash_is_changed
			 * @description Action after user hash is changed
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'um_after_user_hash_is_changed', 'function_name', 10 );
			 * @example
			 * <?php
			 * add_action( 'um_after_user_hash_is_changed', 'my_after_user_hash_is_changed', 10 );
			 * function my_after_user_hash_is_changed() {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'um_after_user_hash_is_changed' );
		}


		/**
		 * Password reset email
		 */
		function password_reset() {
			$this->password_reset_hash();
			UM()->mail()->send( um_user('user_email'), 'resetpw_email' );
		}


		/**
		 * Password changed email
		 */
		function password_changed(){
			UM()->mail()->send( um_user('user_email'), 'changedpw_email' );
		}


		/**
		 * This method approves a user membership and sends them an optional welcome/approval e-mail.
		 *
		 * @usage <?php UM()->user()->approve(); ?>
		 *
		 * @example Approve a pending user and allow him to sign-in to your site.

		<?php

		um_fetch_user( 352 );
		UM()->user()->approve();

		?>
		 *
		 */
		function approve() {
			$user_id = um_user('ID');
			delete_option( "um_cache_userdata_{$user_id}" );

			if ( um_user('account_status') == 'awaiting_admin_review' ) {
				$this->password_reset_hash();
				UM()->mail()->send( um_user('user_email'), 'approved_email' );

			} else {
				$this->password_reset_hash();
				UM()->mail()->send( um_user('user_email'), 'welcome_email');
			}

			$this->set_status('approved');
			$this->delete_meta('account_secret_hash');
			$this->delete_meta('_um_cool_but_hard_to_guess_plain_pw');

			/**
			 * UM hook
			 *
			 * @type action
			 * @title um_after_user_is_approved
			 * @description Action after user was approved
			 * @input_vars
			 * [{"var":"$user_id","type":"int","desc":"User ID"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'um_after_user_is_approved', 'function_name', 10, 1 );
			 * @example
			 * <?php
			 * add_action( 'um_after_user_is_approved', 'my_after_user_is_approved', 10, 1 );
			 * function my_after_user_hash_is_changed( $user_id ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'um_after_user_is_approved', um_user( 'ID' ) );
		}


		/**
		 * Pending email
		 */
		function email_pending() {
			$this->assign_secretkey();
			$this->set_status('awaiting_email_confirmation');
			UM()->mail()->send( um_user('user_email'), 'checkmail_email' );
		}


		/**
		 * This method puts a user under manual review by administrator and sends them an optional e-mail.
		 *
		 * @usage <?php UM()->user()->pending(); ?>
		 *
		 * @example An example of putting a user pending manual review

		<?php

		um_fetch_user( 54 );
		UM()->user()->pending();

		?>
		 *
		 */
		function pending() {
			$this->set_status( 'awaiting_admin_review' );
			UM()->mail()->send( um_user( 'user_email' ), 'pending_email' );
		}


		/**
		 * This method rejects a user membership and sends them an optional e-mail.
		 *
		 * @usage <?php UM()->user()->reject(); ?>
		 *
		 * @example Reject a user membership example

		<?php

		um_fetch_user( 114 );
		UM()->user()->reject();

		?>

		 *
		 */
		function reject() {
			$this->set_status('rejected');
			UM()->mail()->send( um_user('user_email'), 'rejected_email' );
		}


		/**
		 * This method deactivates a user membership and sends them an optional e-mail.
		 *
		 * @usage <?php UM()->user()->deactivate(); ?>
		 *
		 * @example Deactivate a user membership with the following example

		<?php

		um_fetch_user( 32 );
		$ultimatemember->user->deactivate();

		?>
		 *
		 */
		function deactivate() {
			$this->set_status( 'inactive' );
			/**
			 * UM hook
			 *
			 * @type action
			 * @title um_after_user_is_inactive
			 * @description Action after user was inactive
			 * @input_vars
			 * [{"var":"$user_id","type":"int","desc":"User ID"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'um_after_user_is_inactive', 'function_name', 10, 1 );
			 * @example
			 * <?php
			 * add_action( 'um_after_user_is_inactive', 'my_after_user_is_inactive', 10, 1 );
			 * function my_after_user_is_inactive( $user_id ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'um_after_user_is_inactive', um_user( 'ID' ) );

			UM()->mail()->send( um_user( 'user_email' ), 'inactive_email' );
		}


		/**
		 * Delete user
		 *
		 * @param bool $send_mail
		 */
		function delete( $send_mail = true ) {
			/**
			 * UM hook
			 *
			 * @type action
			 * @title um_delete_user_hook
			 * @description On delete user
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'um_delete_user_hook', 'function_name', 10 );
			 * @example
			 * <?php
			 * add_action( 'um_delete_user_hook', 'my_delete_user', 10 );
			 * function my_delete_user() {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'um_delete_user_hook' );
			/**
			 * UM hook
			 *
			 * @type action
			 * @title um_delete_user
			 * @description On delete user
			 * @input_vars
			 * [{"var":"$user_id","type":"int","desc":"User ID"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'um_delete_user', 'function_name', 10, 1 );
			 * @example
			 * <?php
			 * add_action( 'um_delete_user', 'my_delete_user', 10, 1 );
			 * function my_delete_user( $user_id ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'um_delete_user', um_user( 'ID' ) );

			// send email notifications
			if ( $send_mail ) {
				UM()->mail()->send( um_user( 'user_email' ), 'deletion_email' );

				$emails = um_multi_admin_email();
				if ( ! empty( $emails ) ) {
					foreach ( $emails as $email ) {
						UM()->mail()->send( $email, 'notification_deletion', array( 'admin' => true ) );
					}
				}
			}

			// remove uploads
			UM()->files()->remove_dir( um_user_uploads_dir() );

			// remove user
			if ( is_multisite() ) {

				if ( ! function_exists( 'wpmu_delete_user' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/ms.php' );
				}

				wpmu_delete_user( $this->id );

			} else {

				if ( ! function_exists( 'wp_delete_user' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/user.php' );
				}

				wp_delete_user( $this->id );

			}

		}


		/**
		 * This method gets a user role in slug format. e.g. member
		 *
		 * @usage <?php UM()->user()->get_role(); ?>
		 *
		 * @return string
		 *
		 * @example Do something if the user's role is paid-member

		<?php

		um_fetch_user( 12 );

		if ( UM()->user()->get_role() == 'paid-member' ) {
		// Show this to paid customers
		} else {
		// You are a free member
		}

		?>
		 *
		 */
		function get_role() {
			if ( ! empty( $this->profile['role'] ) ) {
				return $this->profile['role'];
			} else {
				if ( $this->profile['wp_roles'] == 'administrator' ) {
					return 'admin';
				} else {
					return 'member';
				}
			}
		}


		/**
		 * Update one key in user meta
		 *
		 * @param $key
		 */
		function update_usermeta_info( $key ) {
			// delete the key first just in case
			delete_user_meta( $this->id, $key );
			update_user_meta( $this->id, $key, $this->profile[$key] );
		}


		/**
		 * This method can be used to delete user's meta key.
		 *
		 * @usage <?php UM()->user()->delete_meta( $key ); ?>
		 *
		 * @param string $key The meta field key to remove from user
		 *
		 *  @example Delete user's age field

		<?php

		um_fetch_user( 15 );
		UM()->user()->delete_meta( 'age' );

		?>

		 *
		 */
		function delete_meta( $key ){
			delete_user_meta( $this->id, $key );
		}


		/**
		 * Get admin actions for individual user
		 *
		 * @return array|bool
		 */
		function get_admin_actions() {
			$items = array();
			$actions = array();
			/**
			 * UM hook
			 *
			 * @type filter
			 * @title um_admin_user_actions_hook
			 * @description Extend admin actions for each user
			 * @input_vars
			 * [{"var":"$actions","type":"array","desc":"Actions for user"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage
			 * <?php add_filter( 'um_admin_user_actions_hook', 'function_name', 10, 1 ); ?>
			 * @example
			 * <?php
			 * add_filter( 'um_admin_user_actions_hook', 'my_admin_user_actions', 10, 1 );
			 * function my_admin_user_actions( $actions ) {
			 *     // your code here
			 *     return $actions;
			 * }
			 * ?>
			 */
			$actions = apply_filters('um_admin_user_actions_hook', $actions );
			if ( !isset( $actions ) || empty( $actions ) ) return false;
			foreach($actions as $id => $arr ) {
				$url = add_query_arg('um_action', $id );
				$url = add_query_arg('uid', um_profile_id(), $url );
				$items[] = '<a href="' . $url .'" class="real_url '.$id.'-item">' . $arr['label'] . '</a>';
			}
			return $items;
		}


		/**
		 * This method checks if give user profile is private.
		 *
		 * @usage <?php UM()->user()->is_private_profile( $user_id ); ?>
		 *
		 * @param int $user_id A user ID must be passed to check if the user profile is private
		 *
		 * @return bool
		 *
		 * @example This example display a specific user's name If his profile is public

		<?php

		um_fetch_user( 60 );
		$is_private = UM()->user()->is_private_profile( 60 );
		if ( ! $is_private ) {
		echo 'User is public and his name is ' . um_user('display_name');
		}

		?>
		 *
		 */
		function is_private_profile( $user_id ) {
			$privacy = get_user_meta( $user_id, 'profile_privacy', true );
			if ( $privacy == __('Only me','ultimate-member') ) {
				return true;
			}
			return false;
		}


		/**
		 * This method can be used to determine If a certain user is approved or not.
		 *
		 * @usage <?php UM()->user()->is_approved( $user_id ); ?>
		 *
		 * @param int $user_id The user ID to check approval status for
		 *
		 * @return bool
		 *
		 * @example Do something If a user's membership is approved

		<?php

		if ( UM()->user()->is_approved( 55 ) {
		// User account is approved
		} else {
		// User account is not approved
		}

		?>
		 *
		 */
		function is_approved( $user_id ) {
			$status = get_user_meta( $user_id, 'account_status', true );
			if ( $status == 'approved' || $status == '' ) {
				return true;
			}
			return false;
		}


		/**
		 * Is private
		 *
		 * @param $user_id
		 * @param $case
		 *
		 * @return bool|mixed|void
		 */
		function is_private_case( $user_id, $case ) {
			$privacy = get_user_meta( $user_id, 'profile_privacy', true );

			if ( $privacy == $case ) {
				/**
				 * UM hook
				 *
				 * @type filter
				 * @title um_is_private_filter_hook
				 * @description Change user privacy
				 * @input_vars
				 * [{"var":"$is_private","type":"bool","desc":"Is user private"},
				 * {"var":"$privacy","type":"bool","desc":"Profile Privacy"},
				 * {"var":"$user_id","type":"int","desc":"User ID"}]
				 * @change_log
				 * ["Since: 2.0"]
				 * @usage
				 * <?php add_filter( 'um_is_private_filter_hook', 'function_name', 10, 3 ); ?>
				 * @example
				 * <?php
				 * add_filter( 'um_is_private_filter_hook', 'my_is_private_filter', 10, 3 );
				 * function my_is_private_filter( $is_private ) {
				 *     // your code here
				 *     return $is_private;
				 * }
				 * ?>
				 */
				$bool = apply_filters( 'um_is_private_filter_hook', false, $privacy, $user_id );
				return $bool;
			}

			return false;
		}


		/**
		 * Update files
		 *
		 * @param $changes
		 */
		function update_files( $changes ) {

			foreach ( $changes as $key => $uri ) {
				$src = um_is_temp_upload( $uri );
				UM()->files()->new_user_upload( $this->id, $src, $key );
			}

		}


		/**
		 * Update profile
		 *
		 * @param $changes
		 */
		function update_profile( $changes ) {

			$args['ID'] = $this->id;

			/**
			 * UM hook
			 *
			 * @type filter
			 * @title um_before_update_profile
			 * @description Change update profile changes data
			 * @input_vars
			 * [{"var":"$changes","type":"array","desc":"User Profile Changes"},
			 * {"var":"$user_id","type":"int","desc":"User ID"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage
			 * <?php add_filter( 'um_before_update_profile', 'function_name', 10, 2 ); ?>
			 * @example
			 * <?php
			 * add_filter( 'um_before_update_profile', 'my_before_update_profile', 10, 2 );
			 * function my_before_update_profile( $changes, $user_id ) {
			 *     // your code here
			 *     return $changes;
			 * }
			 * ?>
			 */
			$changes = apply_filters('um_before_update_profile', $changes, $this->id );

			// save or update profile meta
			foreach ( $changes as $key => $value ) {
				if ( ! in_array( $key, $this->update_user_keys ) ) {

					update_user_meta( $this->id, $key, $value );

				} else {

					$args[$key] = esc_attr( $changes[$key] );

				}

			}
			// update user
			if ( count( $args ) > 1 ) {
				global $wp_roles;
				$um_roles = get_option( 'um_roles' );

				if ( ! empty( $um_roles ) ) {
					$role_keys = array_map( function( $item ) {
						return 'um_' . $item;
					}, get_option( 'um_roles' ) );
				} else {
					$role_keys = array();
				}

				$exclude_roles = array_diff( array_keys( $wp_roles->roles ), array_merge( $role_keys, array( 'subscriber' ) ) );

				if ( isset( $args['role'] ) && in_array( $args['role'], $exclude_roles ) ) {
					unset( $args['role'] );
				}

				wp_update_user( $args );
			}

		}


		/**
		 * User exists by meta key and value
		 *
		 * @param $key
		 * @param $value
		 *
		 * @return bool|int
		 */
		function user_has_metadata( $key, $value ) {

			$value = UM()->validation()->safe_name_in_url( $value );

			$ids = get_users(array( 'fields' => 'ID', 'meta_key' => $key,'meta_value' => $value,'meta_compare' => '=') );
			if ( !isset( $ids ) || empty( $ids ) ) return false;
			foreach( $ids as $k => $id ) {
				if ( $id == um_user('ID') ){
					unset( $ids[$k] );
				} else {
					$duplicates[] = $id;
				}
			}
			if ( isset( $duplicates ) && !empty( $duplicates ) )
				return count( $duplicates );
			return false;
		}


		/**
		 * User exists by name
		 *
		 * @param $value
		 *
		 * @return bool
		 */
		function user_exists_by_name( $value ) {

			// Permalink base
			$permalink_base = UM()->options()->get( 'permalink_base' );

			$raw_value = $value;
			$value = UM()->validation()->safe_name_in_url( $value );
			$value = um_clean_user_basename( $value );

			// Search by Profile Slug
			$args = array(
				"fields" => array("ID"),
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key'		=>  'um_user_profile_url_slug_'.$permalink_base,
						'value'		=> strtolower( $raw_value ),
						'compare'	=> '='

					)

				)
			);


			$ids = new \WP_User_Query( $args );

			if( $ids->total_users > 0 ){
				$um_user_query = current( $ids->get_results() );
				return $um_user_query->ID;
			}

			// Search by Display Name or ID
			$args = array(
				"fields" => array("ID"),
				"search" => $value,
				'search_columns' => array( 'display_name','ID' )
			);

			$ids = new \WP_User_Query( $args );

			if( $ids->total_users > 0 ){
				$um_user_query = current( $ids->get_results() );
				return $um_user_query->ID;
			}


			// Search By User Login
			$value = str_replace(".", "_", $value );
			$value = str_replace(" ", "", $value );

			$args = array(
				"fields" => array("ID"),
				"search" => $value,
				'search_columns' => array(
					'user_login',
				)
			);

			$ids = new \WP_User_Query( $args );

			if( $ids->total_users > 0 ){
				$um_user_query = current( $ids->get_results() );
				return $um_user_query->ID;
			}

			return false;
		}


		/**
		 * This method checks if a user exists or not in your site based on the user ID.
		 *
		 * @usage <?php UM()->user()->user_exists_by_id( $user_id ); ?>
		 *
		 * @param int $user_id A user ID must be passed to check if the user exists
		 *
		 * @return bool|int
		 *
		 * @example Basic Usage

		<?php

		$boolean = UM()->user()->user_exists_by_id( 15 );
		if ( $boolean ) {
		// That user exists
		}

		?>

		 *
		 */
		function user_exists_by_id( $user_id ) {
			$aux = get_userdata( intval( $user_id ) );
			if( $aux == false ) {
				return false;
			} else {
				return $user_id;
			}
		}


		/**
		 * This method checks if a user exists or not in your site based on the user email as username
		 *
		 * @param string $slug A user slug must be passed to check if the user exists
		 *
		 * @usage <?php UM()->user()->user_exists_by_email_as_username( $slug ); ?>
		 *
		 * @return bool
		 *
		 * @example Basic Usage

		<?php

		$boolean = UM()->user()->user_exists_by_email_as_username( 'calumgmail-com' );
		if ( $boolean ) {
		// That user exists
		}

		?>
		 */
		function user_exists_by_email_as_username( $slug ) {

			$user_id = false;

			$ids = get_users( array( 'fields' => 'ID', 'meta_key' => 'um_email_as_username_'.$slug ) );
			if ( isset( $ids[0] ) && ! empty( $ids[0] ) ){
				$user_id = $ids[0];
			}

			return $user_id;
		}


		/**
		 * Set gravatar hash id
		 *
		 * @param $user_id
		 * @return string
		 */
		function set_gravatar( $user_id ) {

			um_fetch_user( $user_id );
			$email_address = um_user( 'user_email' );
			$hash_email_address = '';

			if ( $email_address ) {
				$hash_email_address = md5( $email_address );
				$this->profile['synced_gravatar_hashed_id'] = $hash_email_address;
				$this->update_usermeta_info( 'synced_gravatar_hashed_id' );
			}

			return $hash_email_address;
		}

	}
}