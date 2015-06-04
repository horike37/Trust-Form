<?php
if( isset($_POST['action']) && $_POST['action'] === 'save' ) {
	check_admin_referer('trust-form-make-form');
	
	//var_dump($_POST);	
}
?>
<div class="wrap">
<?php screen_icon( 'trust-form-logo' ); ?>
<h2><?php echo esc_html( __( 'Trust Form', TRUST_FORM_DOMAIN ) ); ?></h2>
<?php require_once ( $this->admin_dir. '/make-form.php' ); ?>
</div>
<?php require_once ( $this->admin_dir. '/paypal.php' ); ?>
