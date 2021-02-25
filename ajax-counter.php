<?php
/**
 * Plugin Name: AJAX Counter
 * Plugin URI: https://www.kickstart.ch
 * Description: Simplistic view counter using AJAX requests
 * Version: 1.0
 * Author: Manuel Badzong
 * Author URI: https://www.kickstart.ch
 */

// Ajax URL
add_action('wp_footer', function () {
	if ( is_single(get_the_ID()) ): ?>
   	<script>
		function ajax_counter_post(delay = 0) {
			var action = 'ajax_counter'
			var url = "<?php echo admin_url('admin-ajax.php'); ?>";
			var post_id = "<?php echo get_the_ID(); ?>";
			var nonce = "<?php echo wp_create_nonce(); ?>";

			var data = { 'action': action, 'post_type': 'POST', 'post_id': post_id, 'delay': delay, '_ajax_nonce': nonce };

			if ( delay > 0) {
				window.setTimeout(function() { jQuery.post( url, data ); }, delay * 1000);
			} else {
				jQuery.post( url, data );
			}
		}

		document.addEventListener('DOMContentLoaded', function(){ 
			ajax_counter_post();
			ajax_counter_post(10);
			ajax_counter_post(30);
		}, false);
	</script>
<?php endif; });

// AJAX Backend
add_action( 'wp_ajax_ajax_counter', 'ajax_counter');
add_action( 'wp_ajax_nopriv_ajax_counter', 'ajax_counter');
function ajax_counter() {
    	global $wpdb;
	$postmeta = $wpdb->postmeta;
	$meta_key = 'ajax_counter_views';

	if ( !check_ajax_referer() || !array_key_exists('post_id', $_POST) ) {
		echo 'ERROR: bad request';
                exit;
        }

	// SECURITY: Make sure post_id is numeric
        $post_id = trim($_POST['post_id']);
        if ( !preg_match('/^[0-9]+$/', $post_id) ) {
		echo 'ERROR: id=$post_id';
                exit;
        }

	// SECURITY: Only allow given delays
	$delay = array_key_exists('delay', $_POST)? intval($_POST['delay']): 0;
	switch ($delay) {
		case 0:
		case 10:
		case 30:
			break;
		defaul;
			echo "ERROR: delay=$delay";
                	exit;
	}

	if ( $delay ) {
		$meta_key .= '_' . $delay;
	}

	$meta_created = add_post_meta( $post_id, $meta_key, 1, true );
	if ( !$meta_created ) {
    		$wpdb->query("UPDATE `$postmeta` SET meta_value = meta_value + 1 WHERE post_id = '$post_id' and meta_key = '$meta_key' ");
	}
	
	echo "OK";
        exit;
};

function ajax_counter_views($delay = 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'postmeta';

        $post_id = get_the_ID();
        if ( !$post_id ) {
                return false;
        }

	$meta_key = 'ajax_counter_views';
	if ( $delay > 0 ) {
		$meta_key .= '_' . $delay;
	}
	
        // Use a database query to avoid caching problems
        $query = "SELECT meta_value FROM `$table_name` WHERE meta_key='$meta_key' AND post_id='$post_id'";
        $views = $wpdb->get_var($query);

        return $views ? intval($views): 0;
}

?>
