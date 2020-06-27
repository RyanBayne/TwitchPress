<?php
/**
 * TwitchPress - The object registry provides object access throughout WordPress
 * without using globals.  
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
           
if( !class_exists( 'TwitchPress_Object_Registry' ) ) :

class TwitchPress_Object_Registry {

    static $storage = array();

    static function add( $id, $class ) {
        self::$storage[ $id ] = $class;
        //return self::$storage[ $id ];  commented 26th March 2019 - this returns the same value as $class
    }

    static function get( $id ) {
        return array_key_exists( $id, self::$storage ) ? self::$storage[$id] : NULL;    
    }
    
    /**
    * Update the variable in the registry object.
    * 
    * @param string $id
    * @param string $var variable name
    * @param mixed $new new variable value
    * @param mixed $old old variable value
    */
    static function update_var( $id, $var, $new, $old = null ) {
        self::$storage[$id]->$var = $new;     
    }
    
    /**
    * Update a value already in the registry using add_action and this function...
    * 
    * @param mixed $args
    */
    static function update_var_action( $args ) {
        self::update_var( $args['id'], $args['var'], $args['new'] );    
    }
}

endif;

