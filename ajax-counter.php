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
		document.addEventListener('DOMContentLoaded', function(){ 
			 window.setTimeout(function() {
				jQuery.post(
					"<?php echo admin_url('admin-ajax.php'); ?>",
					{ 'action': 'ajax_counter', 'post_type': 'POST', 'post_id': '<?php echo get_the_ID(); ?>', '_ajax_nonce': '<?php echo wp_create_nonce(); ?>' }
				);
			}, 3000);
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
		echo 'ERROR';
                exit;
        }

	// SECURITY: Make sure post_id is in fact an id.
        $post_id = trim($_POST['post_id']);
        if ( !preg_match('/^[0-9]+$/', $post_id) ) {
		echo 'ERROR';
                exit;
        }

	$meta_created = add_post_meta( $post_id, $meta_key, 1, true );
	if ( !$meta_created ) {
    		$wpdb->query("UPDATE `$postmeta` SET meta_value = meta_value + 1 WHERE post_id = '$post_id' and meta_key = '$meta_key' ");
	}
	
	echo 'OK';
        exit;
};

?>
