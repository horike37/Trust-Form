<?php
$updated_message = '';
if ( isset( $_GET['message'] ) ) {
	
	if ( 'entry_trash' == $_GET['message'] ) {
		$ids = isset( $_REQUEST['ids'] ) ? $_REQUEST['ids'] : -1;
		$form = isset( $_REQUEST['form'] ) ? $_REQUEST['form'] : -1;
		$updated_message = sprintf( _n( 'Item moved to the Trash.', '%s items moved to the Trash.', $_REQUEST['trashed'] ), number_format_i18n( $_REQUEST['trashed'] ) );
		$updated_message .= '<a href="' . esc_url( wp_nonce_url( "?page=trust-form-entries&form=$form&action=untrash&ids=$ids", "bulk-entries" ) ) . '">' . __('Undo') . '</a><br />';
	}
	
	if ( 'entry_untrash' == $_GET['message'] )
		$updated_message = sprintf( _n( 'Item restored from the Trash.', '%s items restored from the Trash.', $_REQUEST['untrashed'] ), number_format_i18n( $_REQUEST['untrashed'] ) );

	if ( 'entry_deleted' == $_GET['message'] )
		$updated_message = sprintf( _n( 'Item permanently deleted.', '%s items permanently deleted.', $_REQUEST['deleted'] ), number_format_i18n( $_REQUEST['deleted'] ) );
		
	if ( 'entry_new' == $_GET['message'] )
		$updated_message = sprintf( _n( 'Item moved to the New.', '%s items moved to the New.', $_REQUEST['new'], TRUST_FORM_DOMAIN ), number_format_i18n( $_REQUEST['new'] ) );

	if ( 'entry_read' == $_GET['message'] )
		$updated_message = sprintf( _n( 'Item moved to the Read.', '%s items moved to the Read.', $_REQUEST['read'], TRUST_FORM_DOMAIN ), number_format_i18n( $_REQUEST['read'] ) );
}
?>
<div class="wrap">
<?php screen_icon( 'trust-form-logo' ); ?>

<h2><?php echo esc_html( __( 'Trust Form', TRUST_FORM_DOMAIN ) );
if ( isset($_REQUEST['s']) && $_REQUEST['s'] )
	printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', esc_html( $_REQUEST['s'] ) ); ?>
</h2>
<?php if ( $updated_message ) : ?>
<div id="message" class="updated fade"><p><?php echo $updated_message; ?></p></div>
<?php endif; ?>

<?php
if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] ) {
	require_once ( $this->admin_dir. '/entry.php' );
} else {
	require_once ( $this->admin_dir. '/entries-list.php' );
} 
?>
</div>
<?php require_once ( $this->admin_dir. '/paypal.php' ); ?>




