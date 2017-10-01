<?php         
/**
 * TwitchPress - Options Table Interface
 *
 * Manage individual and groups of options. Contains methods for interfacing
 * with WordPress options. A key feature is controlling the update of individual
 * values in a group (array) of options. Risk of losting other values is removed.
 * 
 * This class also offers installation of options but that might be handled
 * by the main installation class and so we will visit this class later, removing
 * any redundent code. 
 * 
 * @todo Once TwitchPress is released, consider removing installation related methods.  
 *
 * @author   Ryan Bayne
 * @category Configuration
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} 

/** 
 * Handle all things "options".
 * 
 * @package WebTechGlobal WordPress Plugins
 * @author Ryan Bayne   
 * @since 1.0.0
 * @version 1.0 
 */
class TwitchPress_Options {
    use TwitchPress_OptionsTrait;
}

trait TwitchPress_OptionsTrait {
    
    /**
    * Array of the option types that are grouped (array of many options)
    * 
    * @var mixed
    */
    private static $grouped_options = array(      
        'compact' => 'twitchpress_options',
        'private' => 'twitchpress_private_options',
    );

    /**
    * Maintain a list of valid option names for quick validation and
    * building lists. 
    * 
    * @param mixed $type
    * @version 1.0
    * 
    * @deprecated use get_option_information() which holds default values also.
    */
    public static function get_option_names( $type = 'compact' ) {           
        switch ( $type ) {

            //Individual options here, will be prepended with "twitchpress".
            case 'non_compact' :
                return array(
                    'exampleoption1', // Example individual option.
                    'exampleoption2', // Example individual option.
                    'exampleoption3', // Example individual option.
                );                                                                                               
            break;
                              
            //Add security sensitive options here i.e. tokens, keys.
            case 'private' :
                return array();
            break;
        }

        // Return compact options by default.
        return array(
            'postdump', // (boolean) switch in Developer Menu for displaying $_POST.
            'getdump', // (boolean) switch in Developer Menu for displaying $_GET.
            'debugtracedisplay',
            'debugtracelog'
        );
    }

    /**
    * Created to replace get_options_names() which holds only option names.
    * 
    * This method holds option names and their default values. We can thus
    * query default values to correct missing options.
    * 
    * We can also set each option to be installed by default or by trigger i.e.
    * during procedure.      
    * 
    * @author Ryan R. Bayne
    * @param mixed $type
    * @version 1.1
    * 
    * @param mixed $type single|merged|secure|deprec
    * @param mixed $return all|keys|install|update|delete|value
    * @param string|array $name use to get specific option details
    * 
    * @todo complete $return by allowing specific information to be returned.
    * @todo complete $name which makes procedure return data for one or more options.
    * @todo add this method to get_options_names() and return keys only in it. 
    * @todo move installation options to compact.
    */
    public function get_option_information( $type = 'merged', $return = 'value', $name = array() ) {
        $selected_array = array();    
        
        /* 
            Types Explained
            Single - individual records in the WP options table.
            Merged - a single array of many options installed in WP options table.
            Secure - coded options, not installed in data, developer must configure.
            Deprec - depreciated option.

            Options Array Values Explained
            0. Install (0|1)  - add to options on activation of the plugin using add_option() only.
            1. Autoload (0|1) - autoload the option.
            2. Delete (0|1)   - delete when user uninstalls using form (most should be removed). 
            3. Value (mixed)  - options default value.    
        */
        
        switch ( $type ) {        
            case 'single':
            
                // Remember the real option names are prepend with "twitchpress".
                $single_options = array(  
                    // TwitchPress core options.                                  
                    'example1' => array( 1,1,1, 'thevalue1' ),// Description of option.     
                    'example2' => array( 1,0,1, 'thevalue2' ),// Description of option.
                    'example3' => array( 1,0,1, 'thevalue3' ),// Description of option.
                );  

                $selected_array = $single_options;

                break;
            case 'merged':     
           
                $merged_options = array(  
                    'postdump'               => array( 'merged',1,1,1, false ),// (boolean) Switch in Developer menu for displaying $_POST data.
                    'getdump'                => array( 'merged',1,1,1, false ),// (boolean) Switch in Developer menu for displaying $_GET dump.
                    'debugtracedisplay'      => array( 'merged',1,1,1, false ),// (boolean) Switch in Developer menu for displaying trace for the current page load.
                    'debugtracelog'          => array( 'merged',1,1,1, false ),// (boolean) Switch in Developer menu for logging trace for the current page load.
                );
                
                $selected_array = $merged_options; 
                        
                break;
            case 'secure':
                return;
                break;
            case 'deprec':
                return;
                break;    
        }
        
        if( $return == 'keys' )
        {             
            return array_keys( $selected_array );
        }
        else
        {
            return $selected_array;
        }            
    }

    /**
    * Install all options into the WordPress options table. 
    * Does not update, only adds and so this method is only suitable
    * for activation.
    * 
    * We focus on adding missing options when they are required after the
    * first time installation.
    * 
    * @version 1.1
    */
    public function install_options() {         
        $single_options = self::get_option_information( 'single', 'all' );
        $merged_options = self::get_option_information( 'merged', 'all' );
        $all_options = array_merge( $single_options, $merged_options );
        if( $all_options )
        {
            foreach( $all_options as $option_name => $option_information )
            {
                if( $option_information[0] === 1 )
                {
                    add_option( $option_name, $option_information[3], $option_information[1] );    
                }    
            }
        }
        return;
    }    

    /**
    * Deletes every option. Do not change. Create a new method
    * for any other approach to disable or uninstall a plugin please.
    * 
    * @version 1.0
    */
    public function uninstall_options() {
      $single_options = self::get_option_information( 'single', 'all' );
        $merged_options = self::get_option_information( 'merged', 'all' );
        $all_options = array_merge( $single_options, $merged_options );
        if( $all_options )
        {
            foreach( $all_options as $option_name => $option_information )
            {
                if( $option_information[2] === 1 )
                {
                    self::delete_option( $option_name );    
                }    
            }
        }
        return;
    }
    
    /**
    * Confirm that a required option or array of options
    * are valid by name.
    * 
    * Pass group if the option/s belong to a group and are not
    * stored as a seperate "non_compact" entry in the options table.
    * 
    * @param mixed $name
    * @param mixed $group
    * @return mixed
    */
    public static function is_valid( $name, $group = null ) {      
        if ( is_array( $name ) ) {
            $compact_names = array();
            foreach ( array_keys( self::$grouped_options ) as $_group ) {
                $compact_names = array_merge( $compact_names, self::get_option_names( $_group ) );
            }

            $result = array_diff( $name, self::get_option_names( 'non_compact' ), $compact_names );

            return empty( $result );
        }

        if ( is_null( $group ) || 'non_compact' === $group ) {
            if ( in_array( $name, self::get_option_names( $group ) ) ) {
                return true;
            }
        }

        foreach ( array_keys( self::$grouped_options ) as $_group ) {
            if ( is_null( $group ) || $group === $_group ) {
                if ( in_array( $name, self::get_option_names( $_group ) ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns the requested option.  Looks in twitchpress_options group 
     * or twitchpress_$name as appropriate.
     *
     * @version 1.2
     * @param string $name Option name
     * @param mixed $default (optional)
     * 
     * @todo how can we get a grouped option without giving the group?
     * If two groups have the same option name the returned value could be
     * wrong. Add some lines that compares all groups and raises a specific
     * error advising the developer to change the option name.
     */
    public static function get_option( $name, $default = false, $maybe_unserialize = true ) {
                                    
        // First check if the requested option is a non_compact one.
        if ( self::is_valid( $name, 'non_compact' ) ) {
            $option_value = get_option( "twitchpress_$name", $default );
            if( $maybe_unserialize )
            {
                return maybe_unserialize( $option_value );
            }
        }

        // Must be a grouped option, loop through groups.
        foreach ( array_keys( self::$grouped_options ) as $group ) {
            if ( self::is_valid( $name, $group ) ) {
                return self::get_grouped_option( $group, $name, $default );
            }
        }

        trigger_error( sprintf( 'Invalid TwitchPress option name: %s', $name ), E_USER_WARNING );

        return $default;
    }

    /**
    * Update a giving grouped option. Will add the $value if it
    * does not already exist. The $name is the key.
    * 
    * @param mixed $group
    * @param mixed $name
    * @param mixed $value
    */                               
    private function update_grouped_option( $group, $name, $value ) {
        $options = get_option( self::$grouped_options[ $group ] );
        if ( ! is_array( $options ) ) {
            $options = array();
        }
        $options[ $name ] = $value;

        return update_option( self::$grouped_options[ $group ], $options );
    }

    /**
     * Updates the single given option.
     * Updates twitchpress_options or jetpack_$name as appropriate.
     *
     * @param string $name Option name
     * @param mixed $value Option value
     * @param string $autoload If not compact option, allows specifying whether to autoload or not
     * 
     * @todo Check original functions use of do('pre_update_jetpack_option_
     * which requires add_action that calls the delete method in this class.
     * Why delete every option prior to update?
     */
    public function update_option( $name, $value, $autoload = null ) {    
        if ( self::is_valid( $name, 'non_compact' ) ) {
            /**             
             * Allowing update_option to change autoload status only shipped in WordPress v4.2
             * @link https://github.com/WordPress/WordPress/commit/305cf8b95
             */
            if ( version_compare( $GLOBALS['wp_version'], '4.2', '>=' ) ) {
                return update_option( "twitchpress_$name", $value, $autoload );
            }
            return update_option( "twitchpress_$name", $value );
        }

        foreach ( array_keys( self::$grouped_options ) as $group ) {
            if ( self::is_valid( $name, $group ) ) {
                return self::update_grouped_option( $group, $name, $value );
            }
        }

        trigger_error( sprintf( 'Invalid TwitchPress option name: %s', $name ), E_USER_WARNING );

        return false;
    }

    /**
     * Updates the multiple given options.  Updates jetpack_options and/or 
     * jetpack_$name as appropriate.
     *
     * @param array $array array( option name => option value, ... )
     */
    public function update_options( $array ) {
        $names = array_keys( $array );

        foreach ( array_diff( $names, self::get_option_names(), self::get_option_names( 'non_compact' ), self::get_option_names( 'private' ) ) as $unknown_name ) {
            trigger_error( sprintf( 'Invalid TwitchPress option name: %s', $unknown_name ), E_USER_WARNING );
            unset( $array[ $unknown_name ] );
        }

        foreach ( $names as $name ) {
            self::update_option( $name, $array[ $name ] );
        }
    }

    /**
     * Deletes the given option.  May be passed multiple option names as an array.
     * Updates twitchpress_options and/or deletes twitchpress_$name as appropriate.
     *
     * @param string|array $names
     */
    public function delete_option( $names ) {       
        $result = true;
        $names  = (array) $names;

        if ( ! self::is_valid( $names ) ) {
            trigger_error( sprintf( 'Invalid TwitchPress option names: %s', print_r( $names, 1 ) ), E_USER_WARNING );

            return false;
        }

        foreach ( array_intersect( $names, self::get_option_names( 'non_compact' ) ) as $name ) {
            if ( ! delete_option( "twitchpress_$name" ) ) {
                $result = false;
            }
        }

        foreach ( array_keys( self::$grouped_options ) as $group ) {
            if ( ! self::delete_grouped_option( $group, $names ) ) {
                $result = false;
            }
        }

        return $result;
    }

    /**
    * Get one of many groups of options then return a value from within the
    * group.
    * 
    * @param string $group non_compact, private, compact 
    * @param mixed $name
    * @param mixed $default
    */
    private static function get_grouped_option( $group, $name, $default = null ) {
        $options = get_option( self::$grouped_options[ $group ] );
        
        // Does the group have the giving option name?
        if ( is_array( $options ) && isset( $options[ $name ] ) ) {
            return $options[ $name ];
        }

        return $default;
    }

    /**
    * Delete an option value from grouped options.
    * 
    * @param mixed $group
    * @param mixed $names
    */
    private function delete_grouped_option( $group, $names ) {         
        $options = get_option( self::$grouped_options[ $group ], array() );

        $to_delete = array_intersect( $names, self::get_option_names( $group ), array_keys( $options ) );
        if ( $to_delete ) {
            foreach ( $to_delete as $name ) {
                unset( $options[ $name ] );
            }

            return update_option( self::$grouped_options[ $group ], $options );
        }

        return true;
    }

}