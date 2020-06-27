<?php
/** 
* WordPress database interaction covering common queries
* 
* @package WordPress/Plugins/TwitchPress
* @author Ryan Bayne   
* @since 3.1.0
*/

// load in WordPress only
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

function twitchpress_db_selectrow( $tablename, $condition, $select = '*' ){
    global $wpdb;
    if(empty( $condition) ){
        return null;
    }
    return $wpdb->get_row( "SELECT $select FROM $tablename WHERE $condition", OBJECT );
}

function twitchpress_db_selectorderby( $tablename, $condition = null, $orderby = null, $select = '*', $limit = '', $object = 'OBJECT' ){
    global $wpdb;
    $condition = empty ( $condition)? '' : 'WHERE ' . $condition;
    $condition .= empty( $orderby )? '': ' ORDER BY ' . $orderby;
    if(!empty( $limit) ){ $limit = 'LIMIT ' . $limit; }
    $query = "SELECT $select FROM $tablename $condition $limit";
    return $wpdb->get_results( $query, $object );
}

/**
* SELECT (optional WHERE) query returning any passed object
* 
* @param mixed $tablename
* @param mixed $condition
* @param mixed $orderby
* @param mixed $select
* 
* @version 1.1
*/
function twitchpress_db_selectwherearray( $tablename, $condition = null, $orderby = null, $select = '*', $object = 'ARRAY_A', $sort = null ){
    global $wpdb;
    $condition = empty ( $condition)? '' : ' WHERE ' . $condition;
    $condition .= empty( $orderby )? '': ' ORDER BY ' . $orderby;
    if( $sort == 'ASC' || $sort == 'DESC' ){ $condition .= ' ' . $sort; }
    return $wpdb->get_results( "
        SELECT $select 
        FROM $tablename 
        $condition", 
        $object
    );
} 

/**
* insert a new row to any table
* 
* @version 2.0 
* 
* @param string $tablename
* @param array $fields
*/
function twitchpress_db_insert( $tablename, $fields ){
    global $wpdb;
    $fieldss = '';
    $values = '';
    $first = true;
    
    foreach( $fields as $field => $value )
    {
         if( $first )
         {
            $first = false;
         }
         else
         {
            $fieldss .= ',';
            $values .= ',';
         }
         
         $fieldss .= "`$field`";
         $values .= "'" . esc_sql( $value ) ."'";
    }

    $wpdb->query(
        "INSERT INTO $tablename ( $fieldss ) 
         VALUES ( $values )"  
    );  
    
    return $wpdb->insert_id;
}

/**
* Standard update query
* 
* @author Ryan R. Bayne
* @version 1.5
*/
function twitchpress_db_update( $tablename, $condition, $fields ){
    global $wpdb;
    $query = " UPDATE $tablename SET ";
    $first = true;
    foreach( $fields as $field => $value )
    {
        if( $first) $first = false; else $query .= ' , ';
        $query .= " `$field` = '" . $value ."' ";
    }

    $query .= empty( $condition)? '': " WHERE $condition ";
    return $wpdb->query( $query );
}   

/**
* Basic delete query
* 
* @author Ryan R. Bayne
* @version 1.1
*/
function twitchpress_db_delete( $tablename, $condition ){
    global $wpdb;
    return $wpdb->query( "DELETE FROM $tablename WHERE $condition ");
}

/**
* count the number of rows in giving table with optional arguments if giving
* 
* @author Ryan R. Bayne
* @version 1.1
*/
function twitchpress_db_count_rows( $tablename, $where = '' ){
    global $wpdb;      
    return $wpdb->get_var( "SELECT COUNT(*) FROM $tablename" . $where );
}  
  
/**
* get a single value from a single row
* 
* @author Ryan R. Bayne
* @version 1.1
*/
function twitchpress_db_get_value( $columns, $tablename, $conditions ){
    global $wpdb;
    return $wpdb->get_var( "SELECT $columns FROM $tablename WHERE $conditions" );
}  

/**
* Gets posts with the giving meta value
* 
* @author Ryan R. Bayne
* @version 1.1 
* 
* @param mixed $meta_key
* @param mixed $meta_value
* @param mixed $limit
* @param mixed $select add table reference wpostmeta if adding meta table columns to select
* @param mixed $where begin string with AND
* @param mixed $querytype
*/
function twitchpress_db_get_posts_join_meta( $meta_key, $meta_value, $limit = 1, $select = '*', $where = '', $querytype = 'get_results' ){
    global $wpdb;
    
    $q = "SELECT wposts.".$select."
    FROM ".$wpdb->posts." AS wposts
    INNER JOIN ".$wpdb->postmeta." AS wpostmeta
    ON wpostmeta.post_id = wposts.ID
    AND wpostmeta.meta_key = '".$meta_key."'                                                 
    AND wpostmeta.meta_value = '".$meta_value."' 
    ".$where."
    LIMIT ".$limit."";
 
    if( $querytype == 'query' ){
        $result = $wpdb->query( $q);    
    }elseif( $querytype == 'get_var' ){
        $result = $wpdb->get_var( $q );        
    }else{
        $result = $wpdb->get_results( $q, OBJECT );    
    }
    
    return $result;
}

/**
* Function for validating values
* 
* @author Ryan R. Bayne
* @version 1.1 
* 
* @access private
*/
function twitchpress_db__sql_validate_value( $var){
    if (is_null( $var) )
    {
        return 'NULL';
    }
    else if (is_string( $var) )
    {
        return "'" . $this->sql_escape( $var) . "'";
    }
    else
    {
        return (is_bool( $var) ) ? intval( $var) : $var;
    }
}  
  
/**
* Build sql statement from array for insert/update/select statements
* 
* @author Ryan R. Bayne
* @version 1.1 
* 
* Idea for this from Ikonboard
* Possible query values: INSERT, INSERT_SELECT, UPDATE, SELECT
*/
function twitchpress_db_sql_build_array( $query, $assoc_ary = false ){
    if (!is_array( $assoc_ary ) ){
        return false;
    }

    $fields = $values = array();

    if ( $query == 'INSERT' || $query == 'INSERT_SELECT' )
    {
        foreach ( $assoc_ary as $key => $var)
        {
            $fields[] = $key;

            if (is_array( $var) && is_string( $var[0] ) )
            {
                // This is used for INSERT_SELECT(s)
                $values[] = $var[0];
            }
            else
            {
                $values[] = $this->_sql_validate_value( $var);
            }
        }

        $query = ( $query == 'INSERT' ) ? ' ( ' . implode( ', ', $fields) . ' ) VALUES ( ' . implode( ', ', $values) . ' )' : ' ( ' . implode( ', ', $fields) . ' ) SELECT ' . implode( ', ', $values) . ' ';
    }
    else if ( $query == 'UPDATE' || $query == 'SELECT' )
    {
        $values = array();
        foreach ( $assoc_ary as $key => $var)
        {
            $values[] = "$key = " . $this->_sql_validate_value( $var);
        }
        $query = implode(( $query == 'UPDATE' ) ? ', ' : ' AND ', $values);
    }

    return $query;
}    

/**
* Uses get_results and finds all DISTINCT meta_keys, returns the result.
* Currently does not have any measure to ensure keys are custom field only.
* 
* @author Ryan R. Bayne
* @version 1.1 
*/
function twitchpress_db_customfield_keys_distinct() {
    global $wpdb;
    return $wpdb->get_results( "SELECT DISTINCT meta_key FROM $wpdb->postmeta 
                                  WHERE meta_key != '_encloseme' 
                                  AND meta_key != '_wp_page_template'
                                  AND meta_key != '_edit_last'
                                  AND meta_key != '_edit_lock'
                                  AND meta_key != '_wp_trash_meta_time'
                                  AND meta_key != '_wp_trash_meta_status'
                                  AND meta_key != '_wp_old_slug'
                                  AND meta_key != '_pingme'
                                  AND meta_key != '_thumbnail_id'
                                  AND meta_key != '_wp_attachment_image_alt'
                                  AND meta_key != '_wp_attachment_metadata'
                                  AND meta_key != '_wp_attached_file'");    
}

/**
* Uses get_results and finds all DISTINCT meta_keys, returns the result  
* 
* @author Ryan R. Bayne
* @version 1.1 
*/
function twitchpress_db_metakeys_distinct() {
    global $wpdb;
    return $wpdb->get_results( "SELECT DISTINCT meta_key FROM $wpdb->postmeta 
                                  WHERE meta_key != '_encloseme' 
                                  AND meta_key != '_wp_page_template'
                                  AND meta_key != '_edit_last'
                                  AND meta_key != '_edit_lock'
                                  AND meta_key != '_wp_trash_meta_time'
                                  AND meta_key != '_wp_trash_meta_status'
                                  AND meta_key != '_wp_old_slug'
                                  AND meta_key != '_pingme'
                                  AND meta_key != '_thumbnail_id'
                                  AND meta_key != '_wp_attachment_image_alt'
                                  AND meta_key != '_wp_attachment_metadata'
                                  AND meta_key != '_wp_attached_file'");    
}

/**
* counts total records in giving project table
* 
* @author Ryan R. Bayne
* @version 1.1
*  
* @return 0 on fail or no records or the number of records in table
*/
function twitchpress_db_countrecords( $table_name, $where = '' ){
    global $wpdb;
    $records = $wpdb->get_var( 
        "
            SELECT COUNT(*) 
            FROM ". $table_name . "
            ".$where." 
        "
    );
    
    if( $records ){
        return $records;
    }else{
        return '0';
    }    
}

/**
* Returns SQL query result of all option records in WordPress options table that begin with the giving 
* 
* @author Ryan R. Bayne
* @version 1.1 
*/
function twitchpress_db_options_beginning_with( $prependvalue){    
    global $wpdb;
    $optionrecord_array = array();
    
    // first get all records
    $optionrecords = $wpdb->get_results( "SELECT option_name FROM $wpdb->options" );
    
    // loop through each option record and check their name value for csv2post_ at the beginning
    foreach( $optionrecords as $optkey => $option ){
        if(strpos( $option->option_name , $prependvalue ) === 0){
            $optionrecord_array[] = $option->option_name;
        }
    } 
    
    return $optionrecord_array;   
}

/**
* Query posts by ID 
* 
* @author Ryan R. Bayne
* @version 1.1
*/
function twitchpress_db_post_exist_byid( $id){
    global $wpdb;
    return $wpdb->get_row( "SELECT post_title FROM $wpdb->posts WHERE id = '" . $id . "'", 'ARRAY_A' );    
}

/**
 * Checks if a database table name exists or not
 * 1. One issue with this function is that WordPress treats the lack of tables existence as an error
 * 2. Another approach is using csv2post_WP_SQL_get_tables() and checking the array for the table, this is error free
 * 
 * @author Ryan R. Bayne
 * @version 1.1
 * 
 * @global array $wpdb
 * @param string $table_name
 * @return boolean, true if table found, else if table does not exist
 */
function twitchpress_db_does_table_exist( $table_name ){
    global $wpdb;                                      
    if( $wpdb->query( "SHOW TABLES LIKE '".$table_name."'") ){return true;}else{return false;}
}

/**
 * Checks if a database table exist
 * 
 * @author Ryan R. Bayne
 * @version 1.1 
 * 
 * @param string $table_name (possible database table name)
 */
function twitchpress_db_database_table_exist( $table_name ){
    global $wpdb;
    if( $wpdb->get_var( "SHOW TABLES LIKE '".$table_name."'") != $table_name) {     
        return false;
    }else{
        return true;
    }
}

/**
* Returns array of tables from the WordPress database.
* 
* @version 1.1 
* 
* @returns array $tables_array
*/
function twitchpress_db_get_tables() {
    global $wpdb;
    $result = mysql_query( "SHOW TABLES FROM `".$wpdb->dbname."`");
    if(!$result){return false;}
    $tables_array = array();
    while ( $row = mysql_fetch_row( $result) ) {
        $tables_array[] = $row[0];
    }        
    return $tables_array;
}

/**
* Returns an array holding the column names for the giving table
* 
* @author Ryan R. Bayne
* @version 1.1 
* 
* @param mixed $return_array [false] = mysql result [true] = array of the result
* @param mixed $columns_only true will not return column information
* @return array or mysql result or false on failure
* 
* @todo get_col_info() may not be the correct function to use here as it is to be used on a recent query only
* @todo this method could be reduced by using the foreach loop once and everything within it 
*/
function twitchpress_db_get_tablecolumns( $table_name, $return_array = false, $columns_only = false ){
    global $wpdb;
                
    // an array is required - what data is required in the array...    
    if( $return_array == true && $columns_only == false ){// return an array holding ALL info
        $columns_array = array();              
        foreach ( $wpdb->get_col_info( "DESC " . $table_name, 0 ) as $column_details ) {
            $columns_array[] = $column_details;
        }
        
        return $columns_array;
                        
    }elseif( $return_array == true && $columns_only == true){# return an array of column names only
        $columns_array = array();
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            $columns_array[] = $column_name;
        }
        
        return $columns_array;  
    }elseif( $return_array == false ){
        $columns_string = '';
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            $columns_string .= $column_name . ', ';
        }    
        $columns_string = rtrim( $columns_string, ", ");
        return $columns_string;        
    }   
}

/**
* Drops the giving database table and displays result in notice 
* 
* @author Ryan R. Bayne
* @version 1.1 
* 
* @param mixed $table_name
* @returns boolean
*/
function twitchpress_db_drop_table( $table_name ){
    global $wpdb;
    $r = $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
    if( $r ){                  
        return true;
    }else{
        return false;
    }    
}

/**
* Mass change one key name to another
*
* @author Ryan R. Bayne
* @version 1.1 
*  
* @param mixed $old_key
* @param mixed $new_key
*/
function twitchpress_db_update_meta_key( $old_key = NULL, $new_key = NULL ){
    global $wpdb;
    $results = $wpdb->get_results( 
        "
            UPDATE ".$wpdb->prefix."postmeta 
            SET meta_key = '".$new_key."' 
            WHERE meta_key = '".$old_key."'
        "
    , ARRAY_A );
    return $results;
}

/**
* Queries distinct values in a giving column
* 
* @author Ryan R. Bayne
* @version 1.0.1
* 
* @returns array of distinct values or 0 if no records or false if none 
*/
function twitchpress_dba_column_distinctvalues( $table_name, $column_name){
    global $wpdb;
    $distinct_values_found = $wpdb->get_results( "SELECT DISTINCT " . $column_name . " FROM ". $table_name, ARRAY_A );
            
    if( !$distinct_values_found ){
        return false;
    }else{
        return $distinct_values_found;        
    }  
    
    return false;                      
}

/**
* Returns rows where the same values appears twice or more
* 
* @author Ryan R. Bayne
* @version 1.1
*/
function twitchpress_db_get_duplicate_keys( $table_name, $column ) {
     $rows_with_duplicates = array();
     
    // get all distinct values for looping through
    $distinct_array = twitchpress_dba_column_distinctvalues( $table_name, $column );
    if( $distinct_array ){
        foreach( $distinct_array as $key => $distinct ){
                         
            // count how many rows have this $distinct value in this $column
            $count = twitchpress_db_count_rows( $table_name, ' WHERE ' . $column . ' = ' . $distinct[ $column ] );
            
            // if $count greater than 1 we have duplicates, add to $rows_with_duplicates
            if( $count > 1 ){
                $rows_with_duplicates[] = $distinct[ $column ];
            }
        }
    }
    
    return $rows_with_duplicates;
}

/**
* get posts based on comment count.
* 1. if using range and wish to include posts with a single comment then pass 0 as the minimum due to sql argument using > only
* 
* @param mixed $comment_count_low
* @param mixed $comment_count_high
* @param mixed $post_type
* @param mixed $post_status
* @param mixed $output
*/
function twitchpress_db_query_posts_by_comments( $comment_count_low = 0, $comment_count_high = 9999, $post_type = 'post', $post_status = 'publish', $output = 'OBJECT' ){
    global $wpdb;
    $query = "
        SELECT *
        FROM {$wpdb->prefix}posts
        WHERE {$wpdb->prefix}posts.post_type = '{$post_type}'
        AND {$wpdb->prefix}posts.post_status = '{$post_status}'
        ";
    
    // if low and high are not zero we add a range to the query
    if( $comment_count_low == 0 && $comment_count_high == 0)
    {
        $query .= "AND {$wpdb->prefix}posts.comment_count = 0";    
    }    
    else
    {
        $query .= "AND {$wpdb->prefix}posts.comment_count > {$comment_count_low}
        AND {$wpdb->prefix}posts.comment_count <= {$comment_count_high}";
    }
    
    $query .= "
    ORDER BY {$wpdb->prefix}posts.post_date
    DESC;
    ";

    return $wpdb->get_results( $query, $output);        
}

/**
* query multiple database tables, assumed to have a data set and shared key column which is required for JOIN to work
* 
* @param mixed $tables_array
* @param mixed $idcolumn
* @param mixed $where
*/
function twitchpress_db_query_multipletables( $tables_array = array(), $idcolumn = false, $where = false, $total = false ){
    global $wpdb;
                              
    if(!is_array( $tables_array ) || !isset( $tables_array[0] ) ){
        return false;
    }

    // set the main table (always the first)
    $main_table = $tables_array[0];
    
    // build select
    $select = '';
    foreach( $tables_array as $key => $table_name ){
        
        // add comma for the next table being added
        if( $key > 0 ){
            $select .= ', ';
        }
        
        // we join the current table to the main table based on giving ID column
        $select .= "$table_name.*";          
    }        

    // build JOIN
    $join = '';
    foreach( $tables_array as $key => $table_name ){
        
        // avoid adding main table to the JOIN
        if( $key == 0){continue;}
        
        // only join tables if we have an id column
        if( $idcolumn !== false && is_string( $idcolumn ) ){
            // we join the current table to the main table based on giving ID column
            $join .= "
            JOIN $table_name ON $main_table.$idcolumn = $table_name.$idcolumn";    
        }    
    }

    // build limit
    $limit = '';
    if(is_numeric( $total ) ){$limit = "LIMIT $total";}
    
    // build where
    $wherepart = '';
    if( $where !== false && is_string( $where ) ){$wherepart = "WHERE $where";}
    
    $final_query = "SELECT $select FROM $main_table $wherepart $join $limit";
                  
    // build where
    return $wpdb->get_results( $final_query, ARRAY_A );
}

/**
* Get the maximum value in column
* 
* @author Ryan R. Bayne
* @version 1.1
*/
function twitchpress_db_max_value( $column, $tablename ) {
    global $wpdb;        
    return $wpdb->get_var( "SELECT $column FROM $tablename ORDER BY $column DESC LIMIT 1" );        
}

/**
* gets a single row from c2psources table, returns query result
* 
* @uses $this->DB->selectrow()
* 
* @param mixed $project_id
* @param mixed $source_id
*/
function twitchpress_db_get_source( $source_id ){
    global $wpdb;
    return twitchpress_db_selectrow( $wpdb->c2psources, "sourceid = $source_id", '*' );
}
