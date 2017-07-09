<?php       
/**
 * TwitchPress - Installation
 *
 * Installation of post types, taxonomies, database tables, options etc. 
 *
 * @author   Ryan Bayne
 * @category Configuration
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Install' ) ) : 

/**
 * TwitchPress_Install Class.
 */
class TwitchPress_Install { 
    
    /** @var array DB updates and callbacks that need to be run per version */
    private static $db_updates = array(
        '0.0.0' => array(
            'twitchpress_update_000_file_paths',
            'twitchpress_update_000_db_version',
        ), 
    );
                
    public static function init() {
        add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
        add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );    
        add_action( 'admin_init', array( __CLASS__, 'offer_wizard' ) );    
        add_action( 'in_plugin_update_message-twitchpress/twitchpress.php', array( __CLASS__, 'in_plugin_update_message' ) );
        add_filter( 'plugin_action_links_' . TWITCHPRESS_PLUGIN_BASENAME, array( __CLASS__, 'plugin_action_links' ) );    
        add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
    }
    
    /**
     * Update plugin version.
     */
    private static function update_package_version() {
        delete_option( 'twitchpress_version' );
        add_option( 'twitchpress_version', TwitchPress()->version );
    } 

    /**
     * Check package version against installed version on every request.
     * Run installation to apply updates if different.
     */
    public static function check_version() {         
        if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'twitchpress_version' ) !== TwitchPress()->version ) {
            self::install();
        }
    }
    
    /**
     * Actions when the update button is clicked - is hooked by admin_init.
     */
    public static function install_actions() {
        self::install_action_do_update();
        self::install_action_updater_cron();
    }
    
    /**
    * If key values are missing we will offer the wizard.
    * 
    * @version 1.0
    */
    public static function offer_wizard() {
        $offer_wizard = false;
        
        if( !current_user_can( 'manage_twitchpress') ) {
            return;    
        }
        
        if( isset( $_GET['page']) && $_GET['page'] == 'twitchpress-setup' ) {
            return;    
        }
        
        if( !get_option( 'twitchpress_version' ) ) {
            
            $offer_wizard = 'twitchpress_version';
            
        } elseif( !get_option( 'twitchpress_main_channel_name' ) ) {
            
            $offer_wizard = 'twitchpress_main_channel_name';
            
        } elseif( !get_option( 'twitchpress_main_channel_id' ) ) {
            
            $offer_wizard = 'twitchpress_main_channel_id';
            
        } elseif( !get_option( 'twitchpress_main_client_id' ) ) {
            
            $offer_wizard = 'twitchpress_main_client_id';
            
        } elseif( !get_option( 'twitchpress_main_client_secret' ) ) {
            
            $offer_wizard = 'twitchpress_main_client_secret';
            
        } elseif( !get_option( 'twitchpress_main_code' ) ) {
            
            $offer_wizard = 'twitchpress_main_code';
            
        } elseif( !get_option( 'twitchpress_main_token' ) ) {
            
            $offer_wizard = 'twitchpress_main_token';
            
        }     
        
        if( $offer_wizard === false ) { return; }
        
        $wizard_link = '<p><a href="' . esc_url( admin_url( 'index.php?page=twitchpress-setup' ) ) . '" class="button button-primary">' . __( 'Setup wizard', 'twitchpress' ) . '</a></p>';
        
        TwitchPress_Admin_Notices::add_wordpress_notice(
            'missingvaluesofferwizard',
            'info',
            false,
            __( 'Twitch API Credentials Missing', 'twitchpress' ),
            sprintf( __( 'TwitchPress is not ready because the %s option is missing. If you have already been using the plugin and this notice suddenly appears then it suggests important options have been deleted or renamed. You can go through the Setup Wizard again to correct this problem. You should also report it. %s', 'twitchpress'), $offer_wizard, $wizard_link )    
        );           
    }
    
    /**
    * Manual plugin update action. 
    */
    public static function install_action_do_update() {
        if ( ! empty( $_GET['do_update_twitchpress'] ) ) {
            self::update();
            TwitchPress_Admin_Notices::add_notice( 'update' );
        }        
    }
    
    /**
    * Forced plugin updating (twitchpress do_action) 
    */
    public static function install_action_updater_cron() {
        if ( ! empty( $_GET['force_update_twitchpress'] ) ) {
            do_action( 'wp_twitchpress_updater_cron' );
            wp_safe_redirect( admin_url( 'admin.php?page=twitchpress-settings' ) );
        }        
    }
    
    /**
     * Install TwitchPress by Ryan Bayne.
     */
    public static function install() {
        global $wpdb;

        if ( ! defined( 'TWITCHPRESS_INSTALLING' ) ) {
            define( 'TWITCHPRESS_INSTALLING', true );
        }

        // Ensure needed classes are loaded
        include_once( 'admin/class.twitchpress-admin-notices.php' );

        TwitchPress_Admin_Notices::remove_all_notices();
        
        self::create_options();
        self::create_roles();
        self::create_files();
        
        // Register post types
        TwitchPress_Post_types::register_post_types();
        TwitchPress_Post_types::register_taxonomies();
                                    
        // Queue upgrades/setup wizard
        $current_installed_version    = get_option( 'twitchpress_version', null );
        $current_db_version           = get_option( 'twitchpress_db_version', null );

        // No versions? This is a new install :)
        if ( is_null( $current_installed_version ) && is_null( $current_db_version ) && apply_filters( 'twitchpress_enable_setup_wizard', true ) ) {  
            TwitchPress_Admin_Notices::add_notice( 'install' );
            set_transient( '_twitchpress_activation_redirect', 1, 30 );
        }                           

        if ( ! is_null( $current_db_version ) && version_compare( $current_db_version, max( array_keys( self::$db_updates ) ), '<' ) ) {
            TwitchPress_Admin_Notices::add_notice( 'update' );
        } else {
            self::update_db_version();
        }

        self::update_package_version();
                                     
        // Flush rules after install
        flush_rewrite_rules();

        /*
         * Deletes all expired transients. The multi-table delete syntax is used
         * to delete the transient record from table a, and the corresponding
         * transient_timeout record from table b.
         *
         * Based on code inside core's upgrade_network() function.
         */
        $sql = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
            WHERE a.option_name LIKE %s
            AND a.option_name NOT LIKE %s
            AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
            AND b.option_value < %d";
        $wpdb->query( $wpdb->prepare( $sql, $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_transient_timeout_' ) . '%', time() ) );

        // Trigger action
        do_action( 'twitchpress_installed' );
    }
    
    /**
     * Show plugin changes from the readme.txt stored at WordPress.org.
     * 
     * Requires TWITCHPRESS_WORDPRESSORG_SLUG to be defined.  
     * 
     * Code adapted from W3 Total Cache.
     * 
     * @uses TWITCHPRESS_WORDPRESSORG_SLUG
     * @uses wp_kses_post()
     */
    public static function in_plugin_update_message( $args ) {   
        $transient_name = 'twitchpress_upgrade_notice_' . $args['Version'];

        if ( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {
            
            // Plugin might not be on WordPress.org yet or will never be. 
            $response = '';
            if( defined( 'TWITCHPRESS_WORDPRESSORG_SLUG' ) && TWITCHPRESS_WORDPRESSORG_SLUG != false && is_string( TWITCHPRESS_WORDPRESSORG_SLUG ) ) {
                $response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/' . TWITCHPRESS_WORDPRESSORG_SLUG . '/trunk/readme.txt' );
                                   
                if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
                    $upgrade_notice = self::parse_update_notice( $response['body'], $args['new_version'] );
                    set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
                }
            }
        }
                 
        echo wp_kses_post( $upgrade_notice );
    } 
                       
    /**
     * Show action links on the plugin screen.
     *
     * @param    mixed $links Plugin Action links
     * @return    array
     */
    public static function plugin_action_links( $links ) {
        $action_links = array(
            'settings' => '<a href="' . admin_url( 'admin.php?page=twitchpress-settings' ) . '" title="' . esc_attr( __( 'View TwitchPress Settings', 'twitchpress' ) ) . '">' . __( 'Settings', 'twitchpress' ) . '</a>',
        );

        return array_merge( $action_links, $links );
    }
                                              
    /**
     * Show row meta on the plugin screen.
     *
     * @param    mixed $links Plugin Row Meta
     * @param    mixed $file  Plugin Base file
     * @return    array
     */
    public static function plugin_row_meta( $links, $file ) {     
        if ( $file == TWITCHPRESS_PLUGIN_BASENAME ) {
            $row_meta = array(
                'docs'    => '<a href="' . esc_url( apply_filters( 'twitchpress_docs_url', TWITCHPRESS_DOCS ) ) . '" title="' . esc_attr( __( 'View TwitchPress Documentation', 'twitchpress' ) ) . '">' . __( 'Docs', 'twitchpress' ) . '</a>',
                'support' => '<a href="' . esc_url( apply_filters( 'twitchpress_support_url', 'https://github.com/RyanBayne/TwitchPress/issues' ) ) . '" title="' . esc_attr( __( 'Visit Support Forum', 'twitchpress' ) ) . '">' . __( 'Support', 'twitchpress' ) . '</a>',
                'donate' => '<a href="' . esc_url( apply_filters( 'twitchpress_donate_url', TWITCHPRESS_DONATE ) ) . '" title="' . esc_attr( __( 'Donate to Project', 'twitchpress' ) ) . '">' . __( 'Donate', 'twitchpress' ) . '</a>',
                'blog' => '<a href="' . esc_url( apply_filters( 'twitchpress_blog_url', TWITCHPRESS_DONATE ) ) . '" title="' . esc_attr( __( 'Get project updates from the blog.', 'twitchpress' ) ) . '">' . __( 'Blog', 'twitchpress' ) . '</a>',
            );

            return array_merge( $links, $row_meta );
        }

        return (array) $links;
    }

    /**
     * Create roles and capabilities.
     */
    public static function create_roles() {
        global $wp_roles;

        if ( ! class_exists( 'WP_Roles' ) ) {
            return;
        }

        if ( ! isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }

        // TwitchPress Developer role
        add_role( 'twitchpressdeveloper', __( 'TwitchPress Developer', 'twitchpress' ), array(
            'level_9'                => true,
            'level_8'                => true,
            'level_7'                => true,
            'level_6'                => true,
            'level_5'                => true,
            'level_4'                => true,
            'level_3'                => true,
            'level_2'                => true,
            'level_1'                => true,
            'level_0'                => true,
            'read'                   => true,
            'read_private_pages'     => true,
            'read_private_posts'     => true,
            'edit_users'             => true,
            'edit_posts'             => true,
            'edit_pages'             => true,
            'edit_published_posts'   => true,
            'edit_published_pages'   => true,
            'edit_private_pages'     => true,
            'edit_private_posts'     => true,
            'edit_others_posts'      => true,
            'edit_others_pages'      => true,
            'publish_posts'          => true,
            'publish_pages'          => true,
            'delete_posts'           => true,
            'delete_pages'           => true,
            'delete_private_pages'   => true,
            'delete_private_posts'   => true,
            'delete_published_pages' => true,
            'delete_published_posts' => true,
            'delete_others_posts'    => true,
            'delete_others_pages'    => true,
            'manage_categories'      => true,
            'manage_links'           => true,
            'moderate_comments'      => true,
            'unfiltered_html'        => true,
            'upload_files'           => true,
            'export'                 => true,
            'import'                 => true,
            'list_users'             => true
        ) );

        // Add custom capabilities to our new TwitchPress Developers role. 
        $new_admin_capabilities = self::get_developer_capabilities();
        foreach ( $new_admin_capabilities as $cap_group ) {
            foreach ( $cap_group as $cap ) {
                $wp_roles->add_cap( 'twitchpressdeveloper', $cap );
                // Ensure the first administration account has all capabilities.
                if( get_current_user_id() == 1 ) {
                    $wp_roles->add_cap( 'administrator', $cap );    
                }                 
            }
        }        
        
        // Add the plugins custom capabilities to administrators. 
        $new_admin_capabilities = self::get_core_capabilities();
        foreach ( $new_admin_capabilities as $cap_group ) {
            foreach ( $cap_group as $cap ) {
                $wp_roles->add_cap( 'administrator', $cap );                 
            }
        }
    }
    
    /**
     * Remove all roles and all custom capabilities added to 
     * both custom roles and core roles.
     */
    public static function remove_roles() {
        global $wp_roles;

        if ( ! class_exists( 'WP_Roles' ) ) {
            return;
        }

        if ( ! isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }

        $capabilities = self::get_core_capabilities();
        $capabilities = array_merge( $capabilities, self::get_developer_capabilities() );
        
        foreach ( $capabilities as $cap_group ) {
            foreach ( $cap_group as $cap ) {
                $wp_roles->remove_cap( 'twitchpressdeveloper', $cap );
                $wp_roles->remove_cap( 'administrator', $cap );
            }
        }

        remove_role( 'twitchpressdeveloper' );
    }    
        
    /**
     * Get custom capabilities for this package. These are assigned to
     * all administrators and are available for applying to moderator
     * level users.
     * 
     * Caps are assigned during installation or reset.
     *
     * @return array
     * 
     * @version 1.0
     */
    private static function get_core_capabilities() {
        $capabilities = array();

        $capabilities['core'] = array(
            'manage_twitchpress',
        );

        return $capabilities;
    } 
    
    /**
    * Very strict capabilities for professional developers only.
    * 
    * @version 1.0
    */
    private static function get_developer_capabilities() {
        $capabilities = array();

        $capabilities['core'] = array(
            'code_twitchpress',
            'twitchpressdevelopertoolbar'
        );

        return $capabilities;        
    }
                                      
    /**
     * Create files/directories with .htaccess and index files added by default.
     */
    private static function create_files() {
        // Install files and folders for uploading files and prevent hotlinking
        $upload_dir      = wp_upload_dir();
        $download_method = get_option( 'twitchpress_file_download_method', 'force' );
                                             
        $files = array(
            array(
                'base'         => $upload_dir['basedir'] . '/twitchpress_uploads',
                'file'         => 'index.html',
                'content'     => ''
            ),
            array(
                'base'         => TWITCHPRESS_LOG_DIR,
                'file'         => '.htaccess',
                'content'     => 'deny from all'
            ),
            array(
                'base'         => TWITCHPRESS_LOG_DIR,
                'file'         => 'index.html',
                'content'     => ''
            )
        );

        if ( 'redirect' !== $download_method ) {
            $files[] = array(
                'base'         => $upload_dir['basedir'] . '/twitchpress_uploads',
                'file'         => '.htaccess',
                'content'     => 'deny from all'
            );
        }

        foreach ( $files as $file ) {
            if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
                if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
                    fwrite( $file_handle, $file['content'] );
                    fclose( $file_handle );
                }
            }
        }
    }
    
    /**
     * Default options.
     *
     * Sets up the default options used on the settings page.
     */
    private static function create_options() {
        // Include settings so that we can run through defaults
        include_once( 'admin/class.twitchpress-admin-settings.php' );

        $settings = TwitchPress_Admin_Settings::get_settings_pages();

        foreach ( $settings as $section ) {
            if ( ! method_exists( $section, 'get_settings' ) ) {
                continue;
            }
            $subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

            foreach ( $subsections as $subsection ) {
                foreach ( $section->get_settings( $subsection ) as $value ) {
                    if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
                        $autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
                        add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
                    }
                }
            }
        }
    }
        
    /**
     * Update DB version to current.
     */
    public static function update_db_version( $version = null ) {
        delete_option( 'twitchpress_db_version' );
        add_option( 'twitchpress_db_version', is_null( $version ) ? TwitchPress()->version : $version );
    }      
    
    /**
     * Add the default terms for TwitchPress taxonomies - product types and order statuses. Modify this at your own risk.
     */
    public static function create_terms() {
        $taxonomies = array();

        foreach ( $taxonomies as $taxonomy => $terms ) {
            foreach ( $terms as $term ) {
                if ( ! get_term_by( 'name', $term, $taxonomy ) ) {
                    wp_insert_term( $term, $taxonomy );
                }
            }
        }
    }                    
}

endif;

TwitchPress_Install::init();