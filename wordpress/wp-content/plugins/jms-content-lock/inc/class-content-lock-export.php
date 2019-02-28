<?php
if(is_admin())
{
    new JMS_Content_Lock_Export();
}

/**
 * JMS_Content_Lock_Export class will create the page to load the table
 */

class JMS_Content_Lock_Export {
    /**
     * Constructor will create the menu item
     */
    public function __construct()
    {
        add_action( 'admin_menu', array($this, 'jms_content_lock_export_add_menu_dashboard' ));
        add_action( 'admin_init', array($this, 'jms_content_lock_export_dashboard_func' ));
    }

    /**
     * Menu item will allow us to load the page to display the table
     */
    public function jms_content_lock_export_add_menu_dashboard()
    {
        add_menu_page( 'Exportar Leads', 'Exportar Leads', 'manage_options', 'jms-content-lock-export', array($this, 'jms_content_lock_export_dashboard') );
    }

    /**
     * Export to csv
     *
     * @return Void
     */
    public function jms_content_lock_export_dashboard_func()
    {
        if (!empty( $_POST['jms-content-lock-export'] )) {
            global $wpdb;    

            $output_filename = 'nacao_agro_leads_'.date('d-M-Y_h-ia').'.csv';

            header("Content-type: application/force-download");
            header('Content-Disposition: inline; filename="'.$output_filename.'"');
            // WP_User_Query arguments
            $args = array (
                'order'          => 'ASC',
                'orderby'        => 'user_email',
                'fields'         => 'all',
                'role'           => 'subscriber_lead',
            );
    
            // The Result
            $result = get_users( $args );
            // Array of WP_User objects.
            $title = true;
            foreach ( $result as $lead ) {
                $meta = get_user_meta($lead->ID);
                $email = $lead->user_email;                      
                $phone = ( isset($meta['user_phone'][0]) && $meta['user_phone'][0] != '' ) ? $meta['user_phone'][0] : '' ;
                $access  = ( isset($meta['user_login_count'][0]) && $meta['user_login_count'][0] != '' ) ? $meta['user_login_count'][0] : '' ;
                if ($title) { 
                    $title = false; 
                    echo '"E-mail","Telefone","Acessos"' . "\r\n";
                }
                echo '"' . $email . '","' . $phone . '","' . $access . '"' . "\r\n";
            }
            exit();
        }
    }
    /**
     * Display the list table page
     *
     * @return Void
     */
    public function jms_content_lock_export_dashboard()
    {        
        $jmsContentLockListTable = new JMS_Leads_List_Table();
        $jmsContentLockListTable->prepare_items();
        ob_start();
?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
				<h2><?php _e( "Exportar Leads" ); ?></h2>
                <?php $jmsContentLockListTable->display(); ?>
                <script type="text/javascript">
                    jQuery(document).ready( function($)
                    {
                        $('.tablenav.top .clear, .tablenav.bottom .clear').before('<form action="#" method="POST"><input type="hidden" id="jms-content-lock-export" name="jms-content-lock-export" value="1" /><input class="button button-primary" style="margin-left:-8px;" type="submit" value="Exportar como CSV" /></form>');
                    });
                </script>
            </div>
<?php
        $output_string = ob_get_contents();
        ob_end_clean();
        echo $output_string;
    }
}

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class JMS_Leads_List_Table extends WP_List_Table
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
			'user_email'   => 'E-mail',
			'user_phone'   => 'Telefone',
			'user_login_count'   => 'Acessos',
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
        return array('user_email' => array('user_email', false));
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
            'orderby'        => 'user_email',
            'fields'         => 'all',
            'role'           => 'subscriber_lead',
        );
        // The Result
        $result = get_users( $args );
        $data = array();
        // Array of WP_User objects.
        foreach ( $result as $lead ) {
            $meta = get_user_meta($lead->ID);
            $role = $lead->roles;
            $email = $lead->user_email;
            $phone = ( isset($meta['user_phone'][0]) && $meta['user_phone'][0] != '' ) ? $meta['user_phone'][0] : '' ;
            $access  = ( isset($meta['user_login_count'][0]) && $meta['user_login_count'][0] != '' ) ? $meta['user_login_count'][0] : '' ;
			$data[] = array(
						'user_email' 	    => $email,
						'user_phone'        => $phone,
						'user_login_count'  => $access,
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
            case 'user_email':
            case 'user_phone':
            case 'user_login_count':
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
?>