<div id="entry-contaier">
<div id="entry-box" class="entry-box" style="width:55%;">
<h3><span><?php echo esc_html( __( 'Entry', TRUST_FORM_DOMAIN ) ); ?></span></h3>
<table id="entry-detail">
<?php
$form  = isset($_GET['form'])&&is_numeric($_GET['form']) ? $_GET['form'] : -1;
$entry = isset($_GET['entry'])&&is_numeric($_GET['entry']) ? $_GET['entry'] : -1; 
$responce = get_post_meta( $form, 'responce' );
foreach ( $responce[0][$entry]['title'] as $key => $e ){
?>
<tr><th scope="row"><?php echo esc_html($e); ?></th><td><?php echo str_replace( "\n", '<br />', esc_html($responce[0][$entry]['data'][$key])); ?></td></tr>
<?php
}
$notes = $responce[0][$entry]['note'];
?>
</table>
</div>

<div id="entry-note-box" class="entry-box" style="width:35%;">
<h3><span><?php echo esc_html( __( 'Entry Note', TRUST_FORM_DOMAIN ) ); ?></span></h3>
<?php foreach ( $notes as $note ) : ?>
<div class="note-detail">
<p>
<?php echo esc_html( $note['display_name'] ); ?>&lt;<?php echo esc_html( $note['mail'] ); ?>&gt;<br />
Add time:<?php echo esc_html( $note['date'] ); ?>
</p>
<p class="note"><?php echo esc_html( $note['note'] ); ?></p>
</div>
<?php endforeach; ?>
<div id="add-note">
<form method="post" action="">
<input type="hidden" name="action" value="add" />
<input type="hidden" name="form" value="<?php echo esc_html( $form ); ?>" />
<input type="hidden" name="entry" value="<?php echo esc_html( $entry ); ?>" />
<textarea name="add_note" cols="60"></textarea>
<input type="submit" class="button-primary" value="<?php echo esc_html( __( 'Add Note', TRUST_FORM_DOMAIN ) ); ?>">
<?php wp_nonce_field( 'entry_'.$entry ); ?>
</form>
</div>
</div>

</div>



