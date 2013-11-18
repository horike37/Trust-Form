<?php
$form_id = -1;
if( isset($_GET['form']) && is_numeric($_GET['form']) )
	$form_id = $_GET['form'];
	
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

$list_table = new Trust_Form_Entries_List_Table($form_id);
?>
<div class="trust-form-toolbar">
<form id="entry-form" method="post" action="">
<select name="select_form">
<option value="-1"><?php echo esc_html(__('Please select Form', TRUST_FORM_DOMAIN)); ?></option>
<?php
$args = array( 'post_type' => 'trust-form', 'numberposts' => -1, 'post_status' => null, 'post_parent' => null );
$forms = get_posts( $args );
foreach ( $forms as $form ) {
?>
<option value="<?php echo esc_html( $form->ID );  ?>" <?php selected($form_id, $form->ID); ?> ><?php echo esc_html( $form->post_title );  ?></option>
<?php
}
?>
<select>
</form>
</div>
<?php
if ( $form_id != -1 ) :
$list_table->prepare_items();
$list_table->views();
 ?>
<form id="entries-filter" method="get">
<?php //$list_table->search_box( __( 'Search Entries', TRUST_FORM_DOMAIN ), 'trust-form-search-entries' ); ?>
<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"; />
<input type="hidden" name="form" value="<?php echo $form_id ?>"; />
<?php $list_table->display();
if ( $status != 'trash' ) :
 ?>
<p><input type="submit" class="button-primary" name="csv-dl" value="<?php echo esc_html(__('CSV Download', TRUST_FORM_DOMAIN)); ?>" /></p>
<input type="hidden" name="type" value="<?php echo esc_html($status); ?>" />
<?php endif; ?>
</form>
<?php endif; ?>