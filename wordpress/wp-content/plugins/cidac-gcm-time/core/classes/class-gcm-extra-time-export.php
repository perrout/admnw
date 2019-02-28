<?php

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class CIDAC_GCM_List_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $perPage = 20;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'display_name' 	    => 'Nome',
            'user_login' 	    => 'Matrícula',
            'user_cpf' 	        => 'CPF',
            'user_phone'        => 'Telefone',
            'user_email' 	    => 'E-mail',
		);
		

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('display_name' => array('display_name', false));
    }

    /**
     * Get the table data
     *
     * @return Array
     */
	private function table_data()
    {
        // WP_User_Query arguments
        $args = array (
            'order'          => 'ASC',
            'orderby'        => 'fist_name',
            'fields'         => 'all',
            'role'           => 'subscriber_gcm',
        );
        // The Result
        $users = get_users( $args );
        $data = array();
        // Array of WP_User objects.
        foreach ( $users as $user ) {
            $meta = get_user_meta($user->ID);
            $role = $user->roles;
            $email = $user->user_email;
            $cpf = ( isset($meta['user_cpf'][0]) && $meta['user_cpf'][0] != '' ) ? $meta['user_cpf'][0] : '' ;
            $phone = ( isset($meta['user_phone'][0]) && $meta['user_phone'][0] != '' ) ? $meta['user_phone'][0] : '' ;
			$data[] = array(
                        'display_name' 	    => $user->display_name,
						'user_login' 	    => $email,
						'user_cpf' 	        => $cpf,
						'user_phone'        => $phone,
						'user_email' 	    => $email
					);
		}


		return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'display_name':
            case 'user_login':
            case 'user_cpf':
            case 'user_phone':
            case 'user_email':
                return $item[ $column_name ];

            default:
                return print_r( $item, true ) ;
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'user_email';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
}
