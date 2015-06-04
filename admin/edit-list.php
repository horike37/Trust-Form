<a class="add-new-h2" href="?page=trust-form-add"><?php echo esc_html( __( 'Add Form', TRUST_FORM_DOMAIN ) ) ?></a>
</h2>
<?php if ( $updated_message ) : ?>
<div id="message" class="updated fade"><p><?php echo $updated_message; ?></p></div>
<?php endif;
$status = isset($_GET['status']) ? $_GET['status'] : '' ;
$list_table = new Trust_Form_Edit_List_Table( $status );
$list_table->prepare_items();
$list_table->views();
 ?>
<form id="entries-filter" method="get">
<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
<?php $list_table->display(); ?>
</form>
