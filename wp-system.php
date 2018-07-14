<?php
/*
Plugin Name: WP System
Plugin URI: https://michaelott.id.au
Description: Get details and advice about your WordPress set up.
Version: 0.1
Author: Michael Ott
Author Email: hello@michaelott.id.au
Text Domain: wp-system
Domain Path: /languages/
*/

/* Look for translation file. */
function load_wp_system_textdomain() {
    load_plugin_textdomain( 'wp-system', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'load_wp_system_textdomain' );

/* Create admin page under the Tools menu. */
add_action('admin_menu', 'create_wp_system_menu');
function create_wp_system_menu() {
    add_management_page( 'WP System', 'WP System', 'manage_options', 'wp-system', 'generate_wp_system_page_content' );
}

/* Add custom CSS to admin */
function wp_system_admin_style() {
	$plugin_directory = plugins_url('css/', __FILE__ );
    wp_enqueue_style('wp-system-style-admin', $plugin_directory . 'wp-system.css');
}
add_action('admin_enqueue_scripts', 'wp_system_admin_style');

function generate_wp_system_page_content() { ?>
    
    <div class="wrap">
        <h1><?php _e('WP System', 'wp-system'); ?></h1>

        <?php
        if(is_admin()) {
            global $wp_version, $wpdb;
            $options = get_option( 'invoicerocket_settings' );

            $wp_theme_info          = wp_get_theme();
            $wp_theme_name          = $wp_theme_info->get( 'Name' );
            $wp_theme_version       = $wp_theme_info->get( 'Version' );
            $wp_memory              = WP_MEMORY_LIMIT;
            $wp_memory_in_use       = size_format(@memory_get_usage(TRUE), 2);
            $upload_max_filesize    = (int)(ini_get('upload_max_filesize'));
            $post_max_size          = (int)(ini_get('post_max_size'));
            $php_memory             = round(memory_get_usage() / 1024 / 1024, 2);
            $ram_available          = ini_get('memory_limit');
            $mysql_version          = $wpdb->db_version();
            $user_agent             = $_SERVER['HTTP_USER_AGENT'];
            $user_count             = count_users();
            $date_format            = get_option('date_format');
            $license_key            = get_option('license_server_last_key_queried');
            $mailserver_port        = get_option('mailserver_port');
            $permalink_structure    = get_option('permalink_structure');
            $stylesheet             = get_option('stylesheet');
            $template               = get_option('template');
            $admin_email            = get_option('admin_email');
            $current_wp_version     = get_bloginfo( 'version' );
            $html2pdf_apikey        = $options['html_to_pdf_api_key'];
            $google_maps_api_key    = $options['gmaps_api_key'];
            
            $count_pages            = wp_count_posts('page');
            $count_the_pages        = $count_pages->publish;
            $count_posts            = wp_count_posts('post');
            $count_the_posts        = $count_posts->publish;
            $count_invoices         = wp_count_posts('wp_invoices');
            $count_the_invoices     = $count_invoices->publish;
            $count_clients          = wp_count_posts('wp_clients');
            $count_the_clients      = $count_clients->publish;
            $count_income           = wp_count_posts('wp_income');
            $count_the_income       = $count_income->publish;
            $count_expenses         = wp_count_posts('wp_expenses');
            $count_the_expenses     = $count_expenses->publish;
            $count_revisions        = wp_count_posts('revision');
            $count_the_revisions    = $count_revisions->publish;

            $wp_requirements_URL    = 'https://wordpress.org/about/requirements/';
            $good                   = '<span class="good">&#10004</span>';

            // Active plugins
            function active_plugins() {
                $the_plugs = get_option('active_plugins');
                foreach($the_plugs as $key => $value) {
                    $string = explode('/',$value);
                    echo '<a href="https://wordpress.org/plugins/' . $string[0] . '" target="_blank" rel="noopener">' . $string[0] . "\r\n" . '</a>';
                }
            }

            function active_plugins_list() {
                $the_plugs = get_option('active_plugins'); 
                foreach($the_plugs as $key => $value) { 
                    $string = explode('/',$value); echo $string[0] . ' ';
                } echo "\n";
            }

            // Upload max filesize
            if($upload_max_filesize == 2 ) {
                $upload_max_filesize_medium_warning = 'medium_warning';
                $upload_max_filesize_notice = '(' . sprintf( __(  'If the upload_max_filesize() is too small, you may have trouble with form submissions and uploads.', 'wp-system' )) . ')';
            } else {
                $upload_max_filesize_medium_warning = '';
                $upload_max_filesize_notice = '';
            }

            // Multi-site
            if ( is_multisite() ) {
                $multi_site         = 'Yes';
            } else {
                $multi_site         = 'No';
            }

            // SSL check
            if ( is_ssl() ) {
                $is_ssl = 'Yes';
            } else {
                $is_ssl = 'No';
                $is_ssl_medium_warning = 'medium_warning';
                $is_ssl_notice = '(' . sprintf( __(  'Although not always necessary, it is advised that you enable SSL.', 'wp-system' )) . ')';
            }

            // MySQL version
            if($mysql_version < '5') {
                $mysql_version_notice = '(' . sprintf( __(  'You are running an outdated version of MySQL, WordPress <a href="%1$s" target="_blank">requires</a> at least MySQL 5', 'wp-system' ), $wp_requirements_URL ) . ')';
                $mysql_high_warning = 'high_warning';
            } else {
                $mysql_version_notice = $good;
                $mysql_high_warning = '';
            }

            // PHP version
            preg_match("#^\d+(\.\d+)*#", PHP_VERSION, $match);
            $php_version = $match[0];
            if($php_version < '5.2.4') {
                $php_version_notice  = '(' . sprintf( __(  'You are running an outdated version of PHP, WordPress <a href="%1$s" target="_blank">requires</a> at least PHP 5.2.4', 'wp-system' ), $wp_requirements_URL ) . ')';
                $php_high_warning = 'high_warning';
            } else {
                $php_version_notice = $good;
                $php_high_warning = '';
            }

            // WP memory limit
            if($wp_memory == '40M') {
                $wp_memory_notice = '(' . sprintf( __(  'It is recommended you increase the WordPress memory limit above the default 40MB if possible.', 'wp-system' )) . ')';
                $wp_memory_warning = 'high_warning';
            } else {
                
            }

            // Get latest theme version number from remote JSON file
            $url                = 'https://rocketapps.com.au/files/invoice-rocket/invoice-rocket/info.json';
            $request            = wp_remote_get( esc_url_raw( $url ) );
            if( is_wp_error( $request ) ) {
                return false;
            }
            $body               = wp_remote_retrieve_body( $request );
            $data               = json_decode( $body );

            $wp_URL         = 'https://api.wordpress.org/core/version-check/1.7/';
            $wp_response    = wp_remote_get($wp_URL);
            $wp_json        = $wp_response['body'];
            $wp_obj         = json_decode($wp_json);
            $wp_upgrade     = $wp_obj->offers[0];
            $the_wp_version = $wp_upgrade->version;

            if($current_wp_version < $the_wp_version) {
                $the_wp_version         = $the_wp_version;
                $the_wp_version_URL     = get_admin_url() . 'update-core.php';
                $the_wp_version_notice  = '(' . sprintf( __(  'Your version of WordPress is outdated. Please <a href="%1$s" target="_blank">update now</a>.', 'wp-system' ), $the_wp_version_URL ) . ')';
                $the_wp_version_warning = 'high_warning';
            } else {
                $the_wp_version         = $the_wp_version;
                $the_wp_version_URL     = '';
                $the_wp_version_notice  = $good;
                $the_wp_version_warning = '';
            }

            // DB Prefix
            $db_prefix = $wpdb->base_prefix;
            if($db_prefix == 'wp_') {
                $db_prefix_warning = 'high_warning';
                $db_prefix_notice  = '(' . sprintf('For security reasons, it is recommended you do not use the default database prefix.', 'wp-system' ) . ')';
            } else {
                $db_prefix_notice  = '';
                $db_prefix_warning    = '';
            }
        ?>

        <p><?php _e( 'Copy this information to the clipboard.', 'wp-system' ); ?></p>
        <p><a class="copy-log button"><?php _e( 'Copy System Report', 'wp-system' ); ?></a></p>

<pre class="pre-log">
<textarea class="info-log" id="info-log">
### <?php _e( 'WordPress Environment', 'wp-system' ); ?> ###
<?php _e( 'Current theme name', 'wp-system' ); ?>: <?php echo $wp_theme_name . "\n"; ?>
<?php _e( 'Current theme version', 'wp-system' ); ?>: <?php echo $wp_theme_version . "\n"; ?>
<?php _e( 'Current theme directory URL', 'wp-system' ); ?>: <?php echo get_bloginfo( 'template_url' ) . "\n"; ?>
<?php _e( 'WordPress version', 'wp-system' ); ?>: <?php echo get_bloginfo( 'version' ) . "\n"; ?>
<?php _e( 'WordPress memory limit', 'wp-system' ); ?>: <?php echo $wp_memory . "\n"; ?>
<?php _e( 'Site URL', 'wp-system' ); ?>: <?php echo get_bloginfo( 'wpurl' ) . "\n"; ?>
<?php _e( 'Language', 'wp-system' ); ?>: <?php echo get_bloginfo( 'language' ) . "\n"; ?>
<?php _e( 'Active plugins', 'wp-system' ); ?>: <?php echo active_plugins_list(); ?>
<?php _e( 'Multisite', 'wp-system' ); ?>: <?php echo $multi_site . "\n"; ?>
<?php _e( 'DB table prefix', 'wp-system' ); ?>: <?php echo $db_prefix . "\n"; ?>
<?php _e( 'Timezone', 'wp-system' ); ?>: <?php echo get_option('timezone_string') . "\n"; ?>
<?php _e( 'Mail server port', 'wp-system' ); ?>: <?php echo $mailserver_port . "\n"; ?>
<?php _e( 'Permalink structure', 'wp-system' ); ?>: <?php echo $permalink_structure . "\n"; ?>
<?php _e( 'Stylesheet', 'wp-system' ); ?>: <?php echo $stylesheet . "\n"; ?>
<?php _e( 'Template', 'wp-system' ); ?>: <?php echo $template . "\n"; ?>
<?php _e( 'Admin email', 'wp-system' ); ?>: <?php echo $admin_email . "\n"; ?>

### <?php _e( 'Browser', 'wp-system' ); ?> ###
<?php _e( 'User agent', 'wp-system' ); ?>: <?php echo $user_agent . "\n"; ?>

### <?php _e( 'Server Environment', 'wp-system' ); ?>  ###
<?php _e( 'PHP version', 'wp-system' ); ?>: <?php echo $php_version . "\n"; ?>
<?php _e( 'MySQL version', 'wp-system' ); ?>: <?php echo $mysql_version . "\n"; ?>
<?php _e( 'Server software', 'wp-system' ); ?>: <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>
<?php _e( 'PHP memory', 'wp-system' ); ?>: <?php echo round($php_memory, 1) . 'M' ?> / <?php echo $ram_available . "\n"; ?>
<?php _e( 'Memory in use', 'wp-system' ); ?>: <?php echo $wp_memory_in_use . "\n"; ?>
<?php _e( 'Upload max filesize', 'wp-system' ); ?>: <?php echo $upload_max_filesize . 'M' . "\n"; ?>
<?php _e( 'Post max size', 'wp-system' ); ?>: <?php echo $post_max_size . 'M' . "\n"; ?>
<?php _e( 'Character set', 'wp-system' ); ?>: <?php echo get_bloginfo( 'charset' ) . "\n"; ?>
<?php _e( 'SSL', 'wp-system' ); ?>: <?php echo $is_ssl . "\n"; ?>

### <?php _e( 'Post counts', 'wp-system' ); ?> ###
<?php _e( 'Pages', 'wp-system' ); ?>: <?php echo $count_the_pages . "\n"; ?>
<?php _e( 'Posts', 'wp-system' ); ?>: <?php echo $count_the_posts . "\n"; ?>
<?php _e( 'Revisions', 'wp-system' ); ?>: <?php echo $count_the_revisions . "\n"; ?>
<?php _e( 'Users', 'wp-system' ); ?>: <?php echo $user_count['total_users']; ?>
</textarea>
</pre>

<script>
jQuery('.copy-log').click(function() {
    jQuery('.info-log').select();
    jQuery('.pre-log').addClass('reveal');
    jQuery(this).html('<?php _e( 'Copied', 'wp-system' ); ?>' + ' &#10004');
    document.execCommand('copy');
});

// Select all inside textarea
var textBox = document.getElementById("info-log");
textBox.onfocus = function() {
    textBox.select();

    // Work around Chrome's little problem
    textBox.onmouseup = function() {
        // Prevent further mouseup intervention
        textBox.onmouseup = null;
        return false;
    };
};
</script>

        <table class="ir-info widefat" cellspacing="0">
            <thead>
                <tr>
                    <th colspan="2">
                        <h2><?php _e( 'WordPress Environment', 'wp-system' ); ?></h2>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="33%"><?php _e( 'Current theme name', 'wp-system' ); ?>:</td>
                    <td class="<?php echo $high_warning; ?>"><?php echo $wp_theme_name; ?></td>
                </tr>
                <tr>
                    <td width="33%"><?php _e( 'Current theme version', 'wp-system' ); ?>:</td>
                    <td class="<?php echo $high_warning; ?>"><?php echo $wp_theme_version; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Current theme directory URL', 'wp-system' ); ?>:</td>
                    <td><?php echo get_bloginfo( 'template_url' ); ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'WordPress version', 'wp-system' ); ?>:</td>
                    <td class="<?php echo $the_wp_version_warning; ?>"><?php echo $current_wp_version; ?> <?php echo $the_wp_version_notice; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'WordPress memory limit', 'wp-system' ); ?>:</td>
                    <td class="<?php echo $wp_memory_warning; ?>"><?php echo $wp_memory; ?> <?php echo $wp_memory_notice; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Site URL', 'wp-system' ); ?>:</td>
                    <td><?php echo get_bloginfo( 'wpurl' ); ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Language', 'wp-system' ); ?>:</td>
                    <td><?php echo get_bloginfo( 'language' ); ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Active plugins', 'wp-system' ); ?>:</td>
                    <td><pre><?php active_plugins(); ?></pre></td>
                </tr>
                <tr>
                    <td><?php _e( 'Multisite', 'wp-system' ); ?>:</td>
                    <td><?php echo $multi_site; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'DB table prefix', 'wp-system' ); ?>:</td>
                    <td class="<?php echo $db_prefix_warning; ?>"> <?php echo $db_prefix; ?> <?php echo $db_prefix_notice; ?>
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'Timezone', 'wp-system' ); ?>:</td>
                    <td><?php echo get_option('timezone_string'); ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Date format', 'wp-system' ); ?>:</td>
                    <td><?php echo $date_format; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Mail server port', 'wp-system' ); ?>:</td>
                    <td><?php echo $mailserver_port; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Permalink structure', 'wp-system' ); ?>:</td>
                    <td><?php echo $permalink_structure; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Stylesheet', 'wp-system' ); ?>:</td>
                    <td><?php echo $stylesheet; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Template', 'wp-system' ); ?>:</td>
                    <td><?php echo $template; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Admin email', 'wp-system' ); ?>:</td>
                    <td><?php echo $admin_email; ?></td>
                </tr>
            </tbody>
        </table>

        <table class="ir-info widefat" cellspacing="0" class="ir-info">
            <thead>
                <tr>
                    <th colspan="2">
                        <h2><?php _e( 'Browser', 'wp-system' ); ?></h2>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="33%"><?php _e( 'User agent', 'wp-system' ); ?>:</td>
                    <td><?php echo $user_agent; ?></td>
                </tr>
            </tbody>
        </table>

        <table class="ir-info widefat" cellspacing="0" class="ir-info">
            <thead>
                <tr>
                    <th colspan="2">
                        <h2><?php _e( 'Server Environment', 'wp-system' ); ?></h2>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="33%"><?php _e( 'PHP version', 'wp-system' ); ?>:</td>
                    <td class="<?php echo $php_high_warning; ?>"><?php echo $php_version; ?> <?php echo $php_version_notice; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'MySQL version', 'wp-system' ); ?>:</td>
                    <td class="<?php echo $mysql_high_warning; ?>"><?php echo $mysql_version; ?> <?php echo $mysql_version_notice; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Server software', 'wp-system' ); ?>:</td>
                    <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'PHP memory', 'wp-system' ); ?>:</td>
                    <td><?php echo round($php_memory, 1); ?>M / <?php echo $ram_available; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'PHP memory in use', 'wp-system' ); ?>:</td>
                    <td><?php echo $wp_memory_in_use; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Upload max filesize', 'wp-system' ); ?>:</td>
                    <td class="<?php echo $upload_max_filesize_medium_warning; ?>"><?php echo $upload_max_filesize; ?>M <?php echo $upload_max_filesize_notice; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Post max size', 'wp-system' ); ?>:</td>
                    <td><?php echo $post_max_size; ?>M</td>
                </tr>
                <tr>
                    <td><?php _e( 'Character set', 'wp-system' ); ?>:</td>
                    <td><?php echo get_bloginfo( 'charset' ); ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'SSL', 'wp-system' ); ?>:</td>
                    <td class="<?php echo $is_ssl_medium_warning; ?>"><?php echo $is_ssl; ?> <?php echo $is_ssl_notice; ?></td>
                </tr>
            </tbody>
        </table>
        
        <table class="ir-info widefat" cellspacing="0" class="ir-info">
            <thead>
                <tr>
                    <th colspan="2">
                        <h2><?php _e( 'Counts', 'wp-system' ); ?></h2>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="33%"><?php _e( 'Pages', 'wp-system' ); ?>:</td>
                    <td><?php echo $count_the_pages; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Posts', 'wp-system' ); ?>:</td>
                    <td><?php echo $count_the_posts; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Revisions', 'wp-system' ); ?>:</td>
                    <td><?php echo $count_the_revisions; ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Users', 'wp-system' ); ?>:</td>
                    <td><?php echo $user_count['total_users']; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
<?php }
}