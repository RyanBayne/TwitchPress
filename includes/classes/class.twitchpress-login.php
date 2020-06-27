<?php
/**
* Process login attempts via core WP login form...
* 
* @version 5.0
*/
if ( ! class_exists( 'TwitchPress_Login' ) ) :

    class TwitchPress_Login {
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
         */
        public function attach_hooks() {

            add_action( 'plugins_loaded',        array( $this, 'init_filters' ), 1 );

            add_action( 'init',                  array( $this, 'twitch_login_public_listener' ), 1 );
            add_action( 'init',                  array( $this, 'redirect_login_page' ), 1 );

            // WordPress Login Form Approach - Adds button to core login form and processes with full integration.
            add_action( 'login_head',            array( $this, 'hide_login' ) );
            add_action( 'login_form',            array( $this, 'twitch_button_above'), 2 );
            add_action( 'login_form',            array( $this, 'twitch_button_below'), 2 );
            
            // Filters
            add_filter( 'login_errors',          array( $this, 'login_change_errors'), 5, 1 );                                  
            //add_filter( 'registration_redirect', array( $this, 'login_success_redirect_all'), 5, 3 );                                  
            add_filter( 'login_redirect',        array( $this, 'login_success_redirect_all'), 10, 3 );                                  

            do_action( 'twitchpress_login_loaded' );
        }        

        /**
        * Change an existing login related error message by code.
        * 
        * Use login_message() to add the HTML for new messages. It's a bit of a hack!
        * 
        * @version 1.2
        */
        public function login_change_errors( $error ) {
            global $errors;

            $login_messages = new TwitchPress_Custom_Login_Messages();

            // This will currently only ever result in a single custom message.
            if( $login_messages->twitchpress_login_messages ) {                
                foreach( $login_messages->twitchpress_login_messages as $key => $error ) {
                    return $error['message'];
                } 
            }
            
            if( $errors ) {            
                $err_codes = $errors->get_error_codes();       
            
                // Invalid username.
                // Default: '<strong>ERROR</strong>: Invalid username. <a href="%s">Lost your password</a>?'
                if ( in_array( 'invalid_username', $err_codes ) ) {
                    $error = '<strong>ERROR</strong>: Invalid username.';
                }

                // Incorrect password.
                // Default: '<strong>ERROR</strong>: The password you entered for the username <strong>%1$s</strong> is incorrect. <a href="%2$s">Lost your password</a>?'
                if ( in_array( 'incorrect_password', $err_codes ) ) {
                    $error = '<strong>ERROR</strong>: The password you entered is incorrect.';
                }
            }
            
            return $error;
        } 
               
        /**
        * Called by init action hook.
        * 
        * Detect Twitch login request by confirming all required parameters exist to proceed.
        * 
        * We ensure the visitor is back on the original login view before 
        * generating applicable output for that view. 
        *
        * @version 1.7
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
            
            $state_code = $_GET['state'];

            // We require the local state value stored in transient. 
            if( !$transient_state = get_transient( 'twitchpress_oauth_' . $state_code ) ) { 
                return;
            }             
            
            // This procedure is not meant for anyone already logged into WordPress.
            if( is_user_logged_in() ) {   
                return;
            }             
            
            // If the login page type is "post" we stop here...
            if( $transient_state['view'] == 'post' ) { 
                return;                          
            }
            
            // Stop if the states purpose is to manage a login via shortcode...
            if( isset( $transient_state['purpose'] ) && $transient_state['purpose'] == 'loginbyshortcode' ) {
                return;    
            }
            
            // Prepare arguments for add_query_var() when redirecting. Cannot assume they are all set.
            $response_arguments = array( 'state' => $state_code );
            if( isset( $_GET['code'] ) ) { $response_arguments['code'] = $_GET['code']; }            
            if( isset( $_GET['scope'] ) ) { $response_arguments['scope'] = $_GET['scope']; }                     
                      
            // If the $login_type = default we can do a view check and redirect early. 
            if( $transient_state['view'] == 'default' && !twitchpress_is_backend_login() ) {                
                twitchpress_redirect_tracking( add_query_arg( $_GET, wp_login_url() ), __LINE__, __FUNCTION__ ); 
                exit;             
            }    

            // Did it all go terribly wrong in an even worse way?! 
            if( isset( $_GET['error'] ) ) { 
                wp_die( __( 'Twitch.tv returned an error when attempting to login. This could be a temporary issue with the API. Please return to the login page and try again. If this message appears twice please report it.', 'twitchpress' ), __( 'Twitch Returned Error', 'twitchpress' ) );
            }        

            // we need CODE
            if( !isset( $_GET['code'] ) ) {
                wp_die( __( 'Sorry, it appears Twitch.tv returned you without a code. Please try again and report this issue if it happens again.', 'twitchpress' ), __( 'Twitch Login Request Ended', 'twitchpress' ) ); 
            }            
         
            // we need SCOPE
            if( !isset( $_GET['scope'] ) ) {
                wp_die( __( 'Sorry, it appears Twitch.tv returned you without all the URL values required to complete your login request. Please try again and report this issue if it happens again.', 'twitchpress' ), __( 'Twitch Login Request Ended', 'twitchpress' ) ); 
            }

            // The request is Twitch oAuth2 related, lets try and auth visitor.
            $this->process_login_using_twitch_helix( $state_code );
        }

        /**
        * Register (if required) then login the visitor if all security
        * checks are passed.
        * 
        * Assumes CODE, SCOPE and STATE exist in GET request.  
        * 
        * @version 2.0
        */
        private function process_login_using_twitch_helix( $state_code ) { 
                
            $helix = new TWITCHPRESS_Twitch_API();
            
            // Ensure code is ready...
            if( !twitchpress_validate_code( $_GET['code'] ) ) 
            {   
                $this->loginerror( __( 'Invalid Twitch Code'), 
                                   __( 'Your request to login via Twitch has failed because the code return by Twitch appears invalid. Please try again or report the issue.', 'twitchpress-login' ),
                                   null );                
                return;                   
            }
      
            // Generate a token, it is stored as user meta further down.
            $token_array = $helix->request_user_access_token( $_GET['code'], __FUNCTION__ );    
            
            // Confirm token was returned...  
            if( !twitchpress_was_valid_token_returned_from_helix( $token_array ) ) {   
                $this->loginerror( __( 'Invalid Token', 'twitchpress' ), 
                                   __( 'Your request to login via Twitch could not be complete because the giving token is invalid.', 'twitchpress' ),
                                   null ); 
                                   
                                   
                                   // TEMP REMOVE
                                   exit;
                                   
                                                                    
                return;                         
            }
 
            // Ensure the user:read:email scope has been accepted...
            if( !in_array( 'user:read:email', $token_array->scope ) ) {   
                $this->loginerror( __( 'User Permission Required', 'twitchpress' ), 
                                   __( 'Permission to read user email has not been giving - cannot complete login.', 'twitchpress' ),
                                   null );                                  
                return;                         
            }
                                
            // Get the visitors Twitch details...
            //$twitch_user = $helix->getUserObject_Authd( $token_array->access_token, $_GET['code'] );
            $twitch_user = $helix->get_user_by_bearer_token( $token_array->access_token );
 
            if( is_wp_error( $twitch_user ) || $twitch_user == false ) { 
                $this->loginerror( __( 'Scope Permission Missing: user_read_email', 'twitchpress' ), 
                                   __( 'Login by Twitch requires access to your email address using the "user_read" permission. This site is not setup to request it. Please report this problem to the site owner.', 'twitchpress' ),
                                   null ); 
            }
            
            // ['email] is required. 
            if( !isset( $twitch_user->email ) ) {        
                $this->loginerror( __( 'Email Address Missing', 'twitchpress' ), 
                                   __( 'Twitch returned some of your account information but your email address was not included in the data.', 'twitchpress' ),
                                   null );                                                    
                return;                        
            }
              
            // Santization before any database insert...
            $twitch_user->id                = sanitize_key( $twitch_user->id ); // 44322889,
            $twitch_user->bio               = sanitize_text_field( $twitch_user->description ); // "Just a gamer playing games and chatting. :)",
            $twitch_user->display_name      = sanitize_user( $twitch_user->display_name ); // "dallas",
            $twitch_user->email             = sanitize_email( $twitch_user->email ); // "email-address@provider.com",
            $twitch_user->email_verified    = true; // No longer provided by Twitch.tv API
            $twitch_user->logo              = esc_url_raw( $twitch_user->profile_image_url ) ; // "https://static-cdn.jtvnw.net/jtv_user_pictures/dallas-profile_image-1a2c906ee2c35f12-300x300.png",
            $twitch_user->name              = sanitize_user( $twitch_user->login, true ); // "dallas",
            $twitch_user->login             = sanitize_user( $twitch_user->login, true ); // "dallas",
                                          
            // Email processing - ['email_verified] is required and must be bool(true) by default.
            if( 'yes' == get_option( 'twitchpress_registration_requirevalidemail' ) ) 
            {
                if( !isset( $twitch_user->email_verified ) || !$twitch_user->email_verified ) 
                {
                    $this->loginerror( __( 'Email Address Not Verified'), 
                                       __( 'Your request to login via Twitch was refused because your email address has not been verified by Twitch. You will need to verify your email through Twitch and then register on this site.', 'twitchpress' ),
                                       null 
                    );  
        
                    return;                                             
                } 
            }
            
            // We can log the user into WordPress if they have an existing account.
            $wp_user = get_user_by( 'email', $twitch_user->email );
            
            // If visitor does not exist in WP database by email check for Twitch history using "_id".
            if( $wp_user === false ) 
            {     
                $args = array(
                    'meta_key'     => 'twitchpress_twitch_id',
                    'meta_value'   => $twitch_user->id,
                    'count_total'  => false,
                    'fields'       => 'all',
                ); 
                
                $get_users_results = get_users( $args ); 
                
                // We will not continue if there are more than one WP account with the same Twitch ID.     
                // This will be a very rare situation I think so we won't get too advanced on how to deal with it, yet! 
                if( isset( $get_users_results[1] ) ) 
                {       
                    $this->loginerror( __( 'Existing Accounts Detected' ), 
                                       __( 'Welcome back to this site. Your personal Twitch ID has been found linked to two or more accounts but neither of them contain the same email address found in your Twitch account. Please access your preferred account manually. Please also report this matter so we can consider deleting one of your accounts on this site.' ),
                                       null 
                    );      
                                              
                    return false;                            
                } 
                elseif( isset( $get_users_results[0] ) ) 
                {   
                    $wp_user_id = $get_users_results[0]->ID;
                             
                    // A single user has been found with the Twitch "_id" associated with it...
                    // We will further marry the WP account to Twitch account...
                    update_user_meta( $wp_user_id, 'twitchpress_twitch_id', $twitch_user->id );
                    update_user_meta( $wp_user_id, 'twitchpress_twitch_email', $twitch_user->email );
                    update_user_meta( $wp_user_id, 'twitchpress_twitch_bio', $twitch_user->description );
                    update_user_meta( $wp_user_id, 'twitchpress_twitch_display_name', $twitch_user->display_name );
                    update_user_meta( $wp_user_id, 'twitchpress_twitch_email_verified', true );
                    update_user_meta( $wp_user_id, 'twitchpress_twitch_name', $twitch_user->login );
                    update_user_meta( $wp_user_id, 'twitchpress_auth_time', time() );
                    update_user_meta( $wp_user_id, 'twitchpress_code', sanitize_text_field( $_GET['code'] ) );
                    update_user_meta( $wp_user_id, 'twitchpress_token', $token_array->access_token );
                    update_user_meta( $wp_user_id, 'twitchpress_token_refresh', $token_array->refresh_token );

                    // Log the user in.
                    self::authenticate_login_by_twitch( $get_users_results[0]->ID, $twitch_user->name, $state_code  );
                    
                    return;
                } 
            } 
            else 
            {      
                // A single user has been found matching the Twitch email address...
                // We will further marry the WP account to Twitch account...
                update_user_meta( $wp_user->data->ID, 'twitchpress_twitch_id', $twitch_user->id );
                update_user_meta( $wp_user->data->ID, 'twitchpress_twitch_email', $twitch_user->email );
                update_user_meta( $wp_user->data->ID, 'twitchpress_twitch_bio', $twitch_user->description );
                update_user_meta( $wp_user->data->ID, 'twitchpress_twitch_display_name', $twitch_user->display_name );
                update_user_meta( $wp_user->data->ID, 'twitchpress_twitch_email_verified', true );
                update_user_meta( $wp_user->data->ID, 'twitchpress_twitch_name', $twitch_user->login );
                update_user_meta( $wp_user->data->ID, 'twitchpress_auth_time', time() );
                update_user_meta( $wp_user->data->ID, 'twitchpress_code', sanitize_text_field( $_GET['code'] ) );
                update_user_meta( $wp_user->data->ID, 'twitchpress_token', $token_array->access_token );
                update_user_meta( $wp_user->data->ID, 'twitchpress_token_refresh', $token_array->refresh_token );

                self::authenticate_login_by_twitch( $wp_user->data->ID, $wp_user->data->user_login, $state_code );

                return;
            }
            
            // If automatic registration is not on then we do nothing else.
            if( 'yes' !== get_option( 'twitchpress_automatic_registration' ) ) 
            {     
                $this->loginerror( __( 'Manual Registration Required', 'twitchpress' ), 
                                   __( 'This site does not allow automatic registration using Twitch. Please go to the registration page and create an account using the same email address as used in your Twitch account.', 'twitchpress' ),
                                   null );   
                                                                          
                return;                         
            }
            
            // No user found and automatic registration is active so attempt to create a new account...
            $one = get_user_by( 'user_login', $twitch_user->login );
            $two = get_user_by( 'display_name', $twitch_user->display_name );
            $thr = get_user_by( 'user_nicename', $twitch_user->login );
                   
            if( is_object( $one ) && !is_wp_error( $one ) || is_object( $two ) && !is_wp_error( $two ) || is_object( $thr ) && !is_wp_error( $thr )  )
            {
                $this->loginerror( __( 'Could Not Create WordPress Account', 'twitchpress' ), 
                                   __( 'There is an existing account with a similar Twitch username to your channel. Is it possible you have already created an account on this website? Please contact administration so we can secure the best public username. We know your Twitch brand is important to you.', 'twitchpress' ),
                                   null );
                                                   
                return;   
            }
            
            $user_url = 'http://twitch.tv/' . $twitch_user->login;
            $password = wp_generate_password( 12, true );
            $new_user = array(
                'user_login'    =>  $twitch_user->login,
                'display_name'  =>  $twitch_user->display_name,
                'user_url'      =>  $user_url,
                'user_pass'     =>  $password, 
                'user_email'    =>  $twitch_user->email 
            );
                    
            $wp_user_id = wp_insert_user( $new_user ) ;

            if ( is_wp_error( $wp_user_id ) ) 
            {    
                $error_message_append = '
                <ul>
                    <li>Login: ' . $twitch_user->login . '</li>
                    <li>Display Name: ' . $twitch_user->display_name . '</li>
                    <li>User URL: ' . $user_url . '</li>
                    <li>Password: ' . $password . '</li>
                    <li>Email Address: ' . $twitch_user->email . '</li>
                </ul>
                <p>Please screenshot and report this notice.</p>';
                
                $this->loginerror( __( 'Could Not Create WordPress Account'), 
                                   __( 'TwitchPress attempted to register you but failed when using this information.' . $error_message_append, 'twitchpress' ),
                                   null );  
                                               
                return false;                        
            }      
                    
            // Store code and token in our new users meta.
            update_user_meta( $wp_user_id, 'twitchpress_twitch_id', $twitch_user->id );
            update_user_meta( $wp_user_id, 'twitchpress_twitch_email', $twitch_user->email );
            update_user_meta( $wp_user_id, 'twitchpress_twitch_bio', $twitch_user->description );
            update_user_meta( $wp_user_id, 'twitchpress_twitch_display_name', $twitch_user->display_name );
            update_user_meta( $wp_user_id, 'twitchpress_twitch_email_verified', true );
            update_user_meta( $wp_user_id, 'twitchpress_twitch_name', $twitch_user->login );  
            update_user_meta( $wp_user_id, 'twitchpress_auth_time', time() );
            update_user_meta( $wp_user_id, 'twitchpress_code', sanitize_text_field( $_GET['code'] ) );
            update_user_meta( $wp_user_id, 'twitchpress_token', $token_array->access_token );
            update_user_meta( $wp_user_id, 'twitchpress_token_refresh', $token_array->refresh_token );

            // Avatar Method One: works with the raw URL and filters the display of avatars...
            twitchpress_update_user_meta_avatar( $wp_user_id, $twitch_user->logo );
            
            // Store users original Twitch.tv logo URL in user meta for future use when permitted...
            twitchpress_update_user_meta_twitch_logourl( $wp_user_id, $twitch_user->logo );
            
            // Avatar Method Two: local WP media attachment of Twitch.tv logo for use as avatar...
            $attachment_id = twitchpress_save_twitch_logo( $wp_user_id, $twitch_user->logo, $twitch_user->display_name, $twitch_user->login );
            if( $attachment_id !== WP_Error ) {
                // Use the new media to create an avatar...
                update_user_meta( $wp_user_id, $wpdb->get_blog_prefix() . 'user_avatar', $attachment_id );         
            } 
                                   
            // Run sync which can import more data than the values above i.e. subscription status...
            twitchpress_sync_user_on_registration( $wp_user_id );
        
            // Now we need to authenticate the visitor and give them access to WP...
            self::authenticate_login_by_twitch( $wp_user_id, $twitch_user->display_name, $state_code );
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
  
                // A bit of a hack to tell WordPress which user is the current one. 
                wp_set_current_user( $wp_user_id );
                
                // Set authorization for the current visitor for the now "current_user" account.
                wp_set_auth_cookie( $wp_user_id ); 

                // We need the user_object for the 2nd parameter of the wp_login action.
                $user_object = get_user_by( 'ID', $wp_user_id );
              
                // Do the wp_login action to behave as the core would... 
                do_action( 'wp_login', $user_object->user_login, $user_object ); 
                
                // Do our own login_success and possibly redirect user to a custom logged-in page. 
                $this->login_success( $state_code );
                
                return true;    
            }
            
            return false;
        }
        
        /**
        * Last login access when the login was a success and auth has been
        * granted and setup by WordPress. 
        * 
        * @version 2.1
        */
        public function login_success( $state_code ) {     
            global $current_user;
            
            if( twitchpress_login_prevent_redirect() ) { return; }
            
            $transient = get_transient( 'twitchpress_oauth_' . $state_code );

            if( isset( $transient['successurl'] ) && is_string( $transient['successurl'] ) ) 
            {
                // Send user to custom set URL pass through shortcode.
                twitchpress_redirect_tracking( $transient['successurl'], __LINE__, __FUNCTION__, __FILE__ );     
                exit;    
            }
            elseif( isset( $transient['redirectto'] ) && is_string( $transient['redirectto'] ) )
            {
                // Send user to custom set URL pass through shortcode. 
                twitchpress_redirect_tracking( $transient['redirectto'], __LINE__, __FUNCTION__, __FILE__ );     
                exit;       
            }
            else 
            {    
                // Check for a successful logged-in page set using page ID.
                $loggedin_page_id = get_option( 'twitchpress_login_loggedin_page_id', false );
                if( $loggedin_page_id !== false && is_numeric( $loggedin_page_id ) ) 
                {
                    $permalink = get_post_permalink( $loggedin_page_id );
                    twitchpress_redirect_tracking( $permalink, __LINE__, __FUNCTION__, __FILE__ );
                    exit;                    
                }
                else
                {
                    $profile = get_bloginfo('url') . '/wp-admin/profile.php';       
                    twitchpress_redirect_tracking( $profile, __LINE__, __FUNCTION__, __FILE__ );
                    exit;
                }
            }
        }
        
        /**
        * Redirects all logging in users if the option has been activated...
        * 
        * @version 1.0
        */
        public function login_success_redirect_all( $redirect_to, $request, $user ) {       
            // Prevent error when not going through login procedure...
            if( !isset( $user->roles ) ) {  
                return $redirect_to;
            }       
            if( isset( $user->empty_username ) ) {                                              
                return $redirect_to;
            }                  
            
            // Exclude administrators...
            if( in_array( 'administrator', $user->roles ) ) {                               
                return $redirect_to; 
            }                                                                   
                
            // Ensure a custom page has been setup and redirect-all is wanted...
            if( get_option( 'twitchpress_login_loggedin_page_id', false ) && get_option( 'twitchpress_login_redirect_all' ) == 'yes' ) {
                return get_post_permalink( get_option( 'twitchpress_login_loggedin_page_id' ) );           
            }  
            
            return $redirect_to;
        }
        
        /**
        * Use the TwitchPress Custom Login Notices class to generate a 
        * a new notice on the login scree
        * 
        * @param mixed $message
        * 
        * @version 2.0
        */
        function loginerror( $title, $message, $link = null ) {
            $login_messages = new TwitchPress_Custom_Login_Messages();
            $login_messages->add_error( $message );             
        }
                    
        /**
        * Generates notice for a refusal or failure.
        * 
        * @version 1.0
        */
        public static function oauth2_failure() {           
            
            if( !isset( $_GET['error'] ) ) {
                return;
            }

            $message = '<strong>' . __( 'Twitch Refused Request: ', 'twitchpress-login') . '</strong>';
            
            $message .= sprintf( __( 'the %s error was returned.'), $_GET['error'] );            
            
            if( isset( $_GET['description'] ) ) {
                $message .= ' ' . $_GET['description'] . '.';        
            }
            
            $login_notices = new TwitchPress_Custom_Login_Messages();
            $login_notices->add_error( $message );
            unset( $login_notices );
        }

        /**
        * Display the Twitch Login button below the WP login form.
        * 
        * @version 2.2
        */
        public function twitch_button_below() {
            // This is the top (above) position button.
            if( 'below' !== get_option( 'twitchpress_login_loginpage_position' ) ) { return; }

            $kraken = new TWITCHPRESS_Twitch_API();

            // Ensure Twitch app is setup to avoid pointless API calls.
            $is_app_set = $kraken->is_app_set();
            if( !$is_app_set ) { return; }
                        
            $states_array = array( 'random14' => twitchpress_random14(), 'view' => 'default' );

            $authUrl = twitchpress_generate_authorization_url( twitchpress_get_visitor_scopes(), $states_array );
        
            echo "<h3 class='twitchpresslogin-or'>" . __( 'or' , 'twitchpress-login') . "</h3>";
            
            $this->button( $authUrl );
        }
        
        /**
        * Add a Twitch login button to the WordPress default login form. 
        * If the user has not registered it will also register them.
        * 
        * @version 2.0
        */
        public function twitch_button_above() {    
            
            // Ensure this button is activated.                                 
            if( 'yes' !== get_option( 'twitchpress_login_button' ) ) {
                return;
            }
            
            // Ensure user has set the login page type. 
            $type = get_option( 'twitchpress_login_loginpage_type', false );
            if( $type !== 'default' && $type !== 'both' ) {
                return;// Login page type must be "page". 
            }
            
            // This is the top (above) position button.
            if( 'above' !== get_option( 'twitchpress_login_loginpage_position' ) ) { return; }

            // Is auto login active? (sends visitors straight to Twitch oAuth2)
            $do_autologin = false;
            $temp_option_autologin = false;

            // We will not do this with helix, instead we will work towards a more global
            // value or an approach that disables services and takes such situations very serious!
            if( TWITCHPRESS_API_NAME == 'kraken' ) 
            {
                // Generate oAuth2 URL.
                $kraken = new TWITCHPRESS_Twitch_API();

                // Ensure Twitch app is setup to avoid pointless API calls.
                $is_app_set = $kraken->is_app_set();
                if( !$is_app_set ) {
                    return;
                }
            }

            // States array is used to process visitor on returning from Twitch.tv.
            // We can use these values later in this function but they are more important to generate_authorization_url()
            $states_array = array( 'random14' => twitchpress_random14(), 'view' => 'default' );
            
            // Generate oAuth2 request URL.
            $authUrl = twitchpress_generate_authorization_url( twitchpress_get_visitor_scopes(), $states_array );
        
            // Auto-in via Twitch - all traffic going to wp-login.php is wp_redirect() to an oAuth2 URL 
            if ( $temp_option_autologin ) {
                
                // Respect the option unless GET params mean we should remain on login page (e.g. ?loggedout=true)
                if (count($_GET) == (isset($_GET['redirect_to']) ? 1 : 0) 
                                        + (isset($_GET['reauth']) ? 1 : 0) 
                                        + (isset($_GET['action']) && $_GET['action']=='login' ? 1 : 0)) {
                    $do_autologin = true;
                }
                
                if (isset($_POST['log']) && isset($_POST['pwd'])) { // This was a WP username/password login attempt
                    $do_autologin = false;
                }
            }
            
            if ( $do_autologin ) {
                
                if ( !headers_sent() ) {

                    twitchpress_redirect_tracking( $authUrl, __LINE__, __FUNCTION__, __FILE__ );
                    exit;
                    
                } else { ?>
                
                    <p><b><?php printf( __( 'Redirecting to <a href="%s">%s</a>...' , 'twitchpress-login'), $authUrl, __( 'Login via Twitch', 'twitchpress' ) ); ?></b></p>
                    <script type="text/javascript">
                    window.location = "<?php echo $authUrl; ?>";
                    </script>
                    
                <?php 
                }
            }
            
            // Output the top positioned Twitch button.
            $this->button( $authUrl );
            ?>

            <script>
            jQuery(document).ready(function(){
                <?php ob_start(); /* Buffer javascript contents so we can run it through a filter */ ?>
                
                var loginform = jQuery('#loginform,#front-login-form');
                var googlelink = jQuery('div.twitchpresslogin');
                var poweredby = jQuery('div.twitchpresslogin-powered');

                    loginform.prepend("<h3 class='twitchpresslogin-or'><?php esc_html_e( 'or' , 'twitchpress-login'); ?></h3>");

                loginform.prepend(googlelink);

                <?php 
                    $fntxt = ob_get_clean(); 
                    echo apply_filters('twitchpress_login_form_readyjs', $fntxt);
                ?>
            });
            </script>
        
        <?php     
        }
        
        public function button( $authUrl ) {
            ?>
            <div class="twitchpresslogin">
                <a href="<?php echo $authUrl; ?>"><?php echo esc_html( $this->get_login_button_text() ); ?></a>
            </div>        
            <?php     
        }
        
        /**
        * Hides core username and password fields using CSS.
        * 
        * @version 2.0
        */
        public function hide_login() {
            
            if( 'yes' !== get_option( 'twitchpress_login_requiretwitch' ) ) { return; }     
            
            $style = '';

            $style .= '<style type="text/css">';
                
                // Display the TwitchPress login button-styled link with adjustments...
                $style .= 'body.login div#login form { padding: 20px 24px 18px }';
                $style .= 'body.login div#login div.twitchpress-connect-button-one { margin-bottom: 33px}';
                
                // Hide the username field...
                $style .= 'body.login div#login form#loginform p { display: none }';
                $style .= 'body.login div#login form#loginform p label { display: none }';
                
                // Hide the password field...
                $style .= 'body.login div#login form#loginform .user-pass-wrap { display: none }';

                // Hide the custom "or" text when two logon methods displayed...
                $style .= 'body.login div#login form#loginform .twitchpresslogin-or { display: none }';
                
                // Hide the login button (replaced by a Twitch button)...
                $style .= 'body.login div#login form#loginform p.submit { display: none }';

            $style .= '</style>';
   
            /*
            body.login {}
            body.login div#login {}
            body.login div#login h1 {}
            body.login div#login h1 a {}
            body.login div#login form#loginform {}
            body.login div#login form#loginform p {}
            body.login div#login form#loginform p label {}
            body.login div#login form#loginform input {}
            body.login div#login form#loginform input#user_login {}
            body.login div#login form#loginform input#user_pass {}
            body.login div#login form#loginform p.forgetmenot {}
            body.login div#login form#loginform p.forgetmenot input#rememberme {}
            body.login div#login form#loginform p.submit {}
            body.login div#login form#loginform p.submit input#wp-submit {}
            body.login div#login p#nav {}
            body.login div#login p#nav a {}
            body.login div#login p#backtoblog {}
            body.login div#login p#backtoblog a {}
            */ 

            echo $style; 
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