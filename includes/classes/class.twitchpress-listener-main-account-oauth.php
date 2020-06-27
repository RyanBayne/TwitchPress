<?php
/**
 * Administrator level listener for assigning oAuth credentials to the main user.
 *  
 * This class waits for a Twitch.tv oAuth code to be returned in URL and stores
 * the code assuming that the Twitch account is the main/official one. It will then
 * be used on behalf of the owner where needed.
 * 
 * This is seperate from the applications token and the access the app credentials have
 * without the specified main account. 
 * 
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Core
 * @version  1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TWITCHPRESS_Listener_Main_Twitchtv_Account_oAuth' ) ) :

class TWITCHPRESS_Listener_Main_Twitchtv_Account_oAuth_Kraken {
    public $rejection_reason = 'No incoming request detected.';
    public $scope = null;
    public $state = null;
    public $code = null;
    public $wp_user_id = null;
    public $access_token = null;
    public $refresh_token = null;
    public $returned_scope = null;
    public $redirect_to = null;
    
    function listen() {
        
        // Is the request a return from Twitch?
        if( !$this->detect() ){
            return; 
        }  

        // This object is used by methods following it's initialization...
        $this->api_calls = new TWITCHPRESS_Twitch_API_Calls();

        // detect() establishes what we need to request a token.  
        $this->token();   

        // Did we get an access token and refresh token? 
        if( !$this->access_token || !$this->refresh_token ){  
            return;
        }

        // Store all credentials now in this class object.
        $this->store();

        // Make a test call and confirm channel details. 
        $this->channel();
        
        unset( $this->api_calls );

        // Storage went well, redirect the user to a suitable page. 
        $this->redirect();
        exit;
    }
    
    function detect() {
        
        if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {   
            return false;
        }
                 
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {    
            return false;
        }
         
        if( defined( 'DOING_CRON' ) && DOING_CRON ) {    
            return false;    
        }        
         
        if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {      
            return false;    
        }
          
        if( !is_user_logged_in() ) {         
            return false;
        }      
        
        $this->wp_user_id = get_current_user_id();  
        
        // This is not a listener for processing WordPress logins.
        // This $_GET is set by TwitchPress_Login_Extension.
        if( isset( $_GET['twitchpress_sentto_login'] ) ) {
            return false;
        }
                
        if( isset( $_GET['error'] ) ) {  
            return false;
        } 
         
        if( !isset( $_GET['scope'] ) ) {       
            return false;
        }
        
        $this->scope = sanitize_text_field( $_GET['scope'] );     
            
        if( !isset( $_GET['state'] ) || !is_string( $_GET['state'] ) ) {       
            return false;
        }    
        
        $this->state = sanitize_key( $_GET['state'] );
        
        // Change to true when $_REQUEST cannot be validated. 
        $return = false;
          
        if( !isset( $_GET['code'] ) ) {       
            $return = true;
            $this->rejection_reason = __( 'Main Account Listener: No code returned.', 'twitchpress' );
        }

        // We require the local state value stored in transient. 
        elseif( !$transient_state = get_transient( 'twitchpress_oauth_' . $this->state ) ) {       
            $return = true;
            $this->rejection_reason = __( 'Main Account Listener: No matching transient.', 'twitchpress' );
        }  
        
        // Ensure the reason for this request is an attempt to set the main channels credentials
        elseif( !isset( $transient_state['reason'] ) ) {
            $return = true;
            $this->rejection_reason = __( 'Main Account Listener: Reason not provided for this request.', 'twitchpress' );            
        }              
         
        // Ensure we have the admin view or page the user needs to be sent to. 
        elseif( $transient_state['reason'] !== 'mainchannelsetup' ) {         
            $return = true;
            $this->rejection_reason = __( 'Main Account Listener: Request reason rejected for this procedure.', 'twitchpress' );    
        }
                 
        // Ensure we have the admin view or page the user needs to be sent to. 
        elseif( !isset( $transient_state['redirectto'] ) ) {         
            $return = true;
            $this->rejection_reason = __( 'Main Account Listener: The redirectto value does not exist.', 'twitchpress' );    
        } 
          
        // For this procedure the userrole MUST be administrator.
        elseif( !isset( $transient_state['userrole'] ) ) {        
            $return = true;
            $this->rejection_reason = __( 'Main Account Listener: this request is not an expected operation related to the main account.', 'twitchpress' );    
        }          
        
        elseif( !isset( $transient_state['userrole'] ) || 'administrator' !== $transient_state['userrole'] ) {        
            $return = true;
            $this->rejection_reason = __( 'Main Account Listener: User is not an administrator.', 'twitchpress' );    
        }         
                
        // NEW IF - Validate the code as a measure to prevent URL spamming that gets further than here.
        elseif( !twitchpress_validate_code( $_GET['code'] ) ) {        
            $return = true;
            $this->rejection_reason = __( 'Main Account Listener: Code is invalid.', 'twitchpress' );
        }
                          
        if( $return === true ) {
            return false;
        } 

        $this->redirect_to = $transient_state['redirectto'];
        $this->code = sanitize_key( $_GET['code'] );
        
        // This is a genuine Twitch.tv response! 
        return true;
    }
    
    /**
    * Request a token.
    * 
    * @version 1.0
    */
    function token() {
        // Request oAuth token for the current user and the main channel. 
        $token_array = $this->api_calls->request_user_access_token( $this->code, __FUNCTION__ );
        unset( $twitch_api_calls );   

        if( !$token_array ) 
        {   
            $this->rejection_reason = __( 'Twitch.tv rejected a request for a user token.', 'twitchpress' );
            return false;
        }     
        
        $this->access_token = $token_array['access_token'];
        $this->refresh_token = $token_array['refresh_token'];
        $this->returned_scope = $token_array['scope'];
    }
    
    /**
    * Store credentials. 
    * 
    * @version 1.0
    */
    function store() {
            
        // Update current user although this is a main channel procedure.
        // This is to enforce the user as the owner of the logged in Twitch account and the main channel. 
        twitchpress_update_user_code( $this->wp_user_id, $this->code );       
        twitchpress_update_user_token( $this->wp_user_id, $this->access_token );
        twitchpress_update_user_token_refresh( $this->wp_user_id, $this->refresh_token );        

        // Start storing main channel credentials.  
        update_option( 'twitchpress_main_code', $this->code );// old value, being phased out.
        twitchpress_update_main_channels_code( $this->code ); 
        twitchpress_update_main_channels_wpowner_id( $this->wp_user_id );
        twitchpress_update_main_channels_token( $this->access_token ); 
        twitchpress_update_main_channels_refresh_token( $this->refresh_token );
        twitchpress_update_main_channels_scopes( $this->returned_scope );                                    
        
        TwitchPress_Admin_Notices::add_custom_notice( 'mainkrakenapplicationsetup', __( 'Twitch.tv provided a token to allow this site to access your channel based on the permissions (scopes) you selected.')  );    
    }
    
    /**
    * Make a test call by getting the current users Twitch channel and take
    * the opportunity to get any data we do not yet have.
    * 
    * @version 2.0
    */
    function channel() {
        // Confirm the giving main (default) channel is valid. 
        $user_objects = $this->api_calls->get_users( twitchpress_get_main_channels_name() );
        
        if( !isset( $user_objects['users'][0]['_id'] ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'listenerchanneldoesnotexist', __( '<strong>Channel Was Not Confirmed:</strong> TwitchPress wants to avoid errors in future by ensuring what you typed is correct. So far it could not confirm your entered channel is correct. Please check the spelling of your channel and the status of Twitch. If your entered channel name is correct and Twitch is online, please report this message.', 'twitchpress' ) );      
            return;                         
        } 

        twitchpress_update_main_channels_id( $user_objects['users'][0]['_id'] );
        
        // Assume the channel is owned by the current logged in admin or they just want it paired with the current WP account. 
        // Store all possible details in user meta. 
        twitchpress_update_user_oauth( 
            $this->wp_user_id, 
            $this->code, 
            $this->access_token, 
            $user_objects['users'][0]['_id'] 
        );        
    }
    
    /**
    * Forward user to the custom destinaton i.e. where they were before oAuth2.
    * 
    * @version 1.0
    */
    function redirect() { 
        twitchpress_redirect_tracking( $this->redirect_to, __LINE__, __FUNCTION__ );
        exit;       
    }
}

class TWITCHPRESS_Listener_Main_Twitchtv_Account_oAuth_Helix {
    public $rejection_reason = 'No incoming request detected.';
    public $scope = null;
    public $state = null;
    public $code = null;
    public $wp_user_id = null;
    public $access_token = null;
    public $refresh_token = null;
    public $returned_scope = null;
    public $redirect_to = null;
    
    function listen() {
        
        // Is the request a return from Twitch?
        if( !$this->detect() )
        {
            return; 
        }  
               
        // We will need to make a call to Twitch. 
        $this->api_calls = new TwitchPress_Twitch_API();
               
        // detect() establishes what we need to request a token.  
        $this->token();   

        // Did we get a refresh token? 
        if( !$this->refresh_token )
        {         
            return;
        }         
                  
        // Did we get an access token? 
        if( !$this->access_token )
        {        
            return;
        }
         
        // Store all credentials now in this class object.
        $this->store();

        // Make a test call and confirm channel details. 
        $this->channel();

        // Storage went well, redirect the user to a suitable page. 
        $this->redirect();
        exit;
    }
    
    function detect() {
                     
        if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {   
            return false;
        }             
    
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {    
            return false;
        }
         
        if( defined( 'DOING_CRON' ) && DOING_CRON ) {  
            return false;    
        }        
         
        if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {      
            return false;    
        }
        
        // This listener is for requests started on administration side only.  
        if( !is_user_logged_in() ) {         
            return false;
        }      
        
        $this->wp_user_id = get_current_user_id();  
        
        // This is not a listener for processing WordPress logins.
        // This $_GET is set by TwitchPress_Login_Extension.
        if( isset( $_GET['twitchpress_sentto_login'] ) ) {   
            return false;
        }
                
        // Create a notice for an error.
        if( isset( $_GET['error'] ) ) {      
            return false;
        } 
         
        if( !isset( $_GET['scope'] ) ) {    
            return false;
        }
        
        $this->scope = sanitize_text_field( $_GET['scope'] );     
            
        if( !isset( $_GET['state'] ) || !is_string( $_GET['state'] ) ) {       
            return false;
        }    
        
        $this->state = sanitize_key( $_GET['state'] );
        
        // Change to true when $_REQUEST cannot be validated. 
        $return = false;
          
        if( !isset( $_GET['code'] ) ) {       
            $return = true;
            $this->rejection_reason = __( 'Main Account Listener: No code returned.', 'twitchpress' );
        }

        // We require the local state value stored in transient. 
        elseif( !$transient_state = get_transient( 'twitchpress_oauth_' . $this->state ) ) {       
            $return = true;      
            $this->rejection_reason = __( 'Main Account Listener: No matching transient.', 'twitchpress' );
        }  
        
        // Ensure the reason for this request is an attempt to set the main channels credentials
        elseif( !isset( $transient_state['reason'] ) ) {
            $return = true;      
            $this->rejection_reason = __( 'Main Account Listener: Reason not provided for this request.', 'twitchpress' );            
        }              
         
        // Ensure we have the admin view or page the user needs to be sent to. 
        elseif( $transient_state['reason'] !== 'mainchannelsetup' ) {         
            $return = true;      
            $this->rejection_reason = __( 'Main Account Listener: Request reason rejected for this procedure.', 'twitchpress' );    
        }
                 
        // Ensure we have the admin view or page the user needs to be sent to. 
        elseif( !isset( $transient_state['redirectto'] ) ) {         
            $return = true;     
            $this->rejection_reason = __( 'Main Account Listener: The redirectto value does not exist.', 'twitchpress' );    
        } 
          
        // For this procedure the userrole MUST be administrator.
        elseif( !isset( $transient_state['userrole'] ) ) {        
            $return = true;     
            $this->rejection_reason = __( 'Main Account Listener: this request is not an expected operation related to the main account.', 'twitchpress' );    
        }          
        
        elseif( !isset( $transient_state['userrole'] ) || 'administrator' !== $transient_state['userrole'] ) {        
            $return = true;    
            $this->rejection_reason = __( 'Main Account Listener: User is not an administrator.', 'twitchpress' );    
        }         
                
        // NEW IF - Validate the code as a measure to prevent URL spamming that gets further than here.
        elseif( !twitchpress_validate_code( $_GET['code'] ) ) {        
            $return = true;      
            $this->rejection_reason = __( 'Main Account Listener: Code is invalid.', 'twitchpress' );
        }

        if( $return === true ) {    
            return false;
        }
        
        // No reason to reject the request...
        $this->rejection_reason = __( 'Not rejected', 'twitchpress' ); 
                               
        $this->redirect_to = $transient_state['redirectto'];
        $this->code = sanitize_key( $_GET['code'] );
                       
        // This is a genuine Twitch.tv response! 
        return true;
    }
    
    /**
    * Request a token.
    * 
    * @version 1.0
    */
    function token() {   
    
        // Request oAuth token for the current user and the main channel. 
        $token_array = $this->api_calls->request_user_access_token( $this->code, __FUNCTION__ );
 
        if( !$token_array ) 
        {
            $this->rejection_reason = __( 'Twitch.tv rejected a request for a user token.', 'twitchpress' );
            return false;
        }     
        
        $this->access_token = $token_array->access_token;
        $this->refresh_token = $token_array->refresh_token;
        $this->returned_scope = $token_array->scope;
    }
    
    /**
    * Store credentials. 
    * 
    * @version 1.0
    */
    function store() {
            
        // Update current user although this is a main channel procedure.
        // This is to enforce the user as the owner of the logged in Twitch account and the main channel. 
        twitchpress_update_user_code( $this->wp_user_id, $this->code );       
        twitchpress_update_user_token( $this->wp_user_id, $this->access_token );
        twitchpress_update_user_token_refresh( $this->wp_user_id, $this->refresh_token );        

        // Start storing main channel credentials.  
        update_option( 'twitchpress_main_code', $this->code );// old value, being phased out.
        twitchpress_update_main_channels_code( $this->code ); 
        twitchpress_update_main_channels_wpowner_id( $this->wp_user_id );
        twitchpress_update_main_channels_token( $this->access_token ); 
        twitchpress_update_main_channels_refresh_token( $this->refresh_token );
        twitchpress_update_main_channels_scopes( $this->returned_scope );                                    
        
        // Store main channel scopes as the app scopes which is simply a way of
        // managing the applicable scopes when making requests...
        twitchpress_update_app_token_scopes( $this->returned_scope );
        
        TwitchPress_Admin_Notices::add_custom_notice( 'mainkrakenapplicationsetup', __( 'Twitch.tv provided a token to allow this site to access your channel based on the permissions (scopes) you selected.')  );    
    }
    
    /**
    * Make a test call by getting the current users Twitch channel and take
    * the opportunity to get any data we do not yet have.
    * 
    * @version 2.0
    */
    function channel() {
        // Confirm the giving main (default) channel is valid. 
        $user_objects = $this->api_calls->get_user_without_email_by_login_name( twitchpress_get_main_channels_name() );

        if( !isset( $user_objects->data[0]->id ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'listenerchanneldoesnotexist', __( '<strong>Channel Was Not Confirmed:</strong> TwitchPress wants to avoid errors in future by ensuring what you typed is correct. So far it could not confirm your entered channel is correct. Please check the spelling of your channel and the status of Twitch. If your entered channel name is correct and Twitch is online, please report this message.', 'twitchpress' ) );      
            return;                         
        } 

        twitchpress_update_main_channels_id( $user_objects->data[0]->id );
        
        // Assume the channel is owned by the current logged in admin or they just want it paired with the current WP account. 
        // Store all possible details in user meta. 
        twitchpress_update_user_oauth( 
            $this->wp_user_id, 
            $this->code, 
            $this->access_token, 
            $user_objects->data[0]->id 
        );        
    }
    
    /**
    * Forward user to the custom destinaton i.e. where they were before oAuth2.
    * 
    * @version 1.0
    */
    function redirect() { 
        twitchpress_redirect_tracking( $this->redirect_to, __LINE__, __FUNCTION__ );
        exit;       
    }
}
        
if( TWITCHPRESS_API_NAME == 'kraken' )
{   
    add_action( 'init', array( new TWITCHPRESS_Listener_Main_Twitchtv_Account_oAuth_Kraken(), 'listen' ), 50 );
}
else
{ 
    add_action( 'init', array( new TWITCHPRESS_Listener_Main_Twitchtv_Account_oAuth_Helix(), 'listen' ), 50 );   
}

endif;