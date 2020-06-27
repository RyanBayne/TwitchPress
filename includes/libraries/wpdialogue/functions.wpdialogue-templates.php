<?php
/**
* Box for presenting the status and progress of something.
* 
* Includes HTML5 progress bars.
* 
* @author Ryan Bayne
* @package TwitchPress
* 
* @version 1.0
*/    
function wpdialogue_progress_box( $title, $intro, $progress_array = array() ){    
    echo '
    <div class="twitchpress_status_box_container">
        <div class="welcome-panel">
        
            <h3>' . ucfirst( $title ) . '</h3>
            
            <div class="welcome-panel-content">
                <p class="about-description">' . ucfirst( $intro ) . '...</p>
                
                <h4>Section Development Progress</h4>

                ' . wpdialogue_info_area( '', '                    
                Free Edition: <progress max="100" value="24"></progress> <br>
                Premium Edition: <progress max="100" value="36"></progress> <br>
                Support Content: <progress max="100" value="67"></progress> <br>
                Translation: <progress max="100" value="87"></progress>' ) .'
                <p>' . __( 'Pledge £9.99 to the TwitchPress project for 50% discount on the premium edition.' ) . '</p>                                                     
            </div>

        </div> 
    </div>';  
}
    
/**
* Present information at the head of a page with option to dismiss.   
* 
* @author Ryan Bayne
* @package TwitchPress
* 
* @version 1.0
*/
function wpdialogue_intro_box( $title, $intro, $info_area = false, $info_area_title = '', $info_area_content = '', $footer = false, $dismissable_id = false ){
    global $current_user;
                                           
    // handling user action - hide notice and update user meta
    if ( isset($_GET[ $dismissable_id ]) && 'dismiss' == $_GET[ $dismissable_id ] ) {
        add_user_meta( $current_user->ID, $dismissable_id, 'true', true );
        return;
    }
           
    // a dismissed intro
    if ( $dismissable_id !== false && get_user_meta( $current_user->ID, $dismissable_id ) ) {
        return;
    }
                          
    // highlighted area within the larger box
    $highlighted_info = '';
    if( $info_area == true && is_string( $info_area_title ) ) {
        $highlighted_info = '<h4>' . $info_area_title . '</h4>';
        $highlighted_info .= wpdialogue_info_area( false, $info_area_content );
    }
                  
    // footer text within the larger box, smaller text, good for contact info or a link
    $footer_text = '<br>';
    if( $footer ) {
        $footer_text = '<p>' . $footer . '</p>';    
    }

    // add dismiss button
    $dismissable_button = '';
    if( $dismissable_id !== false ) {
        $dismissable_button = sprintf( 
            ' <a href="%s&%s=dismiss" class="button button-primary"> ' . __( 'Hide', 'twitchpress' ) . ' </a>', 
            $_SERVER['REQUEST_URI'], 
            $dismissable_id 
        );
    }
            
    echo '
    <div class="twitchpress_status_box_container">
        <div class="welcome-panel">
            <div class="welcome-panel-content">
                
                <h1>' . ucfirst( $title ) . $dismissable_button . '</h1>
                
                <p class="about-description">' . ucfirst( $intro ) . '</p>

                ' . $highlighted_info . '
                
                ' . $footer_text . '
              
            </div>
        </div> 
    </div>';  
}

/**
* A mid grey area for attracting focus to a small amount of information. Is 
* an alternative to standard WordPress notices and good for tips. 
* 
* @param mixed $title
* @param mixed $message
* 
* @author Ryan Bayne
* @package TwitchPress 
* 
* @deprecated use wpdialogue_info_area() function
*/
function wpdialogue_info_area( $title, $message, $admin_only = true ){   
    if( $admin_only == true && current_user_can( 'manage_options' ) || $admin_only !== true){
        
        $area_title = '';
        if( $title ){
            $area_title = '<h4>' . $title . '</h4>';
        }
        
        return '
        <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
            ' . $area_title  . '
            <p>' . $message . '</p>
        </div>';          
    }
}