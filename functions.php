<?php 

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
?>