<?php
$form  = isset($_GET['form'])&&is_numeric($_GET['form']) ? $_GET['form'] : -1;
$entry = isset($_GET['entry'])&&is_numeric($_GET['entry']) ? $_GET['entry'] : -1; 
$responce = get_post_meta( $form, 'answer' );

$status = $responce[$entry]['status'];
if ( $status == 'new' ) {
	$status_msg = __( 'New', TRUST_FORM_DOMAIN );
	$status_class = '';
} elseif ( $status == 'read' ) {
	$status_msg = __( 'Read', TRUST_FORM_DOMAIN );
	$status_class = 'class="read"';
} else {
	$status_msg = '';
	$status_class = '';
}

?>
<p><a href='<?php echo admin_url( 'admin.php?page=trust-form-entries&form='.$form.'&status='.$status ); ?>'><?php echo esc_html( __( 'Back to post list', TRUST_FORM_DOMAIN ) ); ?></a></p>
<p><strong><?php echo esc_html( __( 'Status', TRUST_FORM_DOMAIN ) ); ?> : <?php echo $status_msg ?></strong></p>
<div id="entry-contaier">
<div id="entry-box" class="entry-box" style="width:55%;">
<h3 <?php echo $status_class; ?>><span><?php echo esc_html( __( 'Entry', TRUST_FORM_DOMAIN ) ); ?></span></h3>
<table id="entry-detail">
<?php
foreach ( $responce[$entry]['title'] as $key => $e ){
?>
<tr><th scope="row"><?php echo esc_html($e); ?></th><td><?php echo str_replace( "\n", '<br />', esc_html($responce[$entry]['data'][$key])); ?></td></tr>
<?php
}
do_action( 'tr_entry_action', $responce[$entry], $form, $entry );
$notes = $responce[$entry]['note'];
?>
</table>
</div>

<div id="entry-note-box" class="entry-box" style="width:35%;">
<h3 <?php echo $status_class; ?>><span><?php echo esc_html( __( 'Entry Note', TRUST_FORM_DOMAIN ) ); ?></span></h3>
<?php foreach ( $notes as $note ) : 
if ( $note['status'] == 'new' ) {
	$status_his = __( 'New', TRUST_FORM_DOMAIN );
} elseif( $note['status'] == 'read' ) {
	$status_his = __( 'Read', TRUST_FORM_DOMAIN );
} else {
	$status_his = '';
}
?>
<div class="note-detail">
<p>
<span><?php echo esc_html( $note['display_name'] ); ?>&lt;<?php echo esc_html( $note['mail'] ); ?>&gt;</span><br />
<span><?php echo esc_html( __( 'Status', TRUST_FORM_DOMAIN ) ); ?> : <?php echo esc_html( $status_his ); ?></span><br />
<span>Add time:<?php echo esc_html( $note['date'] ); ?></span>
</p>
<p class="note"><?php echo esc_html( $note['note'] ); ?></p>
</div>
<?php endforeach; ?>
<div id="add-note">
<form method="post" action="">
<input type="hidden" name="action" value="add" />
<input type="hidden" name="form" value="<?php echo esc_html( $form ); ?>" />
<input type="hidden" name="entry" value="<?php echo esc_html( $entry ); ?>" />
<textarea name="add_note" style="width:100%;" ></textarea>
<input type="submit" class="button-primary" value="<?php echo esc_html( __( 'Add Note', TRUST_FORM_DOMAIN ) ); ?>"> 
<select name="entry_status">
<option <?php selected($status, 'new'); ?> value="new"><?php echo __( 'New', TRUST_FORM_DOMAIN ); ?></option>
<option <?php selected($status, 'read'); ?> value="read"><?php echo __( 'Read', TRUST_FORM_DOMAIN ); ?></option>
</select>
<span><?php echo esc_html( __( 'Change Status', TRUST_FORM_DOMAIN ) ); ?></span>
<?php wp_nonce_field( 'entry_'.$entry ); ?>
</form>
</div>
</div>

</div>



