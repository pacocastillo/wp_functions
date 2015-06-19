<?php 
// Subir imagen a wordpress
function uploadRemoteImageAndAttach($image_url, $parent_id = null){
  $image = $image_url;
  $get = wp_remote_get( $image );
  $type = wp_remote_retrieve_header( $get, 'content-type' );

  if (!$type)
      return false;
  $mirror = wp_upload_bits( str_replace('%20', '-',basename( $image )), '', wp_remote_retrieve_body( $get ) );
  $attachment = array(
      'post_title'=> str_replace('%20', '-',basename( $image )),
      'post_mime_type' => $type
  );
  $attach_id = wp_insert_attachment( $attachment, $mirror['file'], $parent_id );
  require_once(ABSPATH . 'wp-admin/includes/image.php');
  $attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );
  wp_update_attachment_metadata( $attach_id, $attach_data );
  return $attach_id;
}

// Añadir campo adicional a un usuario en woocommerce y que aparezca en el pedido
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
// Our hooked in function - $fields is passed via the filter!
function custom_override_checkout_fields( $fields ) {
  $fields['billing']['nif'] = array(
      'label'     => __('NIF', 'woocommerce'),
  'placeholder'   => _x('NIF', 'placeholder', 'woocommerce'),
  'required'  => true,
  'class'     => array('form-row-wide'),
  'clear'     => true
  );

  return $fields;
}

add_action( 'woocommerce_checkout_update_order_meta', 'my_custom_checkout_field_update_order_meta' );
function my_custom_checkout_field_update_order_meta( $order_id ) {
    if ( ! empty( $_POST['nif'] ) ) {
        update_post_meta( $order_id, 'user_nif', sanitize_text_field( $_POST['nif'] ) );
    }
}

add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );
function my_custom_checkout_field_display_admin_order_meta($order){
    echo '<p><strong>'.__('NIF').':</strong> ' . get_post_meta( $order->id, 'user_nif', true ) . '</p>';
}
?>