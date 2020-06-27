<?php
/**
* Process login attempts via Connect with Twitch shortcodes...
* 
* @version 1.0
*/
if ( ! class_exists( 'TwitchPress_Login_by_Shortcode' ) ) :

    class TwitchPress_Login_by_Shortcode {
        /**
         * @var Singleton
         */
        private static $instance;        
        
        public $twitchpress_login_errors = array();
        
        /**
        * To avoid duplicating entire procedures but allow them to
        * output messages in different ways we set this variable.
        * 
        * Here are the optional values...
        * 1. wp-login.php   - messages will display as proper login errors. 
        * 2. shortcodewpdie - wp_die() will be used to output first encountered error.
        * 3. customtemplate - template with connect to twitch button and styled messages.
        * 
        * @var string
        */
        private $output_type = null;

        /**
         * Protected constructor to prevent creating a new instance of the
         * *Singleton* via the `new` operator from outside of this class.
         */
        public function init() {
            // twitchpress_init fires once the core has loaded...
            add_action( 'twitchpress_init', array( $this, 'attach_hooks' ) );
        }

        /**
         * Hook plugin classes into WP/WC core.
         * 
         * @version 1.0
         */
        public function attach_hooks() {                
            add_action( 'plugins_loaded',        array( $this, 'init_filters' ), 1 );
            add_action( 'init',                  array( $this, 'twitch_login_public_listener' ), 1 );
            add_action( 'init',                  array( $this, 'redirect_login_page' ), 1 );
            
            add_shortcode( apply_filters( "twitchpress_connect_button_filter", 'twitchpress_connect_button' ), array( $this, 'shortcode_connect_button' ) );            

            wp_register_style( 'twitchpress-login-shortcode-styles', TWITCHPRESS_PLUGIN_URL . '/assets/css/twitchpress-login-shortcode.css' );            
            wp_enqueue_style( 'twitchpress-login-shortcode-styles', TWITCHPRESS_PLUGIN_URL . '/assets/css/twitchpress-login-shortcode.css' );
        
            do_action( 'twitchpress_login_loaded' );
        }
        
        /**
        * Outputs a Connect to Twitch button.
        * 
        * Shortcode tag: twitchpress_login_button
        * 
        * @version 3.0
        */
        public function shortcode_connect_button( $atts ) {
            global $wp, $wp_query, $post;
     
            if( is_user_logged_in() ) {
                return;
            }
            
            // Generate random number that will be return by Twitch.tv as state value and used to confirm cookie. 
            $random14 = twitchpress_random14();
                                         
            // Establish destination post/page ID...
            $loginpageid = apply_filters( 'twitchpress_loginext_shortcode_loginpageid', $wp_query->post->ID );
                            
            // Shortcode attributes.
            $atts = shortcode_atts( array(
                    'style'              => 0,
                    'text'               => $this->get_login_button_text(),
                    'loginpageid'        => $loginpageid,
                    'random14'           => $random14,
                    'successurl'         => null,// URL visitor is sent to on successful login. 
                    'wpmlapplysubdomain' => false,
                    'redirectto'         => null, // deprecated - use successurl. 
                    'returntopost'       => false,
                    'failureurl'         => null
                ), $atts, 'twitchpress_connect_button' );
    
            // Does shortcode require the visitor to be returned to the post? 
            if( isset( $atts['returntopost'] ) && $atts['returntopost'] == true ) {      
                $atts['successurl'] = $post->guid;
                $atts['failureurl'] = $post->guid;
            }

            // Do we need to prepend the current language to the success URL to maintain language.
            if( $atts['wpmlapplysubdomain'] == true || $atts['wpmlapplysubdomain'] == 'yes' ) 
            {
                if( function_exists( 'wpml_get_current_language' ) ) 
                {   
                    // Get the current users set language.          
                    $current_language = wpml_get_current_language();
                    
                    // Set the successurl, some backwards compatability offered by the old redirectto value.
                    if( $atts['successurl'] !== null ) 
                    {
                        $atts['successurl'] = 'https://' . $current_language . '.' . $atts['successurl'];
                    }
                    elseif( $atts['redirectto'] !== null  )
                    {
                        $atts['successurl'] = 'https://' . $current_language . '.' . $atts['redirectto'];    
                    }
                }
            }
                     
            $helix = new TWITCHPRESS_Twitch_API();

            // Create a visitor state array... 
            $states_array = array( 'random14'     => $atts['random14'], 
                                   'loginpageid'  => $atts['loginpageid'],
                                   'view'         => 'post',
                                   'successurl'   => $atts['successurl'], 
                                   'failureurl'   => $atts['failureurl'], 
                                   'returntopost' => $atts['returntopost'],
                                   'postid'       => $post->ID,
                                   'purpose'      => 'loginbyshortcode' );     
                               
            // Generate the oAuth2 URL to Twitch.tv login.
            $authUrl = twitchpress_generate_authorization_url( twitchpress_get_visitor_scopes(), $states_array );
             
            return self::connect_button_style_one( $authUrl, $atts );
        }  
            
        /**
        * Connect to Twitch button originally created for Ultimate Member plugin.
        *
        * @param mixed $authUrl
        * @param mixed $atts set in shortcode_connect_button()
        */
        public static function connect_button_style_one( $authUrl, $atts ) {      
            ob_start();
            switch ( $atts['style'] ) {
                case 0:
                    ?>
                    <div id="twitchpress_connect_button" class="ui text container">
                      <div class="ui inverted segment">
                        <a class="ui twitch button" href="<?php echo $authUrl; ?>">
                          <i class="twitch icon"></i> Connect with Twitch
                        </a>
                      </div>
                    </div> 
                    <?php
                break;
                case 1:
                    ?>
                    <div class="twitchpress-connect-button-one">';
                    <a href="<?php echo $authUrl; ?>"><?php echo $atts['text']; ?></a>
                    </div>
                    <?php 
                break;
                default:
                    ?>
                    <div id="twitchpress_connect_button" class="ui text container">
                      <div class="ui inverted segment">
                        <a class="ui twitch button" href="<?php echo $authUrl; ?>">
                          <i class="twitch icon"></i> Connect with Twitch
                        </a>
                      </div>
                    </div> 
                    <?php
                break;
            }
            return ob_get_clean();
        }   
                     
        /**
        * Called by init action hook.
        * 
        * Detect Twitch login request by confirming all required parameters exist to proceed.
        * 
        * We ensure the visitor is back on the original login view before 
        * generating applicable output for that view. 
        *
        * @version 2.0
        */
        public function twitch_login_public_listener() {              

            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }
            
            if( defined( 'DOING_CRON' ) && DOING_CRON ) {
                return;    
            }        
            
            if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                return;    
            }
                    
            // Ignore $_POST request. 
            if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {     
                return;
            }
                
            // Ignore a GET request if no state value is included.  
            if( !isset( $_GET['state'] ) ) { 
                return;
            }
            
            // This procedure is not meant for anyone already logged into WordPress.
            if( is_user_logged_in() ) {   
                return;
            }
                        
            $state_code = $_GET['state'];

            // We require the local state value stored in transient. 
            if( !$this->transient_state = get_transient( 'twitchpress_oauth_' . $state_code ) ) { 
                return;
            }             
            
            // Avoid continuing for other login methods that use the same state approach...
            if( !isset( $this->transient_state['purpose'] ) ) {
                return;
            }
            
            if( $this->transient_state['purpose'] !== 'loginbyshortcode' ) {
                return;
            }          
                      
            // If the login page type is "post" and not default, we need the page ID. 
            if( $this->transient_state['view'] == 'post' ) { 

                if( !$this->transient_state['loginpageid'] || !is_numeric( $this->transient_state['loginpageid'] ) ) { 

                    $this->return_to_login_page( array( 'key' => 1, 'source' => 'login', 'display_notice' => true ) );
                    return;
                }                           
            } else {
                return;// because this login has begun on the WP core login form.
            }
            
            // Prepare arguments for add_query_var() when redirecting. Cannot assume they are all set.
            $response_arguments = array( 'state' => $state_code );
            if( isset( $_GET['code'] ) ) { $response_arguments['code'] = $_GET['code']; }            
            if( isset( $_GET['scope'] ) ) { $response_arguments['scope'] = $_GET['scope']; }                     
                      
            // Did it all go terribly wrong in an even worse way?! 
            if( isset( $_GET['error'] ) ) { 
                $this->return_to_login_page( 
                    array( 'key' => 0, 
                           'source' => 'login', 
                           'display_notice' => true,
                           'placeholder_values' => array( $_GET['error'] ) 
                    ) 
                );
                return;             
            }        

            // A code is required...
            if( !isset( $_GET['code'] ) ) {
                $this->return_to_login_page( array( 'key' => 2, 'source' => 'login', 'display_notice' => true ) ); 
                return;               
            }            
         
            // The scope value is required...
            if( !isset( $_GET['scope'] ) ) {
                $this->return_to_login_page( array( 'key' => 3, 'source' => 'login', 'display_notice' => true ) );   
                return;         
            }
  
            /*
            * Register visitor (if required) then login the visitor if all security
            * checks are passed.
            * 
            * Assumes CODE, SCOPE and STATE exist in GET request.  
            * 
            * @version 2.0
            */
    
            $helix = new TWITCHPRESS_Twitch_API();
            
            // Ensure code is ready...
            if( !twitchpress_validate_code( $_GET['code'] ) ) {   
                $this->return_to_login_page( array( 'key' => 4, 'source' => 'login', 'display_notice' => true ) );                           
                return;                   
            }
        
            // Generate a token, it is stored as user meta further down.
            $token_array = $helix->request_user_access_token( $_GET['code'], __FUNCTION__ );   

            // Confirm token was returned...  
            if( !twitchpress_was_valid_token_returned_from_helix( $token_array ) ) {                           
                $this->return_to_login_page( array( 'key' => 5, 'source' => 'login', 'display_notice' => true ) );                           
                return;                         
            }
                      
            // Ensure the user:read:email scope has been accepted...
            if( !in_array( 'user:read:email', $token_array->scope ) ) {                              
                $this->return_to_login_page( array( 'key' => 6, 'source' => 'login', 'display_notice' => true ) );                           
                return;                         
            }
                                
            // Get the visitors Twitch details...
            //$twitch_user = $helix->getUserObject_Authd( $token_array->access_token, $_GET['code'] );
            $twitch_reply = $helix->get_user_by_bearer_token( $token_array->access_token );
            
            if( is_wp_error( $twitch_reply ) || $twitch_reply == false ) { 
                $this->return_to_login_page( array( 'key' => 7, 'source' => 'login', 'display_notice' => true ) );                           
                return;                                                        
            }

            // ['email] is required. 
            if( !isset( $twitch_reply->email ) ) {        
                $this->return_to_login_page( array( 'key' => 8, 'source' => 'login', 'display_notice' => true ) );                           
                return;                        
            }

            // Helix Values 
            $twitch_user = array();       
            $twitch_user['_id']               = sanitize_key( $twitch_reply->id ); // 44322889,
            $twitch_user['bio']               = sanitize_text_field( $twitch_reply->description ); // "Just a gamer playing games and chatting. :)",
            $twitch_user['login']             = sanitize_user( $twitch_reply->login ); // "dallas",
            $twitch_user['display_name']      = sanitize_user( $twitch_reply->display_name ); // "dallas",
            $twitch_user['email']             = sanitize_email( $twitch_reply->email ); // "email-address@provider.com",
            $twitch_user['logo']              = esc_url_raw( $twitch_reply->profile_image_url ) ; // "https://static-cdn.jtvnw.net/jtv_user_pictures/dallas-profile_image-1a2c906ee2c35f12-300x300.png",
                                               
            // We can log the user into WordPress if they have an existing account.
            $wp_user = get_user_by( 'email', $twitch_user['email'] );
            
            // If visitor (as email address) does not exist in WP database by email check for Twitch history using user/channel "_id".
            if( $wp_user === false ) 
            {     
                $args = array(
                    'meta_key'     => 'twitchpress_twitch_id',
                    'meta_value'   => $twitch_user['_id'],
                    'count_total'  => false,
                    'fields'       => 'all',
                ); 
                
                $get_users_results = get_users( $args ); 
                
                // We will not continue if there are more than one WP account with the same Twitch ID.     
                // This will be a very rare situation I think so we won't get too advanced on how to deal with it, yet! 
                if( isset( $get_users_results[1] ) ) 
                {       
                    $this->return_to_login_page( array( 'key' => 10, 'source' => 'login', 'display_notice' => true ) );                           
                    return;                           
                } 
                elseif( isset( $get_users_results[0] ) ) 
                {   
                    $wp_user_id = $get_users_results[0]->ID;
                             
                    // A single user has been found with the Twitch "_id" associated with it.
                    // We will further marry the WP account to Twitch account.
                    update_user_meta( $wp_user_id, 'twitchpress_twitch_id', $twitch_user['_id'] );
                    update_user_meta( $wp_user_id, 'twitchpress_twitch_email', $twitch_user['email'] );
                    update_user_meta( $wp_user_id, 'twitchpress_twitch_bio', $twitch_user['bio'] );
                    update_user_meta( $wp_user_id, 'twitchpress_twitch_display_name', $twitch_user['display_name'] );
                    update_user_meta( $wp_user_id, 'twitchpress_auth_time', time() );
                    update_user_meta( $wp_user_id, 'twitchpress_code', sanitize_text_field( $_GET['code'] ) );
                    update_user_meta( $wp_user_id, 'twitchpress_token', $token_array->access_token );
                    update_user_meta( $wp_user_id, 'twitchpress_token_refresh', $token_array->refresh_token );

                    // Twitch.tv logo replaces gravatar...
                    twitchpress_update_user_meta_avatar( $get_users_results[0]->ID, $twitch_user['logo'] );
                          
                    // Log the user in.
                    self::authenticate_login_by_twitch( $get_users_results[0]->ID, $twitch_user['login'], $state_code  );

                    $this->login_success(); 
                                       
                    return;
                } 
            } 
            else 
            {      
                // A single user has been found matching the Twitch email address.
                // We will further marry the WP account to Twitch account.
                update_user_meta( $wp_user->data->ID, 'twitchpress_twitch_id', $twitch_user['_id'] );
                update_user_meta( $wp_user->data->ID, 'twitchpress_twitch_email', $twitch_user['email'] );
                update_user_meta( $wp_user->data->ID, 'twitchpress_twitch_bio', $twitch_user['bio'] );
                update_user_meta( $wp_user->data->ID, 'twitchpress_twitch_display_name', $twitch_user['display_name'] );
                update_user_meta( $wp_user->data->ID, 'twitchpress_auth_time', time() );
                update_user_meta( $wp_user->data->ID, 'twitchpress_code', sanitize_text_field( $_GET['code'] ) );
                update_user_meta( $wp_user->data->ID, 'twitchpress_token', $token_array->access_token );
                update_user_meta( $wp_user->data->ID, 'twitchpress_token_refresh', $token_array->refresh_token );

                // Twitch.tv logo replaces gravatar...
                twitchpress_update_user_meta_avatar( $wp_user->data->ID, $twitch_user['logo'] );

                self::authenticate_login_by_twitch( $wp_user->data->ID, $wp_user->data->user_login, $state_code );
 
                $this->login_success();
                
                return;
            }
           
            // If automatic registration is not on then we cannot continue...
            if( 'yes' !== get_option( 'twitchpress_automatic_registration' ) ) {        
                $this->return_to_login_page( array( 'key' => 11, 'source' => 'login', 'display_notice' => true ) );                           
                return;                         
            }
            
            // Prepare to register a new user starting with ensuring the login and display name do not already exist...
            $one = get_user_by( 'user_login', $twitch_user['login'] );
            $two = get_user_by( 'display_name', $twitch_user['display_name'] );
            $thr = get_user_by( 'user_nicename', $twitch_user['login'] );
                   
            if( is_object( $one ) && !is_wp_error( $one ) || is_object( $two ) && !is_wp_error( $two ) || is_object( $thr ) && !is_wp_error( $thr )  ){
                $this->return_to_login_page( array( 'key' => 12, 'source' => 'login', 'display_notice' => true ) );                           
                return;                                                      
            }
            
            $user_url = 'http://twitch.tv/' . $twitch_user['login'];
            
            $password = wp_generate_password( 12, true );
            
            $new_user = array(
                'user_login'    =>  $twitch_user['login'],
                'display_name'  =>  $twitch_user['display_name'],
                'user_url'      =>  $user_url,
                'user_pass'     =>  $password, 
                'user_email'    =>  $twitch_user['email'] 
            );
                    
            $wp_user_id = wp_insert_user( $new_user ) ;

            if ( is_wp_error( $wp_user_id ) ) 
            {    
                $error_message_append = '
                <ul>
                    <li>Display Name: ' . $twitch_user['display_name'] . '</li>
                    <li>User URL: ' . $user_url . '</li>
                    <li>Password: ' . $password . '</li>
                    <li>Email Address: ' . $twitch_user['email'] . '</li>
                </ul>
                <p>Please screenshot and report this notice.</p>';
                
                $this->return_to_login_page( array( 'key' => 13, 'source' => 'login', 'display_notice' => true ) );                          
                return;                        
            }      
                    
            // Store code and token in our new users meta.
            update_user_meta( $wp_user_id, 'twitchpress_twitch_id', $twitch_user['_id'] );
            update_user_meta( $wp_user_id, 'twitchpress_twitch_email', $twitch_user['email'] );
            update_user_meta( $wp_user_id, 'twitchpress_twitch_bio', $twitch_user['bio'] );
            update_user_meta( $wp_user_id, 'twitchpress_twitch_display_name', $twitch_user['display_name'] );
            update_user_meta( $wp_user_id, 'twitchpress_auth_time', time() );
            update_user_meta( $wp_user_id, 'twitchpress_code', sanitize_text_field( $_GET['code'] ) );
            update_user_meta( $wp_user_id, 'twitchpress_token', $token_array->access_token );
            update_user_meta( $wp_user_id, 'twitchpress_token_refresh', $token_array->refresh_token );

            // Twitch.tv logo replaces gravatar...
            twitchpress_update_user_meta_avatar( $wp_user_id, $twitch_user['logo'] ); 
            
            self::authenticate_login_by_twitch( $wp_user_id, $twitch_user['login'], $state_code );
       
            twitchpress_sync_user_on_registration( $wp_user_id );

            $this->return_to_login_page( array( 'key' => 15, 'source' => 'login', 'display_notice' => true ) );                           
            return;            
        }  
        
        /**
        * Return the visitor to the original page...
        * Notice output can then be performed...
        *              
        * @version 1.0
        */
        public function return_to_login_page( $atts ) { 

            // Avoid doing twice (looping)...
            if( !isset( $_GET['twitchpress_sentto_login'] ) ) {    

                // Get page permalink... 
                $page_permalink = get_post_permalink( $this->transient_state['loginpageid'] );
           
                // Add our value to indicate we have done the redirect once already, avoid it twice. 
                $page_permalink_plus = add_query_arg( array( 'twitchpress_sentto_login' => 1 ), $page_permalink );
                
                // Get current URL query arguments for adding to new URL... 
                $page_permalink_plus = add_query_arg( $_GET, $page_permalink_plus );

                // Attach a notice...
                if( isset( $atts['display_notice'] ) ) {    
                     
                    $notice_values = shortcode_atts( array(
                        'twitchpress_notice' => true,
                        'source'             => 'login',
                        'key'                => 14,
                        'location_id'        => 'login_shortcode',
                        'placeholder_values' => ''
                    ), $atts, 'twitchpress_not_shortcode_login_return' );
                    
                    $page_permalink_plus = add_query_arg( $notice_values, $page_permalink_plus );    
                }
                         
                // Redirect
                twitchpress_redirect_tracking( $page_permalink_plus, __LINE__, __FUNCTION__ );
                exit;
            }    
        }
                
        /**
        * Do the authentication part of a login for the current the visitor using the
        * their WordPress user_id. 
        * 
        * Was originally called by the authenticate hook but
        * we do not need to p9rocess WP core login values and so that approach has been
        * removed.
        * 
        * @param mixed $user_id
        * @returns boolean false if authentication rejected else does exit
        * 
        * @version 1.4
        */
        public function authenticate_login_by_twitch( $wp_user_id, $twitch_username, $state_code ) {
            // This method is only called when Twitch returns a code, scope and state else it shouldn't be called.
            // This method also assumes all security checks done, this line is just a small precaution. 
            if( isset( $_GET['code'] ) || isset( $_GET['scope'] ) || isset( $_GET['state'] ) ) {

                // A bit of a hack to tell WordPress which user is the current one... 
                wp_set_current_user( $wp_user_id );
                
                // Set authorization for the current visitor for the now "current_user" account...
                wp_set_auth_cookie( $wp_user_id ); 

                // We need the user_object for the 2nd parameter of the wp_login action...
                $user_object = get_user_by( 'ID', $wp_user_id );
              
                // Do the wp_login action to behave as the core would... 
                do_action( 'wp_login', $user_object->user_login, $user_object ); 

                return true;    
            }
            
            return false;
        }
        
        /**
        * Last login access when the login was a success and auth has been
        * granted and setup by WordPress. 
        * 
        * @version 3.0
        */
        public function login_success() {
            if( twitchpress_login_prevent_redirect() ) { return; }
            
            if( isset( $this->transient_state['successurl'] ) && is_string( $this->transient_state['successurl'] ) ) 
            {         
                // Send user to custom set URL pass through shortcode...
                twitchpress_redirect_tracking( $this->transient_state['successurl'], __LINE__, __FUNCTION__, __FILE__ );     
                exit; 
            }
            elseif( isset( $this->transient_state['redirectto'] ) && is_string( $this->transient_state['redirectto'] ) )
            {           
                // Send user to custom set URL pass through shortcode... 
                twitchpress_redirect_tracking( $this->transient_state['redirectto'], __LINE__, __FUNCTION__, __FILE__ );     
                exit;       
            }
            else 
            {           
                // Check for a successful logged-in page set using page ID...
                $loggedin_page_id = get_option( 'twitchpress_login_loggedin_page_id', false );
                if( $loggedin_page_id !== false && is_numeric( $loggedin_page_id ) ) 
                {       
                    $permalink = get_post_permalink( $loggedin_page_id ); 
                    twitchpress_redirect_tracking( $permalink, __LINE__, __FUNCTION__, __FILE__ );
                    exit;                    
                }
            }
            
            twitchpress_redirect_tracking( get_post_permalink( $this->transient_state['loginpageid'] ), __LINE__, __FUNCTION__, __FILE__ );
            exit;            
        }
                            
        public function button( $authUrl ) {
            ?>
            <div class="twitchpresslogin">
                <a href="<?php echo $authUrl; ?>"><?php echo esc_html( $this->get_login_button_text() ); ?></a>
            </div>        
            <?php     
        }

        /**
        * Get the text for the public Twitch login button (link styled button and not a form)
        * 
        * @returns string
        * 
        * @version 2.0
        */
        protected function get_login_button_text() {
            $text = get_option( 'twitchpress_login_button_text', __( 'Login with Twitch', 'twitchpress-login' ) );
            if( !$text ) { $text = __( 'Twitch Login', 'twitchpress' ); }
            return apply_filters( 'twitchpress_login_button_text', $text );
        } 

        /**
        * Get the sites login URL with multisite and SSL considered.
        * 
        * @returns string URL with filter available
        * 
        * @version 1.0
        */
        protected function get_login_url() {                                 
            $login_url = wp_login_url();

            if ( is_multisite() ) {
                $login_url = network_site_url('wp-login.php');
            }                      

            if (force_ssl_admin() && strtolower(substr($login_url,0,7)) == 'http://') {
                $login_url = 'https://' . substr( $login_url, 7 );
            }

            return apply_filters( 'twitchpress_login_url', $login_url );
        } 
        
        /**
        * Force redirect default login wp-login.php
        * to a default page with login shortcode.
        * 
        * @version 2.0
        */
        public function redirect_login_page() {

            $page_viewed = basename( esc_url( $_SERVER['REQUEST_URI'] ) );
            if ( $page_viewed !== "wp-login.php" ) {
                return;
            } 
                       
            if( 'yes' !== get_option( 'twitchpress_login_redirect_to_custom' ) ) {
                return;                
            }
            
            if( !$custom_login_id = get_option( 'twitchpress_login_mainform_page_id' ) ) {
                return;    
            }
            
            if( !is_numeric( $custom_login_id ) ) {
                return;
            }
            
            $permalink = get_post_permalink( $custom_login_id );
            twitchpress_redirect_tracking( $permalink, __LINE__, __FUNCTION__, __FILE__ );
            exit;
        }

        /**
         * Modify the url returned by wp_registration_url().
         *
         * @return string page url with registration shortcode.
         */
        public function register_url_func() {
            if ( isset( $this->db_settings_data['set_registration_url'] ) ) {
                $reg_url = get_permalink( absint( $this->db_settings_data['set_registration_url'] ) );
                return $reg_url;
            }
        }

        /** force redirection of default registration to custom one */
        public function redirect_reg_page() {
            if ( isset( $this->db_settings_data['set_registration_url'] ) ) {

                $reg_url = get_permalink( absint( $this->db_settings_data['set_registration_url'] ) );

                $page_viewed = basename( esc_url( $_SERVER['REQUEST_URI'] ) );

                if ( $page_viewed == "wp-login.php?action=register" && $_SERVER['REQUEST_METHOD'] == 'GET' ) {
                    twitchpress_redirect_tracking( $reg_url, __LINE__, __FUNCTION__, __FILE__ );
                    exit;
                }
            }
        }
        
        /** Force redirection of default registration to the page with custom registration. */
        public function redirect_password_reset_page() {
            if ( isset( $this->db_settings_data['set_lost_password_url'] ) ) {

                $password_reset_url = get_permalink( absint( $this->db_settings_data['set_lost_password_url'] ) );

                $page_viewed = basename( esc_url( $_SERVER['REQUEST_URI'] ) );

                if ( $page_viewed == "wp-login.php?action=lostpassword" && $_SERVER['REQUEST_METHOD'] == 'GET' ) {
                    twitchpress_redirect_tracking( $password_reset_url, __LINE__, __FUNCTION__, __FILE__ );
                    exit;
                }
            }
        }                                    
    }
    
endif;    