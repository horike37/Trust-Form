<?php
$display = !isset( $_GET['action'] ) || 'edit' != $_GET['action'] ? 'style="display:none;"' : '' ;
$form_admin_input = !isset( $_GET['action'] ) || 'edit' != $_GET['action'] ? '' : get_post_meta( $this->form_id, 'form_admin_input', true );
$form_admin_confirm = !isset( $_GET['action'] ) || 'edit' != $_GET['action'] ? '' : get_post_meta( $this->form_id, 'form_admin_confirm', true );
$form_admin_finish = !isset( $_GET['action'] ) || 'edit' != $_GET['action'] ? '' : get_post_meta( $this->form_id, 'form_admin_finish', true );
$form_config = !isset( $_GET['action'] ) || 'edit' != $_GET['action'] ? '' : get_post_meta( $this->form_id, 'config' ) ;
?>

<style id="front-css" type="text/css">
</style>
<div id="tr-notice" style="display: none; opacity: 0;">
<p><?php _e( 'saved' ); ?></p>
</div>
<div id="trust-form-short-code" class="updated" <?php echo $display ?>><p><?php echo esc_html(  __( 'Please insert Copy and paste the tag on the right into page or post', TRUST_FORM_DOMAIN ) ); ?><input type="text" size="60" value="<?php echo '[trust-form id='.$this->form_id.']'; ?>" readonly="readonly" onclick="javascript:jQuery(this).select();" /></p></div>
<div id="short-code-box">
<p id="trust-form-title-msg"><?php echo esc_html(  __( 'Title', TRUST_FORM_DOMAIN ) ); ?>: <input id="trust-form-title" type="text" size="40" class="trust-form-title" name="trust-form-title" title="form-title" value="<?php echo $this->form_title != '' ? esc_html( $this->form_title ) : 'trust-form' ; ?>" /></p>
</div>
<div class="metabox-holder">
<div id="element-container" class="postbox-container" style="width:25%;">
<?php
add_meta_box( 'standard-form', __( 'Form Element', TRUST_FORM_DOMAIN ), 'trustform_standard_form_meta_box', 'trustform', 'advanced', 'core' );
//add_meta_box( 'advanced-form', __( 'Advanced Field', TRUST_FORM_DOMAIN ), 'trustform_advanced_form_meta_box', 'trustform', 'advanced', 'core' );

do_meta_boxes( 'trustform', 'advanced', $this );
wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );

function trustform_standard_form_meta_box() {

?>
<table id="standard-form-element-list" class="element-list-box">
  <!-- Textbox -->
  <tr class="form-element ui-draggable textbox-container" title="text">
  <td class="element-title"><h4><span class="in-widget-title"><?php echo esc_html(  __( 'Textbox', TRUST_FORM_DOMAIN )); ?></span></h4></td>
  <th scope="row" class="setting-element-title" style="visibility: hidden;"><div class="subject"><span class="content">title</span><span class="require"></span></div><div class="submessage"><span class="content"></span></div></th>
  <td class="setting-element-discription" style="visibility: hidden;">
    <input type="text" value="" style="display:none;" />
  </td>
  <td class="setting-element-editor" style="display:none">
    <div class="edit-element-container" style="display:none">
      <img class="edit-menu-icon text-edit edit-button" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/gear.png'; ?>" alt="setting" title="setting" width="20px">
      <img class="edit-menu-icon text-delete delete-button" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/trash.png'; ?>" alt="delete" title="delete" width="20px">
      <div class="text-edit-content edit-content display-out" >
        <div class="edit-content-title"><span><strong><?php echo esc_html(  __( 'Textbox', TRUST_FORM_DOMAIN )); ?></strong></span>
        <!-- changed by natasha
        <img class="del-icon" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/del.gif'; ?>">
        -->
        </div>
        <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'attribute', TRUST_FORM_DOMAIN ) ); ?></strong></div>
        <hr class="text-edit-conten-spencer" />
        <ul>
          <li><?php echo esc_html( __( 'size', TRUST_FORM_DOMAIN ) ); ?>&nbsp;<input type="text" size="3" validate="[required]" name="textbox-size" value="" /></li>
          <li><?php echo esc_html( __( 'maxlength', TRUST_FORM_DOMAIN ) ); ?>&nbsp;<input type="text" size="3" name="textbox-maxlength" value="" /></li>
          <li><?php echo esc_html( __( 'class', TRUST_FORM_DOMAIN ) ); ?>&nbsp;<input type="text" name="textbox-class" value="" /></li>
        </ul>
        <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'validation', TRUST_FORM_DOMAIN ) ); ?></strong></div>
        <hr class="text-edit-conten-spencer" />
        <ul style="text-align:left;">
          <li><input type="checkbox" name="text-required" value="1" /><?php echo esc_html( __( 'required', TRUST_FORM_DOMAIN ) ); ?></li>
          <li><input type="checkbox" name="textbox-char-num" value="1" /><?php echo esc_html( __( 'number of characters', TRUST_FORM_DOMAIN ) ); ?></li>
          <li class="display-out">
            <ul style="text-indent:1em;">
              <li><input type="checkbox" name="textbox-min-check" value="1" /><input size="2" type="text" name="textbox-min" value="" /><?php echo esc_html( __( 'min', TRUST_FORM_DOMAIN ) ); ?></li>
              <li><input type="checkbox" name="textbox-max-check" value="1" /><input size="2" type="text" name="textbox-max" value="" /><?php echo esc_html( __( 'max', TRUST_FORM_DOMAIN ) ); ?></li>
            </ul>
          </li>
          <li><input type="checkbox" name="textbox-characters" value="1" /><?php echo esc_html( __( 'characters', TRUST_FORM_DOMAIN ) ); ?></li>
          <li class="display-out">
            <ul style="text-indent:1em;">
              <li><input type="radio" name="textbox-character" value="alphabet" /><?php echo esc_html( __( 'only alphabet', TRUST_FORM_DOMAIN ) ); ?></li>
              <li><input type="radio" name="textbox-character" value="numeric" /><?php echo esc_html( __( 'only numeric', TRUST_FORM_DOMAIN ) ); ?></li>
              <li><input type="radio" name="textbox-character" value="alphanumeric" /><?php echo esc_html( __( 'only alphanumeric', TRUST_FORM_DOMAIN ) ); ?></li>
              <li><input type="radio" name="textbox-character" value="alphanumeric-and-code" /><?php echo esc_html( __( 'only alphanumeric and code', TRUST_FORM_DOMAIN ) ); ?></li>
            </ul>
          </li>
          <li><input type="checkbox" name="textbox-multi-characters" value="1" /><?php echo esc_html( __( 'multibyte characters', TRUST_FORM_DOMAIN ) ); ?></li>
          <li class="display-out">
            <ul style="text-indent:1em;">
              <li><input type="radio" name="textbox-multi-character" value="multibyte" /><?php echo esc_html( __( 'all multibyte', TRUST_FORM_DOMAIN ) ); ?></li>
              <li><input type="radio" name="textbox-multi-character" value="katakana" /><?php echo esc_html( __( 'only katakana', TRUST_FORM_DOMAIN ) ); ?></li>
              <li><input type="radio" name="textbox-multi-character" value="hiragana" /><?php echo esc_html( __( 'only hiragana', TRUST_FORM_DOMAIN ) ); ?></li>
            </ul>
          </li>
	    </ul>
	    <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'Akismet Configuration', TRUST_FORM_DOMAIN ) ); ?></strong></div>
		  <hr class="text-edit-conten-spencer">
	  	  <ul style="text-align:left;">
		    <li><input type="radio" name="akismet-config" value="no-config" /><?php echo esc_html( __( "Don't configuration", TRUST_FORM_DOMAIN ) ); ?></li>
            <li><input type="radio" name="akismet-config" value="author" /><?php echo esc_html( __( 'author', TRUST_FORM_DOMAIN ) ); ?></li>
		    <li><input type="radio" name="akismet-config" value="author_email" /><?php echo esc_html( __( 'author_email', TRUST_FORM_DOMAIN ) ); ?></li>
		    <li><input type="radio" name="akismet-config" value="author_url" /><?php echo esc_html( __( 'author_url', TRUST_FORM_DOMAIN ) ); ?></li>
	      </ul>
		  <hr class="text-edit-conten-spencer">
		  <ul>
		      <li><input class="del-icon" type="image" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/ok.png'; ?>" alt="OK"></li>
	      </ul>
	  </div>
	  

	</div>
  </td>
  </tr>
  <!-- Textarea -->
  <tr class="form-element ui-draggable  textarea-container" title="textarea">
  <td class="element-title"><h4><?php echo esc_html(  __( 'Textarea', TRUST_FORM_DOMAIN )); ?><span class="in-widget-title"></span></h4></td>
  <th scope="row" class="setting-element-title" style="visibility: hidden;"><div class="subject"><span class="content">title</span><span class="require"></span></div><div class="submessage"><span class="content"></span></div></th>
  <td class="setting-element-discription" style="visibility: hidden;">
    <textarea style="display:none;"></textarea>
  </td>
  <td class="setting-element-editor" style="display:none">
    <div class="edit-element-container" style="display:none">
      <img class="edit-menu-icon text-edit edit-button" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/gear.png'; ?>" alt="setting" title="setting" width="20px">
      <img class="edit-menu-icon text-delete delete-button" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/trash.png'; ?>" alt="delete" title="delete" width="20px">
      <div class="text-edit-content  display-out">
        <div class="edit-content-title"><span><strong><?php echo esc_html(  __( 'Textarea', TRUST_FORM_DOMAIN )); ?></strong></span><!--<img class="del-icon" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/del.gif'; ?>">--></div>
        <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'attribute', TRUST_FORM_DOMAIN ) ); ?></strong></div>
        <hr class="text-edit-conten-spencer" />
        <ul>
          <li><?php echo esc_html( __( 'rows', TRUST_FORM_DOMAIN ) ); ?>&nbsp;<input type="text" size="3" name="textarea-rows" value="" /></li>
          <li><?php echo esc_html( __( 'cols', TRUST_FORM_DOMAIN ) ); ?>&nbsp;<input type="text" size="3" name="textarea-cols" value="" /></li>
          <li><?php echo esc_html( __( 'class', TRUST_FORM_DOMAIN ) ); ?>&nbsp;<input type="text" name="textarea-class" value="" /></li>
        </ul>
        <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'validation', TRUST_FORM_DOMAIN ) ); ?></strong></div>
        <hr class="text-edit-conten-spencer" />
        <ul style="text-align:left;">
          <li><input type="checkbox" name="textarea-required" value="1" /><?php echo esc_html( __( 'required', TRUST_FORM_DOMAIN ) ); ?></li>
          <li><input type="checkbox" name="textarea-char-num" value="1" /><?php echo esc_html( __( 'number of characters', TRUST_FORM_DOMAIN ) ); ?></li>
          <li class="display-out">
            <ul style="text-indent:1em;">
              <li><input type="checkbox" name="textarea-min-check" value="1" /><input size="2" type="text" name="textarea-min" value="" /><?php echo esc_html( __( 'min', TRUST_FORM_DOMAIN ) ); ?></li>
              <li><input type="checkbox" name="textarea-max-check" value="1" /><input size="2" type="text" name="textarea-max" value="" /><?php echo esc_html( __( 'max', TRUST_FORM_DOMAIN ) ); ?></li>
            </ul>
          </li>
	    </ul>
		<div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'Akismet Configuration', TRUST_FORM_DOMAIN ) ); ?></strong></div>
		<hr class="text-edit-conten-spencer">
	  	<ul style="text-align:left;">
		  <li><input type="radio" name="akismet-config" value="no-config" /><?php echo esc_html( __( "Don't configuration", TRUST_FORM_DOMAIN ) ); ?></li>
          <li><input type="radio" name="akismet-config" value="author" /><?php echo esc_html( __( 'author', TRUST_FORM_DOMAIN ) ); ?></li>
		  <li><input type="radio" name="akismet-config" value="author_email" /><?php echo esc_html( __( 'author_email', TRUST_FORM_DOMAIN ) ); ?></li>
		  <li><input type="radio" name="akismet-config" value="author_url" /><?php echo esc_html( __( 'author_url', TRUST_FORM_DOMAIN ) ); ?></li>
	    </ul>
	    <hr class="text-edit-conten-spencer">
		<ul>
		    <li><input class="del-icon" type="image" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/ok.png'; ?>" alt="OK"></li>
	    </ul>
	  </div>
    </div>
  </td>
  </tr>
  <!-- Checkbox -->
  <tr class="form-element ui-draggable  checkbox-container" title="checkbox">
    <td class="element-title"><h4><?php echo esc_html(  __( 'Checkbox', TRUST_FORM_DOMAIN )); ?><span class="in-widget-title"></span></h4></td>
    <th scope="row" class="setting-element-title" style="visibility: hidden;"><div class="subject"><span class="content">title</span><span class="require"></span></div><div class="submessage"><span class="content"></span></div></th>
    <td class="setting-element-discription" style="visibility: hidden;">
      <ul>
        <li><input type="checkbox" /><?php echo esc_html(  __( 'option value', TRUST_FORM_DOMAIN ) );?></li>
      </ul>
    </td>
    <td class="setting-element-editor" style="display:none">
      <div class="edit-element-container" style="display:none">
        <img class="edit-menu-icon text-edit edit-button" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/gear.png'; ?>" alt="setting" title="setting" width="20px">
        <img class="edit-menu-icon text-delete delete-button" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/trash.png'; ?>" alt="delete" title="delete" width="20px">
        <div class="text-edit-content display-out">
          <div class="edit-content-title"><span><strong><?php echo esc_html(  __( 'Checkbox', TRUST_FORM_DOMAIN )); ?></strong></span><!--<img class="del-icon" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/del.gif'; ?>">--></div>
          <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'option value', TRUST_FORM_DOMAIN ) ); ?></strong></div>
	      <hr class="text-edit-conten-spencer" />
	      <textarea class="option-value-editor" role="checkbox" cols="33" rows="5" ></textarea>
	      <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'validation', TRUST_FORM_DOMAIN ) ); ?></strong></div>
          <hr class="text-edit-conten-spencer" />
          <ul style="text-align:left;">
            <li><input type="checkbox" name="checkbox-required" value="1" /><?php echo esc_html( __( 'required', TRUST_FORM_DOMAIN ) ); ?></li>
	      </ul>
          <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'attribute', TRUST_FORM_DOMAIN ) ); ?></strong></div>
          <hr class="text-edit-conten-spencer" />
          <ul>
            <li><?php echo esc_html( __( 'class', TRUST_FORM_DOMAIN ) ); ?>&nbsp;<input type="text" name="checkbox-class" value="" /></li>
          </ul>
		  <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'Akismet Configuration', TRUST_FORM_DOMAIN ) ); ?></strong></div>
		  <hr class="text-edit-conten-spencer">
	  	  <ul style="text-align:left;">
		    <li><input type="radio" name="akismet-config" value="no-config" /><?php echo esc_html( __( "Don't configuration", TRUST_FORM_DOMAIN ) ); ?></li>
            <li><input type="radio" name="akismet-config" value="author" /><?php echo esc_html( __( 'author', TRUST_FORM_DOMAIN ) ); ?></li>
		    <li><input type="radio" name="akismet-config" value="author_email" /><?php echo esc_html( __( 'author_email', TRUST_FORM_DOMAIN ) ); ?></li>
		    <li><input type="radio" name="akismet-config" value="author_url" /><?php echo esc_html( __( 'author_url', TRUST_FORM_DOMAIN ) ); ?></li>
	      </ul>
	      <hr class="text-edit-conten-spencer">
		  <ul>
		      <li><input class="del-icon" type="image" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/ok.png'; ?>" alt="OK"></li>
	      </ul>
	    </div>
      </div>
    </td>
  </tr>
  <!-- Radio -->
  <tr class="form-element ui-draggable  radio-container" title="radio">
    <td class="element-title"><h4><?php echo esc_html(  __( 'Radio', TRUST_FORM_DOMAIN )); ?><span class="in-widget-title"></span></h4></td>
    <th scope="row" class="setting-element-title" style="visibility: hidden;"><div class="subject"><span class="content">title</span><span class="require"></span></div><div class="submessage"><span class="content"></span></div></th>
    <td class="setting-element-discription" style="visibility: hidden;">
      <ul>
        <li><input type="radio" /><?php echo esc_html(  __( 'Select', TRUST_FORM_DOMAIN ) );?></li>
      </ul>
    </td>
    <td class="setting-element-editor" style="display:none">
      <div class="edit-element-container" style="display:none">
        <img class="edit-menu-icon text-edit edit-button" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/gear.png'; ?>" alt="setting" title="setting" width="20px">
        <img class="edit-menu-icon text-delete delete-button" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/trash.png'; ?>" alt="delete" title="delete" width="20px">
        <div class="text-edit-content display-out">
          <div class="edit-content-title"><span><strong><?php echo esc_html(  __( 'Radio', TRUST_FORM_DOMAIN )); ?></strong></span><!--<img class="del-icon" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/del.gif'; ?>">--></div>
          <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'Select', TRUST_FORM_DOMAIN ) ); ?></strong></div>
          <hr class="text-edit-conten-spencer" />
          <textarea class="option-value-editor" role="radio" cols="33" rows="5" ></textarea>
          <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'validation', TRUST_FORM_DOMAIN ) ); ?></strong></div>
          <hr class="text-edit-conten-spencer" />
          <ul style="text-align:left;">
            <li><input type="checkbox" name="radio-required" value="1" /><?php echo esc_html( __( 'required', TRUST_FORM_DOMAIN ) ); ?></li>
          </ul>
          <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'attribute', TRUST_FORM_DOMAIN ) ); ?></strong></div>
          <hr class="text-edit-conten-spencer" />
          <ul>
            <li><?php echo esc_html( __( 'class', TRUST_FORM_DOMAIN ) ); ?>&nbsp;<input type="text" name="radio-class" value="" /></li>
          </ul>
		  <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'Akismet Configuration', TRUST_FORM_DOMAIN ) ); ?></strong></div>
		  <hr class="text-edit-conten-spencer">
	  	  <ul style="text-align:left;">
		    <li><input type="radio" name="akismet-config" value="no-config" /><?php echo esc_html( __( "Don't configuration", TRUST_FORM_DOMAIN ) ); ?></li>
            <li><input type="radio" name="akismet-config" value="author" /><?php echo esc_html( __( 'author', TRUST_FORM_DOMAIN ) ); ?></li>
		    <li><input type="radio" name="akismet-config" value="author_email" /><?php echo esc_html( __( 'author_email', TRUST_FORM_DOMAIN ) ); ?></li>
		    <li><input type="radio" name="akismet-config" value="author_url" /><?php echo esc_html( __( 'author_url', TRUST_FORM_DOMAIN ) ); ?></li>
	      </ul>
		  <hr class="text-edit-conten-spencer">
		  <ul>
		      <li><input class="del-icon" type="image" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/ok.png'; ?>" alt="OK"></li>
	      </ul>
	    </div>
      </div>
      <br style="clear: both;">
    </td>
  </tr>
  <!-- Selectbox -->
  <tr class="form-element ui-draggable  selectbox-container" title="selectbox">
    <td class="element-title"><h4><?php echo esc_html(  __( 'Selectbox', TRUST_FORM_DOMAIN )); ?><span class="in-widget-title"></span></h4></td>
    <th scope="row" class="setting-element-title" style="visibility: hidden;"><div class="subject"><span class="content">title</span><span class="require"></span></div><div class="submessage"><span class="content"></span></div></th>
    <td class="setting-element-discription" style="visibility: hidden;">
      <select><option><?php echo esc_html(  __( 'selectbox value', TRUST_FORM_DOMAIN ) ); ?></option></select>
    </td>
    <td class="setting-element-editor" style="display:none">
      <div class="edit-element-container" style="display:none">
      <img class="edit-menu-icon text-edit edit-button" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/gear.png'; ?>" alt="setting" title="setting" width="20px">
      <img class="edit-menu-icon text-delete delete-button" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/trash.png'; ?>" alt="delete" title="delete" width="20px">
      <div class="text-edit-content display-out">
        <div class="edit-content-title"><span><strong><?php echo esc_html(  __( 'Selectbox', TRUST_FORM_DOMAIN )); ?></strong></span><!--<img class="del-icon" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/del.gif'; ?>">--></div>
        <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'selectbox value', TRUST_FORM_DOMAIN ) ); ?></strong></div>
	    <hr class="text-edit-conten-spencer" />
	    <p><?php echo esc_html(  __( 'default', TRUST_FORM_DOMAIN ) ); ?><br />
	    <input type="text" name="selectbox-default-value" size="24" /></p>
	    <p><?php echo esc_html(  __( 'value', TRUST_FORM_DOMAIN ) ); ?><br />
	    <textarea class="option-value-editor" role="selectbox" cols="33" rows="5" ></textarea></p>
	    <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'validation', TRUST_FORM_DOMAIN ) ); ?></strong></div>
        <hr class="text-edit-conten-spencer" />
        <ul style="text-align:left;">
          <li><input type="checkbox" name="selectbox-required" value="1" /><?php echo esc_html( __( 'required', TRUST_FORM_DOMAIN ) ); ?></li>
	    </ul>
        <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'attribute', TRUST_FORM_DOMAIN ) ); ?></strong></div>
        <hr class="text-edit-conten-spencer" />
        <ul>
          <li><?php echo esc_html( __( 'class', TRUST_FORM_DOMAIN ) ); ?>&nbsp;<input type="text" name="selectbox-class" value="" /></li>
        </ul>
		<div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'Akismet Configuration', TRUST_FORM_DOMAIN ) ); ?></strong></div>
		<hr class="text-edit-conten-spencer">
		<ul style="text-align:left;">
		  <li><input type="radio" name="akismet-config" value="no-config" /><?php echo esc_html( __( "Don't configuration", TRUST_FORM_DOMAIN ) ); ?></li>
          <li><input type="radio" name="akismet-config" value="author" /><?php echo esc_html( __( 'author', TRUST_FORM_DOMAIN ) ); ?></li>
		  <li><input type="radio" name="akismet-config" value="author_email" /><?php echo esc_html( __( 'author_email', TRUST_FORM_DOMAIN ) ); ?></li>
		  <li><input type="radio" name="akismet-config" value="author_url" /><?php echo esc_html( __( 'author_url', TRUST_FORM_DOMAIN ) ); ?></li>
	    </ul>
		<hr class="text-edit-conten-spencer">
		<ul>
		    <li><input class="del-icon" type="image" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/ok.png'; ?>" alt="OK"></li>
	    </ul>
	  </div>
    </td>
  </tr>
  <!-- e-mail -->
  <tr class="form-element ui-draggable e-mail-container" title="e-mail">
  <td class="element-title"><h4><span class="in-widget-title"><?php echo esc_html(  __( 'E-Mail', TRUST_FORM_DOMAIN )); ?></span></h4></td>
  <th scope="row" class="setting-element-title" style="visibility: hidden;"><div class="subject"><span class="content">title</span><span class="require"></span></div><div class="submessage"><span class="content"></span></div></th>
  <td class="setting-element-discription" style="visibility: hidden;">
    <input type="text" value="" style="display:none;" />
  </td>
  <td class="setting-element-editor" style="display:none">
    <div class="edit-element-container" style="display:none">
      <img class="edit-menu-icon text-edit edit-button" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/gear.png'; ?>" alt="setting" title="setting" width="20px">
      <img class="edit-menu-icon text-delete delete-button" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/trash.png'; ?>" alt="delete" title="delete" width="20px">
      <div class="text-edit-content edit-content display-out" >
        <div class="edit-content-title"><span><strong><?php echo esc_html(  __( 'E-Mail', TRUST_FORM_DOMAIN )); ?></strong></span><!--<img class="del-icon" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/del.gif'; ?>">--></div>
        <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'attribute', TRUST_FORM_DOMAIN ) ); ?></strong></div>
        <hr class="text-edit-conten-spencer" />
        <ul>
          <li><?php echo esc_html( __( 'size', TRUST_FORM_DOMAIN ) ); ?>&nbsp;<input type="text" size="3" validate="[required]" name="textbox-size" value="" /></li>
          <li><?php echo esc_html( __( 'maxlength', TRUST_FORM_DOMAIN ) ); ?>&nbsp;<input type="text" size="3" name="textbox-maxlength" value="" /></li>
          <li><?php echo esc_html( __( 'class', TRUST_FORM_DOMAIN ) ); ?>&nbsp;<input type="text" name="textbox-class" value="" /></li>
        </ul>
        <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'validation', TRUST_FORM_DOMAIN ) ); ?></strong></div>
        <hr class="text-edit-conten-spencer" />
        <ul style="text-align:left;">
          <li><input type="checkbox" name="e-mail-required" value="1" /><?php echo esc_html( __( 'required', TRUST_FORM_DOMAIN ) ); ?></li>
	    </ul>
		<div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'Akismet Configuration', TRUST_FORM_DOMAIN ) ); ?></strong></div>
		<hr class="text-edit-conten-spencer">
		<ul style="text-align:left;">
		  <li><input type="radio" name="akismet-config" value="no-config" /><?php echo esc_html( __( "Don't configuration", TRUST_FORM_DOMAIN ) ); ?></li>
          <li><input type="radio" name="akismet-config" value="author" /><?php echo esc_html( __( 'author', TRUST_FORM_DOMAIN ) ); ?></li>
		  <li><input type="radio" name="akismet-config" value="author_email" /><?php echo esc_html( __( 'author_email', TRUST_FORM_DOMAIN ) ); ?></li>
		  <li><input type="radio" name="akismet-config" value="author_url" /><?php echo esc_html( __( 'author_url', TRUST_FORM_DOMAIN ) ); ?></li>
	    </ul>
	    <div class="text-edit-content-title"><strong><?php echo esc_html(  __( 'Re-entering E-mail', TRUST_FORM_DOMAIN ) ); ?></strong></div>
		<hr class="text-edit-conten-spencer">
		<p><?php echo esc_html(  __( 'Set two elements of mail address, and input the another title below each other.', TRUST_FORM_DOMAIN ) ); ?></p>
		<ul style="text-align:left;">
		  <li>
		  	<input type="text" name="email-confirm-title" value="" />
		  </li>
		</ul>
		<hr class="text-edit-conten-spencer">
		<ul>
		    <li><input class="del-icon" type="image" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/ok.png'; ?>" alt="OK"></li>
	    </ul>
	  </div>
	</div>
  </td>
  </tr>
</table>
<?php
}

function trustform_advanced_form_meta_box() {
	echo '<div></div>';
}
?>
</div>
<div id="side-box-right" style="width:60%;" class="postbox-container">
<div id="tab">
<ul class="tablist">
<li><a href="#tab-1"><span><?php echo esc_html(  __( 'Input Screen', TRUST_FORM_DOMAIN ) ); ?></span></a></li>
<li><a href="#tab-2"><span><?php echo esc_html(  __( 'Confirm Screen', TRUST_FORM_DOMAIN ) ); ?></span></a></li>
<li><a href="#tab-3"><span><?php echo esc_html(  __( 'Finsh Screen', TRUST_FORM_DOMAIN ) ); ?></span></a></li>
</ul>
<div id="tab-1">
<!-- moved by natasha
<ul id="trust-form-toolbar" class="toolbar">
<?php if ( defined( 'TRUST_FORM_DEFAULT_STYLE' ) && TRUST_FORM_DEFAULT_STYLE === false ) : ?>
<li id="menu-css_editor"><?php echo esc_html( __( 'CSS Editor', TRUST_FORM_DOMAIN ) ); ?></li>
<?php endif; ?>
<li id="menu-require_mark"><?php echo esc_html( __( 'Require Mark', TRUST_FORM_DOMAIN ) ); ?></li>
</ul>
-->
<div class="contact-form contact-form-input">
<?php if ( $form_admin_input !='' ) : ?>
<?php echo $form_admin_input; ?>
<?php  else : ?>
<!-- HTML -->
<!-- changed by natasha
<p id="info-message-input"><?php echo esc_html( __( 'Please Edit Text', TRUST_FORM_DOMAIN ) ); ?></p>
-->
<p id="info-message-input" style="display: none;"></p>
<!-- changed by natasha
<p id="message-container-input" style="display:none;"></p>
-->
<p id="message-container-input"><textarea cols="40" placeholder="<?php echo esc_html( __( 'Please Input Text above the Form', TRUST_FORM_DOMAIN ) ); ?>"></textarea></p>
<ul id="trust-form-toolbar" class="toolbar">
<?php if ( defined( 'TRUST_FORM_DEFAULT_STYLE' ) && TRUST_FORM_DEFAULT_STYLE === false ) : ?>
<li id="menu-css_editor"><?php echo esc_html( __( 'CSS Editor', TRUST_FORM_DOMAIN ) ); ?></li>
<?php endif; ?>
<li id="menu-require_mark">
<!-- changed by natasha
<?php echo esc_html( __( 'Require Mark', TRUST_FORM_DOMAIN ) ); ?>
-->
<?php echo esc_html( __( 'Require Mark Setting', TRUST_FORM_DOMAIN ) ); ?>
</li>
</ul>
<p id="setting-description"><?php echo esc_html( __( 'Click Elements to Setting.', TRUST_FORM_DOMAIN ) ); ?></p>
<table id="setting-form" class="element-sortables">
<tbody>
<tr id="first-setting-info">
  <td class="first-setting-info" style="border-width:0px;color:#AAAAAA;font-size:20px;text-align:center;margin:20px 0;height:150px;background-color: #F5F5F5;border: 2px dotted #999999;"><?php echo esc_html(  __( 'Drag and Drop Form Element', TRUST_FORM_DOMAIN ) ); ?></td>
</tr>
</tbody>
</table>
<!-- Submit Button -->
<div>
<p id="confirm-button" class="submit-container">
  <input type="submit" name="send-to-confirm" value="<?php echo esc_html(  __( 'Confirm', TRUST_FORM_DOMAIN ) ); ?>" />
</p>
<div class="submit-element-container" style="display:none;">
  <div class="text-edit-content">
    <div class="edit-content-title"><span><strong>Submit Button</strong></span><!--<img class="del-icon" style="bottom:0;" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/del.gif'; ?>">--></div>
    <ul>
      <li><?php echo esc_html( __( 'button text', TRUST_FORM_DOMAIN ) ); ?> <input type="text" name="submitbutton-text" value="" /></li>
      <li><?php echo esc_html( __( 'button image', TRUST_FORM_DOMAIN ) ); ?> <a class="media-upload" href="JavaScript:void(0);" rel="button_media_1" ><?php echo esc_html( __( 'Select File', TRUST_FORM_DOMAIN ) ); ?></a> <input style="display:none;" type="button" class="button-secondary" name="restore-to-button" value="<?php echo esc_html( __( 'restore to button', TRUST_FORM_DOMAIN ) ); ?>" /></li>
    </ul>
	<hr class="text-edit-conten-spencer">
	<ul class="ok-button">
	    <li><input class="del-icon submit-icon" type="image" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/ok.png'; ?>" alt="OK"></li>
	</ul>
  </div>
</div>
</div>
<?php endif; ?>
</div>
</div>
<div id="tab-2">
<div class="contact-form contact-form-confirm">
<?php if ( $form_admin_confirm !='' ) : ?>
<?php echo $form_admin_confirm; ?>
<?php  else : ?>
<!-- HTML -->
<!-- changed by natasha
<p id="info-message-confirm"><?php echo esc_html( __( 'Please Edit Text', TRUST_FORM_DOMAIN ) ); ?></p>
-->
<p id="info-message-confirm" style="display: none;"></p>
<!-- changed by natasha
<p id="message-container-confirm" style="display:none;"></p>
-->
<p id="message-container-confirm"><textarea cols="40" placeholder="<?php echo esc_html( __( 'Please Input Text above the Form', TRUST_FORM_DOMAIN ) ); ?>"></textarea></p>
<table id="setting-confirm-form" >
<tbody>
</tbody>
</table>
<!-- Submit Button -->
<p id="finish-button" class="submit-container">
  <input type="submit" name="return-to-input" value="<?php echo esc_html(  __( 'return', TRUST_FORM_DOMAIN ) ); ?>" />
  <input type="submit" name="send-to-finish" value="<?php echo esc_html(  __( 'send', TRUST_FORM_DOMAIN ) ); ?>" />
</p>
<div class="submit-element-container" style="display:none;">
  <div class="text-edit-content">
    <div class="edit-content-title"><span><strong>Submit Button</strong></span><img class="del-icon" style="bottom:0;" src="<?php echo TRUST_FORM_PLUGIN_URL.'/images/del.gif'; ?>"></div>
    <ul>
      <li><?php echo esc_html( __( 'return button text', TRUST_FORM_DOMAIN ) ); ?><input type="text" name="returnbutton-text" value="" /></li>
      <li><?php echo esc_html( __( 'send button text', TRUST_FORM_DOMAIN ) ); ?><input type="text" name="sendbutton-text" value="" /></li>
      <li><?php echo esc_html( __( 'return button image', TRUST_FORM_DOMAIN ) ); ?> <a id="return-button-select-file" class="media-upload" href="JavaScript:void(0);" rel="button_media_1" ><?php echo esc_html( __( 'Select File', TRUST_FORM_DOMAIN ) ); ?></a>  <input style="display:none;" type="button" class="button-secondary" name="restore-to-return-button" value="<?php echo esc_html( __( 'restore to button', TRUST_FORM_DOMAIN ) ); ?>" /></li>
      <li><?php echo esc_html( __( 'send button image', TRUST_FORM_DOMAIN ) ); ?> <a id="send-button-select-file" class="media-upload" href="JavaScript:void(0);" rel="button_media_1" ><?php echo esc_html( __( 'Select File', TRUST_FORM_DOMAIN ) ); ?></a>  <input style="display:none;" type="button" class="button-secondary" name="restore-to-send-button" value="<?php echo esc_html( __( 'restore to button', TRUST_FORM_DOMAIN ) ); ?>" /></li>
    </ul>
  </div>
</div>
<?php endif; ?>
</div>
</div>
<div id="tab-3">
<div class="contact-form contact-form-finish">
<?php if ( $form_admin_finish !='' ) : ?>
<?php echo $form_admin_finish; ?>
<?php  else : ?>
<!-- HTML -->
<!-- changed by natasha
<p id="info-message-finish"><?php echo esc_html( __( 'Please Edit Text', TRUST_FORM_DOMAIN ) ); ?></p>
-->
<p id="info-message-finish" style="display: none;"></p>
<!-- changed by natasha
<p id="message-container-finish" style="display:none;"></p>
-->
<p id="message-container-finish"><textarea cols="40" placeholder="<?php echo esc_html( __( 'Please Input Thanks Message', TRUST_FORM_DOMAIN ) ); ?>"></textarea></p>
<?php endif; ?>
</div>
</div>
</div>
<?php

add_meta_box( 'admin-mail', __( 'Admin Mail', TRUST_FORM_DOMAIN ), 'trustform_admin_mail_meta_box', 'trustform', 'normal', 'core' );
do_meta_boxes( 'trustform', 'normal', $this );

function trustform_admin_mail_meta_box() {
$form_id = !isset( $_GET['form'] ) || !is_numeric($_GET['form']) ? '' : $_GET['form'] ;
$admin_mail = !isset( $_GET['action'] ) || 'edit' != $_GET['action'] ? '' : get_post_meta( $form_id, 'admin_mail' ) ;
?>
<form name="form" method="post" action="">
<table class="form-table">
<tr><th scope="row"><?php echo esc_html( __( 'From Name', TRUST_FORM_DOMAIN ) ); ?></th><td><input type="text" name="from_name" size="56" value="<?php echo $admin_mail != '' ? esc_html($admin_mail[0]['from_name']) : '' ; ?>"></td></tr>
<tr><th scope="row"><?php echo esc_html( __( 'From', TRUST_FORM_DOMAIN ) ); ?></th><td><input type="text" name="from" size="56" value="<?php echo $admin_mail != '' ? esc_html($admin_mail[0]['from']) : '' ; ?>"></td></tr>
<tr><th scope="row"><?php echo esc_html( __( 'to', TRUST_FORM_DOMAIN ) ); ?></th><td><input type="text" name="to" size="56" value="<?php echo $admin_mail != '' ? esc_html($admin_mail[0]['to']) : esc_html(get_option('admin_email')) ; ?>"></td></tr>
<tr><th scope="row"><?php echo esc_html( __( 'cc', TRUST_FORM_DOMAIN ) ); ?></th><td><input type="text" name="cc" size="56" value="<?php echo $admin_mail != '' ? esc_html($admin_mail[0]['cc']) : '' ; ?>"></td></tr>
<tr><th scope="row"><?php echo esc_html( __( 'bcc', TRUST_FORM_DOMAIN ) ); ?></th><td><input type="text" name="bcc" size="56" value="<?php echo $admin_mail != '' ? esc_html($admin_mail[0]['bcc']) : '' ; ?>"></td></tr>
<tr><th scope="row"><?php echo esc_html( __( 'Subject', TRUST_FORM_DOMAIN ) ); ?></th><td><input type="text" name="subject" size="56" value="<?php echo $admin_mail != '' ? esc_html($admin_mail[0]['subject']) : '' ; ?>"></td></tr>
</table>
</form>
<?php
}
add_meta_box( 'auto-reply-mail', __( 'Auto-reply Mail', TRUST_FORM_DOMAIN ), 'trustform_auto_reply_mail_meta_box', 'trustform', 'default', 'core' );
//do_meta_boxes( 'trustform', 'default', $this );
function trustform_auto_reply_mail_meta_box() {
$form_id = !isset( $_GET['form'] ) || !is_numeric($_GET['form']) ? '' : $_GET['form'] ;
$user_mail = !isset( $_GET['action'] ) || 'edit' != $_GET['action'] ? '' : get_post_meta( $form_id, 'user_mail' ) ;
?>
<p><input type="checkbox" name="user_mail_y" value="1" <?php if(isset($user_mail[0]) && isset($user_mail[0]['user_mail_y'])){checked($user_mail[0]['user_mail_y'], '1');} ?> /><?php _e("use auto reply mail", TRUST_FORM_DOMAIN); ?></p>
<form name="form2" method="post" action="">
<table id="reply-table" class="form-table">
<tr><th scope="row"><?php echo esc_html( __( 'From Name', TRUST_FORM_DOMAIN ) ); ?></th><td><input type="text" name="from_name2" size="56" value="<?php echo $user_mail != '' ? esc_html($user_mail[0]['from_name2']) : '' ; ?>"></td></tr>
<tr><th scope="row"><?php echo esc_html( __( 'From', TRUST_FORM_DOMAIN ) ); ?></th><td><input type="text" name="from2" size="56" value="<?php echo $user_mail != '' ? esc_html($user_mail[0]['from2']) : '' ; ?>"></td></tr>
<tr><th scope="row"><?php echo esc_html( __( 'Subject', TRUST_FORM_DOMAIN ) ); ?></th><td><input type="text" name="subject2" size="56" value="<?php echo $user_mail != '' ? esc_html($user_mail[0]['subject2']) : '' ; ?>"></td></tr>
<tr><th scope="row"><?php echo esc_html( __( 'Message', TRUST_FORM_DOMAIN ) ); ?><br /><span><?php _e("[FORM DATA] contain a form data", TRUST_FORM_DOMAIN); ?></span></th><td><textarea name="message2" rows="13" cols="58" ><?php echo $user_mail != '' ? esc_html($user_mail[0]['message2']) : __("Thank you for your contact!\nWe will send an email to you from the person in charge.\n\n[FORM DATA]\n\n-----\nSignature", TRUST_FORM_DOMAIN) ; ?></textarea></td></tr>
</table>
</form>
<?php
}

add_meta_box( 'other-setting', __( 'Other Setting', TRUST_FORM_DOMAIN ), 'trustform_other_setting_meta_box', 'trustform', 'default', 'core' );
do_meta_boxes( 'trustform', 'default', $this );
function trustform_other_setting_meta_box() {
$form_id = !isset( $_GET['form'] ) || !is_numeric($_GET['form']) ? '' : $_GET['form'] ;
$other_setting = !isset( $_GET['action'] ) || 'edit' != $_GET['action'] ? '' : get_post_meta( $form_id, 'other_setting' ) ;
?>
<textarea id="trust-form-other-setting" name="other-setting" rows="7" cols="64" ><?php echo $other_setting != '' && isset($other_setting[0]) ? $other_setting[0] : ''; ?></textarea>
<?php
}

?>
<p id="trust-form-short-code-under" <?php echo $display ?>><?php echo esc_html(  __( 'Please insert Copy and paste the tag on the right into page or post', TRUST_FORM_DOMAIN ) ); ?><input type="text" size="60" value="<?php echo '[trust-form id='.$this->form_id.']'; ?>" readonly="readonly" onclick="javascript:jQuery(this).select();" /></p>
<input type="hidden" name="action" value="save" />
<input id="save-change" type="button" class="button-primary" value="<?php echo esc_html( __( 'Save Changes', TRUST_FORM_DOMAIN ) ); ?>">
<?php wp_nonce_field('trust-form-make-form'); ?>
</form>
</div>
</div>
<div id="css-editor" class="ui-dialog" role="dialog">
<div class="ui-dialog-content">
<p style="width:100%;height:420px;"><label for="css-content-editor"><textarea id="css-content-editor" style="width:95%;height:90%;"></textarea></label></p>
<?php if (isset( $_GET['action'] ) && 'edit' == $_GET['action']) : ?>
<p class="submit"><input id="save-css" type="button" class="button-secondary" value="<?php echo esc_html( __( 'Save Changes', TRUST_FORM_DOMAIN ) ); ?>" /></p>
<?php endif; ?>
</div>
</div>
<div id="require-mark" class="ui-dialog" role="dialog">
<div class="ui-dialog-content">
<p><?php echo esc_html( __( 'require text', TRUST_FORM_DOMAIN ) ); ?> <input id="require-mark-text" type="text" size="24" value="<?php echo $form_config != '' && isset($form_config[0]['require']) ? $form_config[0]['require'] : ''; ?>" /></p>
<p><?php echo esc_html( __( 'require image', TRUST_FORM_DOMAIN ) ); ?> <a id="require-mark-image" rel="button_media_1" href="JavaScript:void(0);" class="media-upload">Select File</a></p>
<p id="require-mark-content"><span class="require"><?php echo $form_config != '' && isset($form_config[0]['require']) ? $form_config[0]['require'] : ''; ?></span></p>
</div>
</div>
