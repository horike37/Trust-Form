<?php
$updated_message = '';
if ( isset( $_GET['message'] ) ) {
	
	if ( 'form_trash' == $_GET['message'] ) {
		$ids = isset( $_REQUEST['ids'] ) ? $_REQUEST['ids'] : -1;
		$updated_message = sprintf( _n( 'Item moved to the Trash.', '%d items moved to the Trash.', $_REQUEST['trashed'] ), number_format_i18n( $_REQUEST['trashed'] ) );
		$updated_message .= '<a href="' . esc_url( wp_nonce_url( "?page=trust-form-edit&action=untrash&ids=$ids", "bulk-forms" ) ) . '">' . __('Undo') . '</a><br />';
	}
	
	if ( 'form_untrash' == $_GET['message'] )
		$updated_message = sprintf( _n( 'Item restored from the Trash.', '%d items restored from the Trash.', $_REQUEST['untrashed'] ), number_format_i18n( $_REQUEST['untrashed'] ) );

	if ( 'form_deleted' == $_GET['message'] )
		$updated_message = __( "Item permanently deleted." );

	if ( 'form_duplicate' == $_GET['message'] )
		$updated_message = sprintf( _n( 'Item duplicated.', '%d items duplicated.', $_REQUEST['duplicated'] ), number_format_i18n( $_REQUEST['duplicated'] ) );
}
?>
<div class="wrap">
<?php screen_icon( 'trust-form-logo' ); ?>
<h2><?php echo esc_html( __( 'Trust Form', TRUST_FORM_DOMAIN ) ); ?></h2>
<?php
if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] ) {
	require_once ( $this->admin_dir. '/make-form.php' );
} else {
	require_once ( $this->admin_dir. '/edit-list.php' );
} 
?>
</div>
<?php require_once ( $this->admin_dir. '/paypal.php' ); ?>
