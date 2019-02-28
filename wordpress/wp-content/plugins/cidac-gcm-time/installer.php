<?php
    global $wpdb;
    $table_name = $wpdb->prefix . "cidac_gcm";
    $charset_collate = $wpdb->get_charset_collate();
    if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) != $table_name ) {
        $sql = "CREATE TABLE $table_name (
                ID mediumint(9) NOT NULL AUTO_INCREMENT,
                `product-model` text NOT NULL,
                `product-name` text NOT NULL,
                `product-description` int(9) NOT NULL,
                PRIMARY KEY  (ID)
        )    $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }