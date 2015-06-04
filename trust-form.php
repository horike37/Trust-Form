<?php 
/*
Plugin Name: Trust Form
Plugin URI: https://github.com/horike37/Trust-Form
Description: Trust Form is a contact form with confirmation screen and mail and data base support.
Author: horike takahiro
Version: 2.0
Author URI: https://github.com/horike37/Trust-Form

Copyright 2015 horike takahiro (email : horike37@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'TRUST_FORM_DOMAIN' ) )
	define( 'TRUST_FORM_DOMAIN', 'trust-form' );
	
if ( ! defined( 'TRUST_FORM_PLUGIN_URL' ) )
	define( 'TRUST_FORM_PLUGIN_URL', plugins_url() . '/' . dirname( plugin_basename( __FILE__ ) ));
	
new Trust_Form();

class Trust_Form {
	private $version = '2.0';
	private $edit_page;
	private $entries_page;
	private $base_dir;
	private $plugin_dir;
	private $admin_dir;
	private $relative_lang_path;
	private $absolute_lang_path;
	private $form_id;
	private $form_title = 'trust-form';
	private $api;
	
	/* ==================================================
	 * construct
	 * @param	none
	 * @return	object  $this
	 * @since	1.0
	 */
	public function __construct() {

		$this->base_dir = dirname( plugin_basename( __FILE__ ) );
		$this->plugin_url = plugins_url() . '/' .$this->base_dir;
		$this->plugin_dir = WP_PLUGIN_DIR . '/' .$this->base_dir;
		$this->admin_dir = $this->plugin_dir . '/admin';
		$this->admin_css_dir = $this->plugin_dir . '/css';
		$this->relative_lang_path = $this->base_dir . '/languages';
		$this->absolute_lang_path = WP_PLUGIN_DIR . '/' . $this->relative_lang_path;
		$this->edit_page = TRUST_FORM_DOMAIN . '-edit';
		$this->add_page = TRUST_FORM_DOMAIN . '-add';
		$this->entries_page = TRUST_FORM_DOMAIN . '-entries';
		$this->api = new Trust_Form_API();
		
		load_plugin_textdomain( TRUST_FORM_DOMAIN, $this->absolute_lang_path, $this->relative_lang_path );
		
		add_action( 'init', array( &$this, 'register_post_type' ) );
		if ( is_admin() ) {
			add_action( 'admin_init', array( &$this, 'update' ) );
			add_action( 'admin_init', array( &$this, 'edit_admin_init' ) );
			add_action( 'admin_init', array( &$this, 'entries_admin_init' ) );
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			add_action( 'admin_print_styles', array( &$this, 'admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_javascript' ) );
			add_action( 'wp_ajax_save', array( &$this, 'wp_ajax_save' ) );
			add_action( 'wp_ajax_get_css', array( &$this, 'wp_ajax_get_css' ) );
			add_action( 'wp_ajax_save_css', array( &$this, 'wp_ajax_save_css' ) );
		} else {
			add_action( 'wp_print_styles', array( &$this, 'add_front_styles') );
		} 
	}
	
	public function update() {
		if ( version_compare( $this->version, '1.4.9', '>' ) )
			$this->trust_form_install_ver15();
	}
	
	private function trust_form_install_ver15() {
		if ( !get_option('trust_form_install_ver15') ) {
			$forms = get_posts(array( 'numberposts' => -1, 'post_type' => 'trust-form' ));
			if ( is_array($forms) ) {
				foreach ( $forms as $form ) {
					$res = get_post_meta( $form->ID, 'responce', true );
					if ( is_array($res) ) {
						foreach ( $res as $r ) {
							add_post_meta( $form->ID, 'answer', $r );
						}
					}
					$res = get_post_meta( $form->ID, 'form_admin', true );
					if ( is_array($res) ) {
						update_post_meta( $form->ID, 'form_admin_input', $res['input'] );
						update_post_meta( $form->ID, 'form_admin_confirm', $res['confirm'] );
						update_post_meta( $form->ID, 'form_admin_finish', $res['finish'] );
					}
				//delete_post_meta( $form->ID, 'responce' );
				//delete_post_meta( $form->ID, 'form_admin' );
				}
			}
			update_option('trust_form_install_ver15', 1);
		}
	}

	/* ==================================================
	 * register_post_type
	 * @param	none
	 * @return	Void
	 * @since	1.0
	 */
	public function register_post_type() {
		register_post_type( 'trust-form', 
							array( 
								'labels' => array( 'name' => __( 'Trust Form', TRUST_FORM_DOMAIN ) ),
								'public' => false,
								'hierarchical' => false,
								'supports' => array( 'title', 'editor', 'custom-fields' ),
								'rewrite' => false,
								'can_export' => true,
								 ) );
	}


	/* ==================================================
	 * do action edit page admin init
	 * @param	none
	 * @return	Void
	 * @since	1.0
	 */
	public function edit_admin_init() {
		global $plugin_page, $wpdb;
		
		if ( ! isset( $plugin_page ) || ( $this->edit_page !== $plugin_page ) )
			return;

		$pagenum  = isset($_GET['page']) ? $_GET['page'] : 1;
		$doaction = isset($_GET['action']) ? $_GET['action'] : false;
		
		if ( isset($_GET['delete_all']) )
			$doaction = 'delete_all';

		if ( $doaction && 'edit' != $doaction ) {
			
			check_admin_referer( 'bulk-forms' );

			$sendback = remove_query_arg( array('trashed', 'untrashed', 'deleted', 'ids', 'message'), wp_get_referer() );
			$sendback = add_query_arg( 'paged', $pagenum, $sendback );

			if ( 'delete_all' == $doaction ) {
				$form_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type=%s AND post_status = %s", 'trust-form', 'trash' ) );
				$doaction = 'delete';
			} elseif ( isset( $_REQUEST['ids'] ) ) {
				$form_ids = explode( ',', $_REQUEST['ids'] );
			} elseif ( !empty( $_REQUEST['form'] ) ) {
				$form_ids = is_array( $_REQUEST['form'] ) ? $_REQUEST['form'] : explode( ',', $_REQUEST['form'] );
			}

			if ( !isset( $form_ids ) ) {
				wp_redirect( $sendback );
				exit();
			}

			switch ( $doaction ) {
				case 'duplicate':
					$duplicated = 0;
					foreach( (array) $form_ids as $form_id ) {
						$this->api->duplicate($form_id);
						$duplicated++;
					}
					$sendback = add_query_arg( array('duplicated' => $duplicated, 'ids' => join(',', $form_ids), 'message' => 'form_duplicate' ), $sendback );
					break;
				case 'trash':
					$trashed = 0;
					foreach( (array) $form_ids as $form_id ) {
						if ( !wp_trash_post($form_id) )
							wp_die( __('Error in moving to Trash.') );

						$trashed++;
					}
					$sendback = add_query_arg( array('trashed' => $trashed, 'ids' => join(',', $form_ids), 'message' => 'form_trash' ), $sendback );
					break;
				case 'untrash':
					$untrashed = 0;
					foreach( (array) $form_ids as $form_id ) {
						if ( !wp_untrash_post($form_id) )
							wp_die( __('Error in restoring from Trash.') );

						$untrashed++;
					}
					$sendback = add_query_arg( array('untrashed' => $untrashed, 'message' => 'form_untrash' ), $sendback);
					break;
				case 'delete':
					$deleted = 0;
					foreach( (array) $form_ids as $form_id ) {
						if ( !wp_delete_post($form_id) )
							wp_die( __('Error in deleting...') );
					
						$deleted++;
					}
					$sendback = add_query_arg( array('deleted' => $deleted, 'message' => 'form_deleted' ),  $sendback);
					break; 
			}

			wp_safe_redirect( $sendback );
			exit();
		} elseif ( ! empty($_REQUEST['_wp_http_referer']) ) {
			wp_safe_redirect( remove_query_arg( array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI']) ) );
			exit();
		}
	}

	/* ==================================================
	 * do action entries page admin init
	 * @param	none
	 * @return	Void
	 * @since	1.0
	 */
	public function entries_admin_init() {
		global $plugin_page, $wpdb;
		
		if ( ! isset( $plugin_page ) || ( $this->entries_page !== $plugin_page ) )
			return;
			
		$pagenum  = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$doaction = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
		$form     = isset($_REQUEST['form']) ? $_REQUEST['form'] : -1;

		if( isset($_REQUEST['csv-dl']) ) {
			$status = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'all';
			$name = get_post_meta( $form, 'name' );
			$responce = get_post_meta( $form, 'answer' );
			$csv_ti = array();
			$csv = array();
			$count = 0;
			foreach ( $name[0] as $key => $title ) {
				$csv_ti[0][$count] = $title;
				$data_count = 0;
				foreach ( $responce as $res ) {
					if ( $status == 'all' || $status == $res['status'] ) {
						$csv[$data_count][$count] = isset($res['data'][$key]) ? $res['data'][$key] : '' ;
						$data_count++;
					}
				}
				$count++;
			}

			$csv_ti[0][$count] = __( 'Status', TRUST_FORM_DOMAIN );
			$data_count = 0;
			foreach ( $responce as $res ) {
				if ( $status == 'all' || $status == $res['status'] ) {
					if ( $res['status'] == 'new' ) {
						$csv[$data_count][$count] = __( 'New', TRUST_FORM_DOMAIN );
					} elseif ( $res['status'] == 'read' ) {
						$csv[$data_count][$count] = __( 'Read', TRUST_FORM_DOMAIN );
					}
					$data_count++;
				}
			}
			$count++;

			$csv_ti[0][$count] = __( 'Entry Date', TRUST_FORM_DOMAIN );
			$data_count = 0;
			foreach ( $responce as $res ) {
				if ( $status == 'all' || $status == $res['status'] ) {
					$csv[$data_count][$count] = isset($res['data']['date']) ? $res['data']['date'] : '' ;
					$data_count++;
				}
			}

			$mime_type = 'text/csv;charset=UTF-8';
			if ( defined( 'TRUST_FORM_CSV_SJIS_SUPPORT' ) 
			&& TRUST_FORM_CSV_SJIS_SUPPORT === true 
			&& function_exists('mb_convert_variables') ) {
				mb_convert_variables('sjis-win', 'UTF-8', $csv_ti);
				mb_convert_variables('sjis-win', 'UTF-8', $csv);
				$mime_type = 'text/csv;charset=Shift_JIS';
			}

			$file_name = 'result_'.time().'.csv';

			$csv = array_reverse($csv);
			header('Content-Disposition: inline; filename="'.$file_name.'"');
			header('Content-Type: '.$mime_type);
			
			$csv_format = isset( $_REQUEST['suite-excel'] ) && $_REQUEST['suite-excel'] ? 'excel' : 'standard';
			echo $this->create_csv_row( $csv_ti[0], $csv_format );
			foreach ($csv as $data) {
				echo $this->create_csv_row( $data, $csv_format );
			}
			exit;
		}
		
		if ( isset($_GET['delete_all']) )
			$doaction = 'delete_all';

		if ( $doaction && 'edit' != $doaction && 'add' != $doaction ) {
			
			check_admin_referer( 'bulk-entries' );
			$sendback = remove_query_arg( array('trashed', 'untrashed', 'deleted', 'ids', 'message'), wp_get_referer() );
			$sendback = add_query_arg( 'paged', $pagenum, $sendback );

			if ( 'delete_all' == $doaction ) {
				$answers = get_post_meta( $form, 'answer' );
				$entry_ids = array();
				foreach ( $answers as $id => $answer ) {
					if ( $answer['trash'] == 'true' )
						$entry_ids[] = $id;
				}
				$doaction = 'delete';
			} elseif ( isset( $_REQUEST['ids'] ) ) {
				$entry_ids = explode( ',', $_REQUEST['ids'] );
			} elseif ( isset( $_REQUEST['entry'] ) ) {
				$entry_ids = is_array( $_REQUEST['entry'] ) ? $_REQUEST['entry'] : explode( ',', $_REQUEST['entry'] );
			}

			if ( !isset( $entry_ids ) ) {
				wp_redirect( $sendback );
				exit();
			}

			$responce = get_post_meta( $form, 'answer' );
			switch ( $doaction ) {
				case 'trash':

					$trashed = 0;
					foreach( (array) $entry_ids as $entry_id ) {
						if ( $responce[$entry_id]['trash'] == 'true' ) {
							wp_die( __('Error in moving to Trash.') );
						} else {
							$prev_value = $responce[$entry_id];
							$responce[$entry_id]['trash'] = 'true';
							update_post_meta( $form, 'answer', $responce[$entry_id], $prev_value );
						}

						$trashed++;
					}
					$sendback = add_query_arg( array('trashed' => $trashed, 'ids' => join(',', $entry_ids), 'message' => 'entry_trash' ), $sendback );
					break;
				case 'read':
					$read = 0;
					foreach( (array) $entry_ids as $entry_id ) {
						if ( $responce[$entry_id]['status'] == 'read' ) {
							wp_die( __('Error in moving to Read.') );
						} else {
							$prev_value = $responce[$entry_id];
							$responce[$entry_id]['status'] = 'read';
							update_post_meta( $form, 'answer', $responce[$entry_id], $prev_value );
						}
						$read++;
					}
					$sendback = add_query_arg( array('read' => $read, 'message' => 'entry_read' ), $sendback);
					break;
				case 'new':
					$new = 0;
					foreach( (array) $entry_ids as $entry_id ) {
						if ( $responce[$entry_id]['status'] == 'new' ) {
							wp_die( __('Error in moving to Read.') );
						} else {
							$prev_value = $responce[$entry_id];
							$responce[$entry_id]['status'] = 'new';
							update_post_meta( $form, 'answer', $responce[$entry_id], $prev_value );
						}
						$new++;
					}
					$sendback = add_query_arg( array('new' => $new, 'message' => 'entry_new' ), $sendback);
					break;
				case 'untrash':
					$untrashed = 0;
					foreach( (array) $entry_ids as $entry_id ) {
						if ( $responce[$entry_id]['trash'] == 'false' ) {
							wp_die( __('Error in restoring from Trash.') );
						} else {
							$prev_value = $responce[$entry_id];
							$responce[$entry_id]['trash'] = 'false';
							update_post_meta( $form, 'answer', $responce[$entry_id], $prev_value );
						}

						$untrashed++;
					}
					$sendback = add_query_arg( array('untrashed' => $untrashed, 'message' => 'entry_untrash' ), $sendback);
					break;
				case 'delete':
					$deleted = 0;
					foreach( (array) $entry_ids as $entry_id ) {
						if ( !isset($responce[$entry_id]) ) {
							wp_die( __('Error in deleting...') );
						} else {
							delete_post_meta( $form, 'answer', $responce[$entry_id] );
						}

						$deleted++;
					}
					$sendback = add_query_arg( array('deleted' => $deleted, 'message' => 'entry_deleted' ),  $sendback);
					break; 
			}

			wp_safe_redirect( $sendback );
			exit();
		} elseif ( $doaction && 'add' == $doaction ) {
			check_admin_referer( 'entry_'.$_POST['entry'] );
			if( !isset($_POST['add_note']) ) {
				wp_die( __('Error add note') );
			} else {
				$entry    = isset($_REQUEST['entry']) ? $_REQUEST['entry'] : -1;
				$responce = get_post_meta( $form, 'answer' );
				$current_user = wp_get_current_user();
				$prev_value = $responce[$entry];
				$responce[$entry]['note'][] = array( 'display_name' => $current_user->display_name,
														'mail'         => $current_user->user_email,
														'date'         => date_i18n('Y/m/d H:i:s'),
														'note'         => $_POST['add_note'],
														'status'       => $_POST['entry_status']
													 );
				
				if ( $responce[$entry]{'status'} != $_REQUEST['entry_status'] ) {
					$responce[$entry]{'status'} = $_REQUEST['entry_status'];
				}
				update_post_meta( $form, 'answer', $responce[$entry], $prev_value );
			}
		} elseif ( ! empty($_REQUEST['_wp_http_referer']) ) {
			wp_safe_redirect( remove_query_arg( array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI']) ) );
			exit();
		}
	}

	/* ==================================================
	 * set admin menu
	 * @param	none
	 * @return	Void
	 * @since	1.0
	 */
	public function admin_menu() {
		$cap = apply_filters( 'trust_form_admin_menu_cap', 'edit_posts' );

		add_menu_page( __( 'Trust Form', TRUST_FORM_DOMAIN ), __( 'Trust Form', TRUST_FORM_DOMAIN ), $cap, $this->edit_page, array( &$this,'add_admin_edit_page' ), TRUST_FORM_PLUGIN_URL . '/images/menu-icon.png' );
		add_submenu_page( $this->edit_page, __( 'Edit Forms', TRUST_FORM_DOMAIN ), __( 'Edit Forms', TRUST_FORM_DOMAIN ), $cap, $this->edit_page, array( &$this, 'add_admin_edit_page' ) );
		add_submenu_page( $this->edit_page, __( 'Add Form', TRUST_FORM_DOMAIN ), __( 'Add Form', TRUST_FORM_DOMAIN ), $cap, $this->add_page, array( &$this, 'add_admin_add_page' ) );

		if ( !defined( 'TRUST_FORM_DB_SUPPORT' ) || TRUST_FORM_DB_SUPPORT !== false )
			add_submenu_page( $this->edit_page, __( 'Entries', TRUST_FORM_DOMAIN ), __( 'Entries', TRUST_FORM_DOMAIN ), $cap, $this->entries_page, array( &$this, 'add_admin_entries_page' ) );
	}

	/* ==================================================
	 * set admin edit page
	 * @param	none
	 * @return	Void
	 * @since	1.0
	 */ 
	public function add_admin_edit_page() {
		require_once( $this->admin_dir.'/edit.php' );
	}
	
	/* ==================================================
	 * set admin add page
	 * @param	none
	 * @return	Void
	 * @since	1.0
	 */ 
	public function add_admin_add_page() {
		require_once( $this->admin_dir.'/add.php' );
	}
	 
	/* ==================================================
	 * set admin entries page
	 * @param	none
	 * @return	Void
	 * @since	1.0
	 */
	public function add_admin_entries_page() {		
		if ( isset( $_GET[$this->entries_page] ) )
			check_admin_referer( $this->entries_page );
		require_once( $this->admin_dir.'/entries.php' );
	}
	
	/* ==================================================
	 * set admin stylesheet
	 * @param	none
	 * @return	Void
	 * @since	1.0
	 */
	public function admin_styles() {
		global $plugin_page;
	
		if ( ! isset( $plugin_page ) || ( $this->edit_page !== $plugin_page && $this->entries_page !== $plugin_page && $this->add_page !== $plugin_page ) )
			return;
			
		wp_enqueue_style( 'trfm_admin_css', $this->plugin_url . '/css/style.css' );
		switch ( $plugin_page ) {
			case $this->add_page:
				wp_enqueue_style( 'jquery-ui-1.8.16.custom', $this->plugin_url . '/css/jquery-ui-1.8.16.custom.css' );
				wp_enqueue_style( 'trust-form-front', $this->plugin_url . '/css/front.css' );
				wp_enqueue_style( 'thickbox' );
				break;
			case $this->edit_page:
				if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] ) {
					wp_enqueue_style( 'jquery-ui-1.8.16.custom', $this->plugin_url . '/css/jquery-ui-1.8.16.custom.css' );
					wp_enqueue_style( 'trust-form-front', $this->plugin_url . '/css/front.css' );
					wp_enqueue_style( 'thickbox' );
				}
				break;
			case $this->entries_page:
				break;
		}
	}
	
	/* ==================================================
	 * set admin JavaScript
	 * @param	none
	 * @return	Void
	 * @since	1.0
	 */
	public function admin_javascript() {
		global $plugin_page;
	
		if ( ! isset( $plugin_page ) || ( $this->edit_page !== $plugin_page && $this->entries_page !== $plugin_page && $this->add_page !== $plugin_page ) )
			return;

		switch ( $plugin_page ) {
			case $this->add_page:
				wp_enqueue_script( 'jquery-ui-tabs' );
				wp_enqueue_script( 'jquery-ui-draggable' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_script( 'postbox' );
				wp_enqueue_script( 'media-upload' );
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_script( 'jquery-textchange', $this->plugin_url . '/js/jquery.textchange.js', array( 'jquery' ) );
				wp_enqueue_script( 'jquery-outerclick', $this->plugin_url . '/js/jquery.outerclick.js', array( 'jquery' ) );
				wp_enqueue_script( 'jquery-ah-placeholder', $this->plugin_url . '/js/jquery.ah-placeholder.js', array( 'jquery' ) );
//				wp_enqueue_script( 'jquery-exflexfixed', $this->plugin_url . '/js/jquery.exflexfixed-0.3.0.js', array( 'jquery' ) );
				wp_enqueue_script( 'add-form', $this->plugin_url . '/js/add-form.js' );

				add_action( 'admin_head', array( &$this, 'post_admin_ajax' ) );
				break;
			case $this->edit_page:
				if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] ) {			
					wp_enqueue_script( 'jquery-ui-tabs' );
					wp_enqueue_script( 'jquery-ui-draggable' );
					wp_enqueue_script( 'jquery-ui-sortable' );
					wp_enqueue_script( 'jquery-ui-dialog' );
					wp_enqueue_script( 'postbox' );
					wp_enqueue_script( 'media-upload' );
					wp_enqueue_script( 'thickbox' );
					wp_enqueue_script( 'jquery-textchange', $this->plugin_url . '/js/jquery.textchange.js', array( 'jquery' ) );
					wp_enqueue_script( 'jquery-outerclick', $this->plugin_url . '/js/jquery.outerclick.js', array( 'jquery' ) );
					wp_enqueue_script( 'jquery-ah-placeholder', $this->plugin_url . '/js/jquery.ah-placeholder.js', array( 'jquery' ) );
//					wp_enqueue_script( 'jquery-exflexfixed', $this->plugin_url . '/js/jquery.exflexfixed-0.3.0.js', array( 'jquery' ) );
					wp_enqueue_script( 'add-form', $this->plugin_url . '/js/add-form.js' );
					
					$this->form_id = isset( $_GET['form'] )&&is_numeric( $_GET['form'] ) ? $_GET['form'] : -1;
					$conf = get_post_meta( $this->form_id, 'config' );
					$this->form_title = $conf != '' ? $conf[0]['title']:'';
					add_action( 'admin_head', array( &$this, 'init_edit_page' ) );
					add_action( 'admin_head', array( &$this, 'post_admin_ajax' ) );
				}
				break;
			case $this->entries_page:
				if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] ) {
					wp_enqueue_script( 'postbox' );
				} else {
					wp_enqueue_script( 'trust-form-entries', $this->plugin_url . '/js/entries.js' );
				}
				break;
		}
	}

	/* ==================================================
	 * do action edit page initialize
	 * @param	none
	 * @return	Void
	 * @since	1.0
	 */
	public function init_edit_page() {
		//$form = get_post_meta( $this->form_id, 'form_admin' );
		$config = get_post_meta( $this->form_id, 'config' ); 
	
?>
<script type="text/javascript" >
jQuery(document).ready(function() {
	//setting-formのイベントを復元
	addTrustForm.textContentEvent(jQuery);
	addTrustForm.sortable(jQuery);
	addTrustForm.setupForm(jQuery);
	addTrustForm.setupButton(jQuery);
	TR_element_count = <?php echo esc_html($config[0]['TR_element_count']); ?>

	jQuery('#tab-1').append(jQuery('<input>',{id:'form_id',type:'hidden',value:<?php echo esc_html( $this->form_id ); ?>}));
});
</script>
<?php
	}
	
	/* ==================================================
	 * do action admin JavaScript
	 * @param	none
	 * @return	Void
	 * @since	1.0
	 */
	public function post_admin_ajax() {
		$ajax_nonce = wp_create_nonce("trust-form-ajax");
?>
<script type="text/javascript" >
jQuery(document).ready(function() {

	jQuery('#save-change').bind('click', function(){
		var name = '',validation = {},
		param = {
				action:'save',
				security:'<?php echo $ajax_nonce; ?>',
				id:'',
				name:[],
				type:{},
				attention:{},
				validation:{},
				attr:{value:{},
				      size:{},
				      maxlength:{},
				      cols:{},
				      rows:{},
				      class:{},
				      akismet:{},
				      e_mail_confirm:{}},
				admin_mail:{},
				user_mail:{},
				form_admin:{},
				form_front:{element:{}},
				config:{},
				other_setting:{}
				};
		if (!jQuery('#setting-form tbody tr').not('#first-setting-info').length) {
			return false;
		}
		jQuery('#setting-form tbody tr').not('#first-setting-info').each(function(i){
			//HTMLを整形
			jQuery(this).find('.text-edit-content').addClass('display-out');
			jQuery(this).removeClass('element-hover-edit');
			jQuery(this).find('.setting-element-editor').css('display', 'none');
			if (jQuery(this).find('div.subject > span.content, div.submessage > span.content').children("input").length) {
				jQuery(this).find('div.subject > span.content, div.submessage > span.content').children("input").blur();
			}
			name = jQuery(this).children('.setting-element-discription').find('input,select,textarea').prop('name');
			name = name.replace('[]', '');
			validation = {};
			//textboxのバリデーション
			if (jQuery(this).hasClass('textbox-container')) {
				validation['required'] = jQuery(this).find('input[name=text-required]').is(':checked') ? 'true' : '';

				if (jQuery(this).find('input[name=textbox-char-num]').is(':checked')) {
					validation['min'] = jQuery(this).find('input[name=textbox-min-check]').is(':checked') ? jQuery(this).find('input[name=textbox-min]').val() : '';
					validation['max'] = jQuery(this).find('input[name=textbox-max-check]').is(':checked') ? jQuery(this).find('input[name=textbox-max]').val() : '';
				}
				validation['charactor'] = jQuery(this).find('input[name=textbox-characters]').is(':checked') ? jQuery(this).find('input[name=textbox-character-'+name+']:checked').val() : "";
				validation['multi-charactor'] = jQuery(this).find('input[name=textbox-multi-characters]').is(':checked') ? jQuery(this).find('input[name=textbox-multi-character-'+name+']:checked').val() : '';
			}
			
			//textareaのバリデーション
			if (jQuery(this).hasClass('textarea-container')) {
				validation['required'] = jQuery(this).find('input[name=textarea-required]').is(':checked') ? 'true' : '';
				
				if (jQuery(this).find('input[name=textarea-char-num]').is(':checked')) {
					validation['min'] = jQuery(this).find('input[name=textarea-min-check]').is(':checked') ? jQuery(this).find('input[name=textarea-min]').val() : '';
					validation['max'] = jQuery(this).find('input[name=textarea-max-check]').is(':checked') ? jQuery(this).find('input[name=textarea-max]').val() : '';
				}
			}
			
			//checkboxのバリデーション
			if (jQuery(this).hasClass('checkbox-container')) {
				validation['required'] = jQuery(this).find('input[name=checkbox-required]').is(':checked') ? 'true' : '';
			}
			
			//radioのバリデーション
			if (jQuery(this).hasClass('radio-container')) {
				validation['required'] = jQuery(this).find('input[name=radio-required]').is(':checked') ? 'true' : '';
			}
			
			//selectboxのバリデーション
			if (jQuery(this).hasClass('selectbox-container')) {
				validation['required'] = jQuery(this).find('input[name=selectbox-required]').is(':checked') ? 'true' : '';
			}
			//emailのバリデーション
			if (jQuery(this).hasClass('e-mail-container')) {
				validation['required'] = jQuery(this).find('input[name=e-mail-required]').is(':checked') ? 'true' : '';
				validation['e_mail_confirm'] = jQuery(this).find('.edit-element-container input[name=email-confirm-title]').val();
			}

			param['name'][i] = {};
			param['name'][i][name] = jQuery(this).children('th.setting-element-title').children('div.subject').children('span.content').html();
			param['attention'][name] = jQuery(this).children('th.setting-element-title').children('div.submessage').children('span.content').html();
			param['type'][name] = jQuery(this).attr('title');
			param['validation'][name] = validation;

			//各属性値の取得
			if ( jQuery(this).attr('title') == 'selectbox' ) {
				param['attr']['value'][name] = [];
				jQuery(this).find('select > option').each(function(i){
					param['attr']['value'][name][i] = jQuery(this).text();
				});
				param['attr']['class'][name] = jQuery(this).find('select').attr('class') ? jQuery(this).find('select').attr('class') : '' ;

			} else if ( jQuery(this).attr('title') == 'radio' || jQuery(this).attr('title') == 'checkbox' ) {
				param['attr']['value'][name] = [];
				jQuery(this).find('.setting-element-discription > ul > li').each(function(i){
					param['attr']['value'][name][i] = jQuery(this).children('input').val();
				});
				param['attr']['class'][name] = jQuery(this).find('.setting-element-discription > ul > li:first > input').attr('class') ? jQuery(this).find('.setting-element-discription > ul > li:first > input').attr('class') : '' ;

			} else if ( jQuery(this).attr('title') == 'text' ) {
				param['attr']['size'][name] = jQuery(this).find('.setting-element-discription > input').attr('size') ? jQuery(this).find('.setting-element-discription > input').attr('size') : '';
				param['attr']['maxlength'][name] = jQuery(this).find('.setting-element-discription > input').attr('maxlength') ? jQuery(this).find('.setting-element-discription > input').attr('maxlength') : '';
				param['attr']['class'][name] = jQuery(this).find('.setting-element-discription > input').attr('class') ? jQuery(this).find('.setting-element-discription > input').attr('class') : '' ;

			} else if ( jQuery(this).attr('title') == 'textarea' ) {
				param['attr']['cols'][name] = jQuery(this).find('.setting-element-discription > textarea').attr('cols') ? jQuery(this).find('.setting-element-discription > textarea').attr('cols') : '';
				param['attr']['rows'][name] = jQuery(this).find('.setting-element-discription > textarea').attr('rows') ? jQuery(this).find('.setting-element-discription > textarea').attr('rows') : '';
				param['attr']['class'][name] = jQuery(this).find('.setting-element-discription > textarea').attr('class') ? jQuery(this).find('.setting-element-discription > textarea').attr('class') : '' ;

			} else if ( jQuery(this).attr('title') == 'e-mail' ) {
				param['attr']['size'][name] = jQuery(this).find('.setting-element-discription > input').attr('size') ? jQuery(this).find('.setting-element-discription > input').attr('size') : '';
				param['attr']['maxlength'][name] = jQuery(this).find('.setting-element-discription > input').attr('maxlength') ? jQuery(this).find-('.setting-element-discription > input').attr('maxlength') : '';
				param['attr']['class'][name] = jQuery(this).find('.setting-element-discription > input').attr('class') ? jQuery(this).find('.setting-element-discription > input').attr('class') : '' ;
			}
			param['attr']['akismet'][name] = jQuery(this).find('.edit-element-container input[name=akismet-config-'+name+']:checked').val();
			param['form_front']['element'][name] = jQuery(this).children('td.setting-element-discription').html();
		});
		
		//admin mailの情報
		param['admin_mail']['from_name'] = jQuery('input[name=from_name]').val();
		param['admin_mail']['from'] = jQuery('input[name=from]').val();
		param['admin_mail']['to'] = jQuery('input[name=to]').val();
		param['admin_mail']['cc'] = jQuery('input[name=cc]').val();
		param['admin_mail']['bcc'] = jQuery('input[name=bcc]').val();
		param['admin_mail']['subject'] = jQuery('input[name=subject]').val();
		//param['admin_mail']['custom-header'] = jQuery('input[name=custom-header]').val();
		//自動返信メールの情報
		param['user_mail']['user_mail_y'] = jQuery('input[name=user_mail_y]').is(':checked') ? jQuery('input[name=user_mail_y]').val() : '' ;
		param['user_mail']['from_name2'] = jQuery('input[name=from_name2]').val();
		param['user_mail']['from2'] = jQuery('input[name=from2]').val();
		param['user_mail']['subject2'] = jQuery('input[name=subject2]').val();
		param['user_mail']['message2'] = jQuery('textarea[name=message2]').val();

		//管理画面用HTML
		param['form_admin']['input']   = jQuery('#tab-1 .contact-form').html();
		param['form_admin']['confirm'] = jQuery('#tab-2 .contact-form').html();
		param['form_admin']['finish']  = jQuery('#tab-3 .contact-form').html();
		
		//HTMLを整形
		jQuery('.submit-element-container').css('display', 'none');
		jQuery('#confirm-button').removeClass('element-hover-edit');
		jQuery('#finish-button').removeClass('element-hover-edit');
		//フロント用にHTMLを加工
		param['form_front']['input_top'] = jQuery('#message-container-input').children('textarea').length ? jQuery('#message-container-input').children('textarea').val() : jQuery('#message-container-input').html();
		param['form_front']['input_bottom'] = jQuery('#confirm-button').html();
		param['form_front']['confirm_top'] = jQuery('#message-container-confirm').children('textarea').length ? jQuery('#message-container-confirm').children('textarea').val() : jQuery('#message-container-confirm').html();
		param['form_front']['confirm_bottom'] = jQuery('#finish-button').html();
		param['form_front']['finish'] = jQuery('#message-container-finish').children('textarea').length ? jQuery('#message-container-finish').children('textarea').val() : jQuery('#message-container-finish').html();
		
		param['id'] = jQuery('#form_id').length ? jQuery('#form_id').val() : '';
		
		param['config']['TR_element_count'] = TR_element_count;
		param['config']['title'] = jQuery('#trust-form-title').val();
		if (jQuery('#require-mark-text').val() != '') {
			param['config']['require'] = jQuery('#require-mark-content > span').html();
		}
		param['other_setting'] = jQuery('#trust-form-other-setting').val();

		//コールバック
		jQuery.post(ajaxurl, param, function(id) {
			if( jQuery('#form_id').length ) {
				jQuery('#form_id').val(id);
			} else {
				jQuery('#tab-1').append(jQuery('<input>',{id:'form_id',type:'hidden',value:id}));
			}
			var param3 = {
				action:'save_css',
				security:'<?php echo $ajax_nonce; ?>',
				id: jQuery('#form_id').length ? jQuery('#form_id').val() : '',
				content: jQuery('#css-content-editor').val()
				}
				jQuery.post(ajaxurl, param3, function() {});

			//changed by natasha
			var btn = jQuery('#save-change');
				//btn.css('display', 'none');
				btn.after(jQuery('<img>', {id:'loading-icon',src:'../wp-content/plugins/trust-form/images/ajax-loader.gif',style:'margin-left:30px;'}));
				setTimeout( function(id) {
   					jQuery('#loading-icon').remove();
   					btn.css('display', 'block');
   					btn.after('<p id="save-result">' + '<?php echo __( 'Change Saved', TRUST_FORM_DOMAIN ); ?>' + '</p>');//add
   					jQuery('#trust-form-short-code').css('display', 'block');
   					jQuery('#trust-form-short-code-under').css('display', 'block');
					jQuery('#trust-form-short-code > p > input').val('[trust-form id='+jQuery('#form_id').val()+']');
					jQuery('#trust-form-short-code-under > input').val('[trust-form id='+jQuery('#form_id').val()+']');
   				}, 1500 );
		});
	});
	
	jQuery('#save-css').bind('click', function(){
		var param = {
				action:'save_css',
				security:'<?php echo $ajax_nonce; ?>',
				id: jQuery('#form_id').length ? jQuery('#form_id').val() : '',
				content: jQuery('#css-content-editor').val()
				}
		jQuery.post(ajaxurl, param, function() {
			var btn = jQuery('#save-css');
				btn.css('display', 'none');
				btn.after(jQuery('<img>', {id:'loading-icon',src:'../wp-content/plugins/trust-form/images/ajax-loader.gif',style:'margin-left:30px;'}));
				setTimeout( function(id) {
   					jQuery('#loading-icon').remove();
   					btn.css('display', 'block');
   				}, 1200 );
		});
	});

	var param2 = {
				action:'get_css',
				security:'<?php echo $ajax_nonce; ?>',
				id:'<?php echo isset($_GET['form']) ? esc_html($_GET['form']) : ''; ?>'
				}
	jQuery.get(ajaxurl, param2, function(content) {
		jQuery('#css-content-editor').html(content);
		jQuery('#front-css').html(content);
	});
});
</script>
<?php
	}

	/* ==================================================
	 * do action ajax server side
	 * @param	none
	 * @return	Void
	 * @since	1.0
	 */
	public function wp_ajax_save_css() {
		check_ajax_referer( 'trust-form-ajax', 'security' );
		$content = isset($_POST['content'])&&$_POST['content'] != '' ? stripslashes($_POST['content']) : '';
		$id = isset($_POST['id'])&&$_POST['id'] != 0 ? $_POST['id'] : '';
		
		if ( $id == '' ) {
			$fp = fopen($this->plugin_dir . '/css/front_tmp.css', 'w');
			if ($fp && flock($fp, LOCK_EX) && fwrite($fp, $content)){
				echo 'success';
				fclose($fp);
				die();
			}
		} else {
			$fp = fopen($this->plugin_dir . '/css/front_'.$id.'.css', 'w');
			if ($fp && flock($fp, LOCK_EX) && fwrite($fp, $content)){
				echo 'success';
				fclose($fp);
				die();
			}

		}
		echo 'error';
		die();
	}

	/* ==================================================
	 * do action ajax server side
	 * @param	none
	 * @return	Void
	 * @since	1.0
	 */
	public function wp_ajax_save() {
		check_ajax_referer( 'trust-form-ajax', 'security' );

		$id = isset($_POST['id'])&&$_POST['id'] != 0 ? $_POST['id'] : '';
		$post = array(
  					'ID' => (int)$id,
					'post_type' => 'trust-form',
					'post_status' => 'publish',
					'post_title' => $_POST['config']['title']
					 );

		$post_id = wp_insert_post( $post );
		if ( $post_id ) {
			$name = array();
			foreach($_POST['name'] as $idx => $key) {
				foreach($key as $idx_1 => $key_1) {
					$name[$idx_1] = $key_1;
				}
			}
			if (array_key_exists('name', $_POST)) {update_post_meta( $post_id, 'name', $name );}
			if (array_key_exists('attention', $_POST)) {update_post_meta( $post_id, 'attention', $_POST['attention'] );}
			if (array_key_exists('type', $_POST)) {update_post_meta( $post_id, 'type', $_POST['type'] );}
			if (array_key_exists('validation', $_POST)) {update_post_meta( $post_id, 'validation', $_POST['validation'] );}
			if (array_key_exists('attr', $_POST)) {update_post_meta( $post_id, 'attr', $_POST['attr'] );}
			if (array_key_exists('admin_mail', $_POST)) {update_post_meta( $post_id, 'admin_mail', $_POST['admin_mail'] );}
			if (array_key_exists('user_mail', $_POST)) {update_post_meta( $post_id, 'user_mail', $_POST['user_mail'] );}
			//if (array_key_exists('form_admin', $_POST)) {update_post_meta( $post_id, 'form_admin', $_POST['form_admin'] );}
			if (array_key_exists('form_admin', $_POST)) {update_post_meta( $post_id, 'form_admin_input', $_POST['form_admin']['input'] );}
			if (array_key_exists('form_admin', $_POST)) {update_post_meta( $post_id, 'form_admin_confirm', $_POST['form_admin']['confirm'] );}
			if (array_key_exists('form_admin', $_POST)) {update_post_meta( $post_id, 'form_admin_finish', $_POST['form_admin']['finish'] );}
			if (array_key_exists('form_front', $_POST)) {update_post_meta( $post_id, 'form_front', $_POST['form_front'] );}
			if (array_key_exists('config', $_POST)) {update_post_meta( $post_id, 'config', $_POST['config'] );}
			if (array_key_exists('other_setting', $_POST)) {update_post_meta( $post_id, 'other_setting', $_POST['other_setting'] );}
		}
		echo esc_html( $post_id );
		die();
	}

	/* ==================================================
	 * do action ajax server side
	 * @param	none
	 * @return	Void
	 * @since	1.0
	 */
	public function wp_ajax_get_css() {
		check_ajax_referer( 'trust-form-ajax', 'security' );
		$content = '';
		$id = isset($_GET['id'])&&$_GET['id'] != 0 ? $_GET['id'] : '';
		
		if ( file_exists( $this->plugin_dir . '/css/front_'.$id.'.css' ) ) {
			readfile( $this->plugin_dir . '/css/front_'.$id.'.css' );
		} else {
			readfile( $this->plugin_dir . '/css/front.css' );
		}
		die();
	}

	/* ==================================================
	 * add front css
	 * @param	none
	 * @return	Void
	 * @since	1.0
	 */
	public function add_front_styles() {

		if (!$code = $this->_has_shortcode('trust-form'))
			return;
		
		$atts = $this->_get_shortcode_atts('trust-form');
		if ( defined( 'TRUST_FORM_DEFAULT_STYLE' ) && TRUST_FORM_DEFAULT_STYLE === false ) {
			wp_enqueue_style('trust-form-front', "/wp-content/plugins/trust-form/css/front_{$atts['id']}.css");
		} elseif ( defined( 'TRUST_FORM_DEFAULT_RESPONSIVE_STYLE' ) && TRUST_FORM_DEFAULT_RESPONSIVE_STYLE === false ) {
			wp_enqueue_style('trust-form-front', plugins_url( "/css/default.css", __FILE__ ), array(), '1.0', 'all' );
		} else {
			wp_enqueue_style('trust-form-front', plugins_url( "/css/default-responsive.css", __FILE__ ), array(), '1.0', 'all' );
		}
	}

	/* ==================================================
	 * get shortcode atts
	 * @param	none
	 * @return	Array
	 * @since	1.0
	 */
	private function _get_shortcode_atts($shortcode) {
		$text = preg_replace("/\[$shortcode/", '', $this->_has_shortcode($shortcode));
		$text = preg_replace("/\]/", '', $text);
		return shortcode_parse_atts(trim($text));
	}

	/* ==================================================
	 * true or false ,is shortcode in a post
	 * @param	none
	 * @return	true:shortcode,false:false
	 * @since	1.0
	 */
	private function _has_shortcode($shortcode) {
		global $wp_query;

		$posts   = $wp_query->posts;
		
		if ( !is_array($posts) )
			return false;
		
		$pattern = '/\[' . preg_quote($shortcode) . '[^\]]*\]/im';
		$found   = false;
		$hasTeaser = !( is_single() || is_page() );

		foreach($posts as $post) {
			if (isset($post->post_content)) {
				$post_content = $post->post_content;
				if ( $hasTeaser && preg_match('/<!--more(.*?)?-->/', $post_content, $matches) ) {
					$content = explode($matches[0], $post_content, 2);
					$post_content = $content[0];
				}

				if ( !empty($post_content) && preg_match($pattern, $post_content, $matches) ) {
					$found = $matches[0];
				}
			}
			if ( $found )
				break;
		}
		unset($posts);
		return $found;
	}

	/* ==================================================
	 * generating csv row data from array data
	 * @param	$row_datas Array, $mode String
	 * @return	String
	 * @since	1.8.8
	 */
	private function create_csv_row( $row_datas, $mode = 'standard' ) {
		if ( ! is_array( $row_datas ) ) { return $row_datas; }
		foreach ( $row_datas as $key => $row_data ) {
			if ( 'excel' == $mode ) {
				if ( preg_match( '|^[\+\-\/0-9]+$|', $row_data ) || preg_match( '|^[\+\-\－\/０-９]+$|', $row_data ) ) {
					$row_data = '=' . '"' . $row_data . '"';
				}
				if ( preg_match( '|^[0-9]+$|', $row_data ) || preg_match( '|^[０-９]+$|', $row_data ) ) {
					$row_data = '=' . '"' . $row_data . '"';
				}
			}
			if ( preg_match( '/[",\'\r\n]/', $row_data ) ) {
				$row_datas[$key]= '"' . preg_replace( '/"/', '""', $row_data ) . '"';
			}
		}
	
		return implode( ',', $row_datas ) . "\n";
	}
}

if ( !class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . '/wp-admin/includes/class-wp-list-table.php' );
}
class Trust_Form_Entries_List_Table extends WP_List_Table {

	var $id = '';
	var $colums_count = '';
	var $max = 3;
	var $entries_data = array();
	var $colum_name = array();
	var $status = '';
	var $responce = array();
 
	/* ==================================================
	 * construct
	 * @param	none
	 * @return	object  $this
	 * @since	1.0
	 */
	function __construct($id = -1) {
		global $status, $page;

		parent::__construct( array(
			'singular' => 'entry',
			'plural'   => 'entries',
			'ajax'     => false	
		) );
		$this->id = $id;
		$this->colum_name = get_post_meta( $id, 'name' );
		$this->colums_count = array_key_exists(0, $this->colum_name) ? count( $this->colum_name[0] ) : 0;
		$this->max = $this->colums_count < $this->max ? $this->colums_count : $this->max;
		$this->max = apply_filters( 'tr_entry_manage_posts_columns_max', $this->max, $this->id );
		$this->status = isset( $_GET['status'] ) ? $_GET['status'] : '';
		
		$this->responce = get_post_meta( $this->id, 'answer' );
		if ( !empty($this->responce) ) {
			foreach( $this->responce as $key => $res ){
				if ( $this->status == '' && $res['trash'] == 'true' || $this->status == 'trash' && $res['trash'] == 'false' || $this->status != '' && $this->status != 'trash' && ( $this->status != $res['status'] || $this->status == $res['status'] && $res['trash'] == 'true' ) )
					continue;
				
				$arr = array();
				$arr['ID'] = $key;
				$i = 0;
				foreach ( $this->colum_name[0] as $key => $name ) {
					if ( $i == $this->max )
						break;
					
					$arr["entry_{$i}"] = array_key_exists( $key, $res['data'] ) ? $res['data'][$key] : '' ;
					$i++;
				} 
				$arr['date'] = $res['data']['date'];
				$this->entries_data[] = $arr;
			}
			$this->entries_data = apply_filters( 'tr_entry_manage_posts_custom_column', $this->entries_data, $this->id );
		}
	}
	
	/* ==================================================
	 * set default column name
	 * @param	$item        String
	 * @param	$column_name String
	 * @return	void
	 * @since	1.0
	 */
	function column_default( $item, $column_name ) {
		return $item[$column_name];
	}

	/* ==================================================
	 * get bulk actions ul li link
	 * @param	none
	 * @return	Array
	 * @since	1.0
	 */
	function get_views() {
		$status = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$class = ' class="current"';

		$views = array();
		$views['all'] = '<a href="?page=trust-form-entries&form='.$this->id.'"'. ( '' == $status ? $class : '' ) .' >' .__( 'All', TRUST_FORM_DOMAIN ). '</a><span class="count">('. $this->entries_count() .')</span>';
		$views['new'] = '<a href="?page=trust-form-entries&form='.$this->id.'&status=new"'. ( 'new' == $status ? $class : '' ) .' >' .__( 'New', TRUST_FORM_DOMAIN ). '</a><span class="count">('. $this->entries_count( 'new' ) .')</span>';
		$views['read'] = '<a href="?page=trust-form-entries&form='.$this->id.'&status=read"'. ( 'read' == $status ? $class : '' ) .' >' .__( 'Read', TRUST_FORM_DOMAIN ). '</a><span class="count">('. $this->entries_count( 'read' ) .')</span>';
		$views['trash'] = '<a href="?page=trust-form-entries&form='.$this->id.'&status=trash"'. ( 'trash' == $status ? $class : '' ) .' >' .__( 'Trash', TRUST_FORM_DOMAIN ). '</a><span class="count">('. $this->entries_count( 'trash' ) .')</span>';

		return $views;
	}

	function entries_count( $status = '' ) {
		$count = 0; 

		if ( empty($this->responce) )
			return $count;
		
		if ( $status == '' ) {
			foreach ( $this->responce as $key => $res ) {
				if ( $res['trash'] == 'false' )
					$count++;
			}
		} elseif ( $status == 'trash' ) {
			foreach ( $this->responce as $key => $res ) {
				if ( $res['trash'] == 'true' )
					$count++;
			}
		} else {
			foreach ( $this->responce as $key => $res ) {
				if ( $res['trash'] == 'false' && $res['status'] == $status )
					$count++;
			}
		}
		return $count;
	}
	
	/* ==================================================
	 * Generates content for a single row of the table
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param object $item The current item
	 * @override
	 */
	function single_row( $item ) {
		static $row_class = '';
		//$row_class = ( $row_class == '' ? ' class="alternate"' : '' );
		$row_class = $this->status == '' && $this->responce[$item['ID']]['status'] == 'read' ? ' class="read"' : '' ;

		echo '<tr' . $row_class . '>';
		echo $this->single_row_columns( $item );
		echo '</tr>';
	}

	/* ==================================================
	 * set column title
	 * @param	$item   Array
	 * @return	void
	 * @since	1.0
	 */
	function column_entry_0( $item ) {
		if ( $this->status == '' ) {
			$trash_url  = sprintf( '?page=%s&action=%s&form=%s&entry=%s' ,$_REQUEST['page'], 'trash', $this->id, $item['ID'] );
			if ( $this->responce[$item['ID']]['status'] == 'new' ) {
				$read_url = sprintf( '?page=%s&action=%s&form=%s&entry=%s' ,$_REQUEST['page'], 'read', $this->id, $item['ID'] );
				$actions = array (
					'view'     => sprintf( '<a href="?page=%s&action=%s&form=%s&entry=%s">'.__( 'View', TRUST_FORM_DOMAIN ).'</a>', $_REQUEST['page'], 'edit', $this->id, $item['ID'] ),
					'read'   => '<a href="'.wp_nonce_url( $read_url, 'bulk-entries' ).'">'.__( 'Move to Read', TRUST_FORM_DOMAIN ).'</a>',
					'trash'    => '<a href="'.wp_nonce_url( $trash_url, 'bulk-entries' ).'">'.__( 'Move to Trash', TRUST_FORM_DOMAIN ).'</a>'
				);
			} elseif ( $this->responce[$item['ID']]['status'] == 'read' ) {
				$new_url = sprintf( '?page=%s&action=%s&form=%s&entry=%s' ,$_REQUEST['page'], 'new', $this->id, $item['ID'] );
				$actions = array (
					'view'     => sprintf( '<a href="?page=%s&action=%s&form=%s&entry=%s">'.__( 'View', TRUST_FORM_DOMAIN ).'</a>', $_REQUEST['page'], 'edit', $this->id, $item['ID'] ),
					'new'   => '<a href="'.wp_nonce_url( $new_url, 'bulk-entries' ).'">'.__( 'Move to New', TRUST_FORM_DOMAIN ).'</a>',
					'trash'    => '<a href="'.wp_nonce_url( $trash_url, 'bulk-entries' ).'">'.__( 'Move to Trash', TRUST_FORM_DOMAIN ).'</a>'
				);
			}
		} elseif ( $this->status == 'new' ) {
			$trash_url  = sprintf( '?page=%s&action=%s&form=%s&entry=%s' ,$_REQUEST['page'], 'trash', $this->id, $item['ID'] );
			$read_url = sprintf( '?page=%s&action=%s&form=%s&entry=%s' ,$_REQUEST['page'], 'read', $this->id, $item['ID'] );
			$actions = array (
				'view'     => sprintf( '<a href="?page=%s&action=%s&form=%s&entry=%s">'.__( 'View', TRUST_FORM_DOMAIN ).'</a>', $_REQUEST['page'], 'edit', $this->id, $item['ID'] ),
				'read'   => '<a href="'.wp_nonce_url( $read_url, 'bulk-entries' ).'">'.__( 'Move to Read', TRUST_FORM_DOMAIN ).'</a>',
				'trash'    => '<a href="'.wp_nonce_url( $trash_url, 'bulk-entries' ).'">'.__( 'Move to Trash', TRUST_FORM_DOMAIN ).'</a>'
			);
		} elseif ( $this->status == 'read' ) {
			$trash_url  = sprintf( '?page=%s&action=%s&form=%s&entry=%s' ,$_REQUEST['page'], 'trash', $this->id, $item['ID'] );
			$new_url = sprintf( '?page=%s&action=%s&form=%s&entry=%s' ,$_REQUEST['page'], 'new', $this->id, $item['ID'] );
			$actions = array (
				'view'     => sprintf( '<a href="?page=%s&action=%s&form=%s&entry=%s">'.__( 'View', TRUST_FORM_DOMAIN ).'</a>', $_REQUEST['page'], 'edit', $this->id, $item['ID'] ),
				'new'   => '<a href="'.wp_nonce_url( $new_url, 'bulk-entries' ).'">'.__( 'Move to New', TRUST_FORM_DOMAIN ).'</a>',
				'trash'    => '<a href="'.wp_nonce_url( $trash_url, 'bulk-entries' ).'">'.__( 'Move to Trash', TRUST_FORM_DOMAIN ).'</a>'
			);
		} else {
			$delete_url = sprintf( '?page=%s&action=%s&form=%s&entry=%s' ,$_REQUEST['page'], 'delete', $this->id, $item['ID'] );
			$restore_url = sprintf( '?page=%s&action=%s&form=%s&entry=%s' ,$_REQUEST['page'], 'untrash',$this->id, $item['ID'] );
			$actions = array (
				'restore'    => '<a href="'.wp_nonce_url( $restore_url, 'bulk-entries').'">' .__( 'Restore', TRUST_FORM_DOMAIN ). '</a>',
				'delete'     => '<a href="'.wp_nonce_url( $delete_url, 'bulk-entries').'">' .__( 'Delete Permanently', TRUST_FORM_DOMAIN ). '</a>'
			);
		}

		return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
						$item['entry_0'],
						$item['ID'],
						$this->row_actions($actions)
					);
	}
	
	/* ==================================================
	 * displaying checkboxes or using bulk actions
	 * @param	$item   Array
	 * @return	void
	 * @since	1.0
	 */
	function column_cb( $item ) {
		return sprintf(
					'<input type="checkbox" name="%1$s[]" value="%2$s" />',
					$this->_args['singular'],
					$item['ID']
		);
	}
	
	/* ==================================================
	 * get columes
	 * @param	none
	 * @return	$columns Array
	 * @since	1.0
	 */
	function get_columns() {
		$columns['cb'] = '<input type="checkbox" />';
		$i=0;
		if( array_key_exists( 0, $this->colum_name ) ) {
			foreach ( $this->colum_name[0] as $key => $name ) {
				if ( $i == $this->max )
					break;
					
				$columns["entry_{$i}"] = $name;
				$i++;
			} 
		}
		$columns['date'] = __( 'Entry Date', TRUST_FORM_DOMAIN );

		$columns = apply_filters( 'tr_entry_manage_posts_columns', $columns, $this->id );
		return $columns;
	}
	
	/* ==================================================
	 * get shortable columes
	 * @param	none
	 * @return	$sortable_columns Array
	 * @since	1.0
	 */
	function get_sortable_columns() {
		$i=0;
		if( array_key_exists( 0, $this->colum_name ) ) {
			foreach ( $this->colum_name[0] as $key => $name ) {
				if ( $i == $this->max )
					break;
					
				$sortable_columns["entry_{$i}"] = array("entry_{$i}",true);
				$i++;
			}
		}
		$sortable_columns['date'] = array('date',true);
		$sortable_columns = apply_filters( 'tr_entry_sortable_columns', $sortable_columns );

		return $sortable_columns;
	}

	/* ==================================================
	 * get bulk actions
	 * @param	none
	 * @return	$actions Array
	 * @since	1.0
	 */
	function get_bulk_actions() {
		
		if ( $this->status == 'trash' ) {
			$actions = array(
				'untrash' => __( 'Restore', TRUST_FORM_DOMAIN ),
				'delete'  => __( 'Delete Permanently', TRUST_FORM_DOMAIN )
			);
		} elseif ( $this->status == 'new' ) {
			$actions = array(
				'read'  => __( 'Move to Read', TRUST_FORM_DOMAIN ),
				'trash'   => __( 'Move to Trash', TRUST_FORM_DOMAIN )
			);
		} elseif ( $this->status == 'read' ) {
			$actions = array(
				'new'  => __( 'Move to New', TRUST_FORM_DOMAIN ),
				'trash'   => __( 'Move to Trash', TRUST_FORM_DOMAIN )
			);
		} else {
			$actions = array(
				'new'  => __( 'Move to New', TRUST_FORM_DOMAIN ),
				'read'  => __( 'Move to Read', TRUST_FORM_DOMAIN ),
				'trash'   => __( 'Move to Trash', TRUST_FORM_DOMAIN )
			);
		}
		return $actions;
	}

	/* ==================================================
	 * extra tablenav
	 * @param	none
	 * @return	void
	 * @since	1.0
	 */	
	function extra_tablenav( $which ) {
?>
		<div class="alignleft actions">
<?php
		if ( $this->status === 'trash' ) {
			submit_button( __( 'Empty Trash' ), 'button-secondary apply', 'delete_all', false );
		}
?>
		</div>
<?php
	}

	/* ==================================================
	 * process bulk action
	 * @param	none
	 * @return	void
	 * @since	1.0
	 */
	function process_bulk_action() {
		if( 'trash' === $this->current_action() ) {
		}
	}

	/* ==================================================
	 * This is where you prepare your data for display
	 * @param	none
	 * @return	void
	 * @since	1.0
	 */
	 function prepare_items() {
        
		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = 30;

		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();


		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column 
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array($columns, $hidden, $sortable);


		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		$this->process_bulk_action();

		/**
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example 
		 * package slightly different than one you might build on your own. In 
		 * this example, we'll be using array manipulation to sort and paginate 
		 * our data. In a real-world implementation, you will probably want to 
		 * use sort and pagination data to build a custom query instead, as you'll
		 * be able to use your precisely-queried data immediately.
		 */
		$data = $this->entries_data;
        

		/**
		 * This checks for sorting input and sorts the data in our array accordingly.
		 * 
		 * In a real-world situation involving a database, you would probably want 
		 * to handle sorting by passing the 'orderby' and 'order' values directly 
		 * to a custom query. The returned data will be pre-sorted, and this array
		 * sorting technique would be unnecessary.
		 */
		function usort_reorder($a,$b){
			$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'date'; //If no sort, default to title
			$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
			$result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
			return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
		}
		usort($data, 'usort_reorder');


		/***********************************************************************
		 * ---------------------------------------------------------------------
		 * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
		 * 
		 * In a real-world situation, this is where you would place your query.
		 * 
		 * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
		 * ---------------------------------------------------------------------
		 **********************************************************************/


		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently 
		 * looking at. We'll need this later, so you should always include it in 
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();

		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array. 
		 * In real-world use, this would be the total number of items in your database, 
		 * without filtering. We'll need this later, so you should always include it 
		 * in your own package classes.
		 */
		$total_items = count($data);


		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to 
		 */
		$data = array_slice($data,(($current_page-1)*$per_page),$per_page);



		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where 
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;


		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
		) );
	}
}

class Trust_Form_Edit_List_Table extends WP_List_Table {

	var $edit_data = array();
	var $status = '';

	/* ==================================================
	 * construct
	 * @param	none
	 * @return	object  $this
	 * @since	1.0
	 */
	function __construct($status = '') {
		global $page;

		parent::__construct( array(
			'singular' => 'form',
			'plural'   => 'forms',
			'ajax'     => false	
		) );
		$this->status = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$args = array( 'post_type' => 'trust-form', 'numberposts' => -1, 'post_status' => $this->status, 'post_parent' => null );
		$forms = get_posts( $args );

		foreach ( $forms as $form ) {
			$this->edit_data[] = array(
                					'ID'        => $form->ID,
                					'title'     => $form->post_title,
                					'date'      => $form->post_modified
								);
		}
		$this->edit_data = apply_filters( 'tr_form_manage_posts_custom_column', $this->edit_data );
	}
	
	/* ==================================================
	 * set default column name
	 * @param	$item        String
	 * @param	$column_name String
	 * @return	void
	 * @since	1.0
	 */
	function column_default( $item, $column_name ) {
		return $item[$column_name];
	}

	/* ==================================================
	 * get bulk actions ul li link
	 * @param	none
	 * @return	Array
	 * @since	1.0
	 */
	function get_views() {

		$class = ' class="current"';
		
		$views = array();
		$views['all'] = '<a href="?page=trust-form-edit"'. ( '' == $this->status ? $class : '' ) .' >' .__( 'All', TRUST_FORM_DOMAIN ). '</a><span class="count">('.$this->entries_count().')</span>';
		
		if (  $this->entries_count( 'trash' ) > 0 )
			$views['trash'] = '<a href="?page=trust-form-edit&status=trash"'. ( 'trash' == $this->status ? $class : '' ) .' >' .__( 'Trash', TRUST_FORM_DOMAIN ). '</a><span class="count">('. $this->entries_count( 'trash' ) .')</span>';

		return $views;
	}
	
	/* ==================================================
	 * set column title
	 * @param	$item   Array
	 * @return	void
	 * @since	1.0
	 */
	function column_title( $item ) {
		if ( $this->status == '' ) {
			$trash_url = sprintf( '?page=%s&action=%s&form=%s' ,$_REQUEST['page'], 'trash', $item['ID'] );
			$duplicate_url = sprintf( '?page=%s&action=%s&form=%s', $_REQUEST['page'], 'duplicate', $item['ID'] );
			$actions = array (
				'edit'      => sprintf( '<a href="?page=%s&action=%s&form=%s">' .__( 'Edit', TRUST_FORM_DOMAIN ). '</a>', $_REQUEST['page'], 'edit', $item['ID'] ),
				'trash'     => '<a href="'.wp_nonce_url( $trash_url, 'bulk-forms').'">' .__( 'Move to Trash', TRUST_FORM_DOMAIN ). '</a>',
				'duplicate' => '<a href="'.wp_nonce_url( $duplicate_url, 'bulk-forms').'">' .__( 'Duplicate', TRUST_FORM_DOMAIN ). '</a>'
			);
		} elseif ( $this->status == 'trash' ){
			$delete_url = sprintf( '?page=%s&action=%s&form=%s' ,$_REQUEST['page'], 'delete', $item['ID'] );
			$restore_url = sprintf( '?page=%s&action=%s&form=%s' ,$_REQUEST['page'], 'untrash', $item['ID'] );
			$actions = array (
				'restore'    => '<a href="'.wp_nonce_url( $restore_url, 'bulk-forms').'">' .__( 'Restore', TRUST_FORM_DOMAIN ). '</a>',
				'delete'     => '<a href="'.wp_nonce_url( $delete_url, 'bulk-forms').'">' .__( 'Delete Permanently', TRUST_FORM_DOMAIN ). '</a>'
			);
		}

		return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
						$item['title'],
						$item['ID'],
						$this->row_actions($actions)
					);
	}
	
	/* ==================================================
	 * displaying checkboxes or using bulk actions
	 * @param	$item   Array
	 * @return	void
	 * @since	1.0
	 */
	function column_cb( $item ) {
		return sprintf(
					'<input type="checkbox" name="%1$s[]" value="%2$s" />',
					$this->_args['singular'],
					$item['ID']
        );
	}
	
	/* ==================================================
	 * get columes
	 * @param	none
	 * @return	$columns Array
	 * @since	1.0
	 */
	function get_columns() {
		$columns = array(
			'cb'    => '<input type="checkbox" />',
			'title' => __( 'Title', TRUST_FORM_DOMAIN ),
			'date'  => __( 'Date', TRUST_FORM_DOMAIN )
		);
		$columns = apply_filters( 'tr_form_manage_posts_columns', $columns );
		return $columns;
	}
	
	/* ==================================================
	 * get shortable columes
	 * @param	none
	 * @return	$sortable_columns Array
	 * @since	1.0
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'title'     => array('title',true),
			//'entries'    => array('entries',true),
			'date'  => array('date',true)
		);
		$sortable_columns = apply_filters( 'tr_form_sortable_columns', $sortable_columns );
		return $sortable_columns;
	}

	/* ==================================================
	 * get bulk actions
	 * @param	none
	 * @return	$actions Array
	 * @since	1.0
	 */
	function get_bulk_actions() {
		if ( $this->status == '' ) {
			$actions = array(
				'trash'     => __( 'Move to Trash', TRUST_FORM_DOMAIN ),
				'duplicate' => __( 'Duplicate', TRUST_FORM_DOMAIN )
			);
		} elseif ( $this->status == 'trash' ) {
			$actions = array(
				'untrash' => __( 'Restore', TRUST_FORM_DOMAIN ),
				'delete'  => __( 'Delete Permanently', TRUST_FORM_DOMAIN )
			);
		}
		return $actions;
	}

	/* ==================================================
	 * extra tablenav
	 * @param	none
	 * @return	void
	 * @since	1.0
	 */	
	function extra_tablenav( $which ) {
?>
		<div class="alignleft actions">
<?php
		if ( $this->status === 'trash' ) {
			submit_button( __( 'Empty Trash' ), 'button-secondary apply', 'delete_all', false );
		}
?>
		</div>
<?php
	}

	/* ==================================================
	 * process bulk action
	 * @param	none
	 * @return	void
	 * @since	1.0
	 */
	function process_bulk_action() {
	}

	function entries_count( $status = '' ) {
		$args = array( 'post_type' => 'trust-form', 'numberposts' => -1, 'post_status' => $status, 'post_parent' => null );
		return count( get_posts( $args ) );
	}
	/* ==================================================
	 * This is where you prepare your data for display
	 * @param	none
	 * @return	void
	 * @since	1.0
	 */
	 function prepare_items() {
        
		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = 30;

		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();


		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column 
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array($columns, $hidden, $sortable);


		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		$this->process_bulk_action();

		/**
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example 
		 * package slightly different than one you might build on your own. In 
		 * this example, we'll be using array manipulation to sort and paginate 
		 * our data. In a real-world implementation, you will probably want to 
		 * use sort and pagination data to build a custom query instead, as you'll
		 * be able to use your precisely-queried data immediately.
		 */
		$data = $this->edit_data;
        

		/**
		 * This checks for sorting input and sorts the data in our array accordingly.
		 * 
		 * In a real-world situation involving a database, you would probably want 
		 * to handle sorting by passing the 'orderby' and 'order' values directly 
		 * to a custom query. The returned data will be pre-sorted, and this array
		 * sorting technique would be unnecessary.
		 */
		function usort_reorder($a,$b){
			$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'date'; //If no sort, default to title
			$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
			$result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
			return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
		}
		usort($data, 'usort_reorder');


		/***********************************************************************
		 * ---------------------------------------------------------------------
		 * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
		 * 
		 * In a real-world situation, this is where you would place your query.
		 * 
		 * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
		 * ---------------------------------------------------------------------
		 **********************************************************************/


		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently 
		 * looking at. We'll need this later, so you should always include it in 
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();

		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array. 
		 * In real-world use, this would be the total number of items in your database, 
		 * without filtering. We'll need this later, so you should always include it 
		 * in your own package classes.
		 */
		$total_items = count($data);


		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to 
		 */
		$data = array_slice($data,(($current_page-1)*$per_page),$per_page);



		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where 
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;


		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
		) );
	}
}

add_shortcode('trust-form', 'trust_form_shortcode');
function trust_form_shortcode($atts) {
	global $trust_form;

	extract(shortcode_atts(array('id' => ''), $atts));

	if( $id == '' )
		return false;

	$trust_form = new Trust_Form_Front($id);

	if (file_exists(get_stylesheet_directory(). '/trust-form-tpl-'.$id.'.php')) {
		require_once(get_stylesheet_directory(). '/trust-form-tpl-'.$id.'.php');
	} elseif (file_exists(get_stylesheet_directory(). '/trust-form-tpl-.php')) {
		require_once(get_stylesheet_directory(). '/trust-form-tpl-.php');
	} else {
		require_once(dirname(  __FILE__ ). '/trust-form-tpl-.php');
	}

	if ( ( isset( $_POST['send-to-confirm'] ) || isset( $_POST['send-to-confirm_x'] ) || ( isset($_POST['mode']) && $_POST['mode'] == 'confirm' ) ) && $trust_form->validate() ) {
		if ( empty($_POST) || !wp_verify_nonce($_POST['trust_form_input_nonce_field'],'trust_form') )
			return trust_form_show_input();
		return trust_form_show_confirm();

	} elseif ( ( isset( $_POST['send-to-finish'] ) || isset( $_POST['send-to-finish_x'] ) || ( isset($_POST['mode']) && $_POST['mode'] == 'finish' ) ) && !$trust_form->is_spam() && $trust_form->validate() ) {
		if ( empty($_POST) || !wp_verify_nonce($_POST['trust_form_confirm_nonce_field'],'trust_form') )
			return trust_form_show_input();

		$trust_form->save();
		return trust_form_show_finish();

	} else {
		return trust_form_show_input();

	}
}

class Trust_Form_Front {
	private $id   = '';
	private $form = '';
	private $name = '';
	private $attention = '';
	private $validate = '';
	private $type = '';
	private $attr = '';
	private $admin_mail = '';
	private $user_mail = '';
	private $config = '';
	private $err_msg = array();
	private $wp_mail_from = '';
	private $wp_mail_from_name = '';

	public function __construct($id){
		$this->id = $id;
		$this->name = get_post_meta($this->id, 'name');
		$this->form = get_post_meta($this->id, 'form_front');
		$this->attention = get_post_meta($this->id, 'attention');
		$this->validate = get_post_meta($this->id, 'validation');
		$this->type = get_post_meta($this->id, 'type');
		$this->attr = get_post_meta($this->id, 'attr');
		$this->admin_mail = get_post_meta($this->id, 'admin_mail');
		$this->config = get_post_meta($this->id, 'config');
		$this->user_mail = get_post_meta($this->id, 'user_mail');
		
		add_filter( 'wp_mail_from', array( &$this, 'wp_mail_from' ) );
		add_filter( 'wp_mail_from_name', array( &$this, 'wp_mail_from_name' ) );
	}
	
	public function get_input_top() {
		return $this->form[0]['input_top'];
	}

	public function get_col_name() {
		return $this->name[0];
	}

	public function get_validate() {	
		return $this->validate[0];
	}
	public function get_config() {	
		return $this->config[0];
	}
	public function get_attention() {	
		return $this->attention[0];
	}
	public function get_err_msg( $key ) {	
		return isset($this->err_msg[$key]) ? $this->err_msg[$key] : '';
	}
	public function get_form( $data ) {	
		return $this->form[0][$data];
	}

	public function is_spam() {
		global $akismet_api_host, $akismet_api_port;

		if ( ! function_exists( 'akismet_get_key' ) || ! akismet_get_key() )
			return false;

		$author = $author_email = $author_url = $content = '';
		if ( isset($this->attr[0]['akismet']) && is_array($this->attr[0]['akismet']) ) {
			foreach( $this->attr[0]['akismet'] as $key => $akismet ) {
				switch( $akismet ) {
					case 'author':
						$author = $_POST[$key];
						break;
					case 'author_email':
						$author_email = $_POST[$key];
						break;
					case 'author_url':
						$author_url = $_POST[$key];
						break;
				}
				$content .= $_POST[$key]."\n\n";
			}
		}
		if ( $author != '' || $author_email != '' || $author_url != '' ) {
			$akismet = array();
			$akismet['blog'] = get_option( 'home' );
			$akismet['blog_lang'] = get_locale();
			$akismet['blog_charset'] = get_option( 'blog_charset' );
			$akismet['user_ip'] = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
			$akismet['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$akismet['referrer'] = $_SERVER['HTTP_REFERER'];
			$akismet['comment_type'] = 'trust-form';

			if ( $permalink = get_permalink() )
				$akismet['permalink'] = $permalink;

			if ( '' != $author )
				$akismet['comment_author'] = $author;

			if ( '' != $author_email )
				$akismet['comment_author_email'] = $author_email;

			if ( '' != $author_url )
				$akismet['comment_author_url'] = $author_url;

			if ( '' != $content )
				$akismet['comment_content'] = $content;

			$ignore = array( 'HTTP_COOKIE', 'HTTP_COOKIE2', 'PHP_AUTH_PW' );

			foreach ( $_SERVER as $key => $value ) {
				if ( ! in_array( $key, (array) $ignore ) )
				$akismet["$key"] = $value;
			}

			$query_string = '';

			foreach ( $akismet as $key => $data )
				$query_string .= $key . '=' . urlencode( stripslashes( (string) $data ) ) . '&';

			$response = akismet_http_post( $query_string,
				$akismet_api_host, '/1.1/comment-check', $akismet_api_port );

			$response = apply_filters( 'tr_akismet_responce', $response );

			return $response[1] == 'true' ? true : false;
		}
	}

	/* ==================================================
	 * Get an input element
	 * @param	none
	 * @return	String
	 * @since	1.0
	 */
	public function get_element( $key ) {

		$class = isset($this->attr[0]['class'][$key]) && $this->attr[0]['class'][$key] != '' ? 'class="'.esc_html($this->attr[0]['class'][$key]).'"' : '';
		switch ( $this->type[0][$key] ) {
			case 'text':
				$value = isset($_POST[$key]) ? $_POST[$key] : '';
				$size = isset($this->attr[0]['size'][$key]) && $this->attr[0]['size'][$key] != '' ? 'size="'.esc_html($this->attr[0]['size'][$key]).'"' : '';
				$maxlength = isset($this->attr[0]['maxlength'][$key]) && $this->attr[0]['maxlength'][$key] != '' ? 'maxlength="'.esc_html($this->attr[0]['maxlength'][$key]).'"' : '';
				return '<input type="text" name="'.esc_html($key).'" '.$size.' '.$maxlength.' '.$class.' value="'.esc_html($value).'" />';
				break;
			case 'textarea':
				$value = isset($_POST[$key]) ? $_POST[$key] : '';
				$cols = isset($this->attr[0]['cols'][$key]) && $this->attr[0]['cols'][$key] != '' ? 'cols="'.esc_html($this->attr[0]['cols'][$key]).'"' : '';
				$rows = isset($this->attr[0]['rows'][$key]) && $this->attr[0]['rows'][$key] != '' ? 'rows="'.esc_html($this->attr[0]['rows'][$key]).'"' : '';
				return '<textarea name="'.esc_html($key).'" '.$rows.' '.$cols.''.$class.' >'.esc_html($value).'</textarea>';
				break;
			case 'selectbox':
				$select  = '<select name="'.esc_html($key).'" '.$class.' >';
				$select .= '<option value="">'.esc_html($this->attr[0]['value'][$key][0]).'</option>';
				unset($this->attr[0]['value'][$key][0]);
				foreach ( $this->attr[0]['value'][$key] as $option ) {
					$selected = isset($_POST[$key]) && $_POST[$key] == $option && $_POST[$key] != '' ? 'selected="selected"' : '';
					$select .= '<option '.$selected.' value="'.esc_html($option).'">'.esc_html($option).'</option>';
				}
				$select .= '</select>';
				return $select;
				break;
			case 'checkbox':
				$checkbox = '<ul>';
				foreach ( $this->attr[0]['value'][$key] as $check ) {
					$checked = isset($_POST[$key]) && in_array($check, $_POST[$key]) ? 'checked="checked"' : '';
					$checkbox .= '<li><label><input type="checkbox" name="'.esc_html($key).'[]" '.$checked.' '.$class.' value="'.esc_html($check).'" />'.esc_html($check).'</label></li>';
				}
				$checkbox .= '</ul>';
				return $checkbox;
				break;
			case 'radio':
				$radio = '<ul>';
				foreach ( $this->attr[0]['value'][$key] as $option ) {
					$checked = isset($_POST[$key]) && $_POST[$key] == $option ? 'checked="checked"' : '';
					$radio .= '<li><label><input type="radio" name="'.esc_html($key).'" '.$checked.' '.$class.' value="'.esc_html($option).'" />'.esc_html($option).'</label></li>';
				}
				$radio .= '</ul>';
				return $radio;
				break;
			case 'e-mail':
				$value = isset($_POST[$key]) ? $_POST[$key] : '';
				$size = isset($this->attr[0]['size'][$key]) && $this->attr[0]['size'][$key] != '' ? 'size="'.esc_html($this->attr[0]['size'][$key]).'"' : '';
				$maxlength = isset($this->attr[0]['maxlength'][$key]) && $this->attr[0]['maxlength'][$key] != '' ? 'maxlength="'.esc_html($this->attr[0]['maxlength'][$key]).'"' : '';
				return '<input type="text" name="'.esc_html($key).'" '.$size.' '.$maxlength.' '.$class.' value="'.esc_html($value).'" />';
				break;
		}
	}

	/* ==================================================
	 * Get an input POST data
	 * @param	none
	 * @return	String
	 * @since	1.0
	 */	
	public function get_input_data( $key ) {
		switch ( $this->type[0][$key] ) {
			case 'checkbox':
				$checkbox = '';
				if ( isset($_POST[$key]) ) {
					$checkbox .= '<ul>';
					if ( is_array($_POST[$key]) ) {
						foreach( $_POST[$key] as $val ) {
							$checkbox .= '<li>'.esc_html($val).'<input type="hidden" name="'.esc_html($key).'[]" value="'.esc_html($val).'" /></li>'; 
						}
					}
				}
				$checkbox .= '</ul>';
				return $checkbox;
				break;
			case 'textarea':
				return str_replace( "\n", '<br />', esc_html($_POST[$key])).'<input type="hidden" name="'.esc_html($key).'" value="'.esc_html($_POST[$key]).'" />';
				break;
			default:
				return esc_html( isset($_POST[$key]) ? $_POST[$key] : '' ).'<input type="hidden" name="'.esc_html($key).'" value="'.esc_html( isset($_POST[$key]) ? $_POST[$key] : '' ).'" />';
				break;
		}
	}

	/* ==================================================
	 * Save the POST data into DB and send mail
	 * @param	none
	 * @return	void
	 * @since	1.0
	 */
	public function save() {

		$new_responce = array();
		foreach( $this->name[0] as $key => $name ) {
			switch ( $this->type[0][$key] ) {
				case 'checkbox':
					$checkbox = '';
					if ( isset($_POST[$key]) ) {
						foreach ( $_POST[$key] as $val ) {
							$checkbox .= $val.',';
						}
					}
					$new_responce['data'][$key] = rtrim($checkbox, ',');
					break;
				default:
					if ( isset($_POST[$key]) ) {
						$new_responce['data'][$key] = $_POST[$key];
					}
					break;
			}
			$new_responce['title'][$key] = $name;
			
			foreach ( $this->validate[0] as $validate ) {
				if ( array_key_exists('e_mail_confirm', $validate) && in_array($name, $validate) ) {
					unset($new_responce['title'][$key]);
					unset($new_responce['data'][$key]);
				}
			}
		}
		
		$new_responce['data']["date"] = date_i18n('Y/m/d H:i:s');
		
		$new_responce["status"] = 'new';
		$new_responce["trash"] = 'false';
		$new_responce["note"] = array();
		$new_responce = apply_filters( 'tr_new_responce', $new_responce, $this->type[0], $this->id );

		$prev_responce = get_post_meta( $this->id, 'answer' );
		foreach ( $prev_responce as $r ) {
			if ( $r == $new_responce )
				return;
		}

		if ( !defined( 'TRUST_FORM_DB_SUPPORT' ) || TRUST_FORM_DB_SUPPORT !== false )
			add_post_meta( $this->id, 'answer', $new_responce );
		
		if ( $this->user_mail[0]['user_mail_y'] === '1' ) {
			foreach( $this->name[0] as $key => $name ) {
				if (  $this->type[0][$key] =='e-mail' ) {
					add_filter( 'wp_mail_from', array(&$this, 'wp_user_mail_from'), 50 );
					add_filter( 'wp_mail_from_name',  array(&$this, 'wp_user_mail_from_name'), 50 );
					if ( isset($_POST[$key]) )
						$this->_send_user_mail( $this->id, $new_responce, $_POST[$key] );
				}
			}
		}

		add_filter( 'wp_mail_from', array(&$this, 'wp_mail_from'), 100);
		add_filter( 'wp_mail_from_name',  array(&$this, 'wp_mail_from_name'), 100 );

		if ( $this->_has_shortcode($this->admin_mail[0]['from']) ) {
			$mail = str_replace( '[', '', $this->admin_mail[0]['from'] );
			$mail = str_replace( ']', '', $mail );
			foreach( $this->name[0] as $key => $name ) {
				if ( $mail == $name ) {
					if ( isset($_POST[$key]) ) {
						$this->wp_mail_from = $_POST[$key];
						add_filter( 'wp_mail_from', array(&$this, 'wp_mail_from_advanced'), 150 );
					}
				}
			}
		}
		if ( $this->_has_shortcode($this->admin_mail[0]['from_name']) ) {
			$mail = str_replace( '[', '', $this->admin_mail[0]['from_name'] );
			$mail = str_replace( ']', '', $mail );
			foreach( $this->name[0] as $key => $name ) {
				if ( $mail == $name ) {
					if ( isset($_POST[$key]) ) {
						$this->wp_mail_from_name = $_POST[$key];
						add_filter( 'wp_mail_from_name', array(&$this, 'wp_mail_from_name_advanced'), 150 );
					}
				}
			}
		}
		$this->_send_admin_mail( $this->id, $new_responce );

	}
	
	/* ==================================================
	 * check shortcode 
	 * @param	none
	 * @return	void
	 * @since	1.01
	 */
	private function _has_shortcode($shortcode) {
		$pattern = '/\[.*\]/im';
		if ( preg_match($pattern, $shortcode) ) {
			return true;
		} else {
			return false;
		}
	}
	 
	/* ==================================================
	 * Send auto reply mail
	 * @param	none
	 * @return	void
	 * @since	1.01
	 */
	private function _send_user_mail( $id, $data, $to ){
		$data['data'] = apply_filters( 'tr_pre_auto_reply_mail', $data['data'], $id );
		$body = '';
		foreach ( $data['data'] as $key => $res ) {
			if ( $key == 'date' ) {
				$body .= __( 'Date', TRUST_FORM_DOMAIN ).': '.$res."\n\n";
			} else {
				if ( isset($data['title'][$key]) ) {
					$body .= $data['title'][$key].': '.$res."\n\n";
				}
			}
		}
		$body = str_replace( '[FORM DATA]', $body, $this->user_mail[0]['message2'] );

		foreach ( $data['data'] as $key => $res ) {
			$body = str_replace( '['.$key.']', $res, $body );
		}

		foreach ( $this->name[0] as $key => $name ) {
			if ( preg_match('/['.$name.']/i', $body) ) {
				if ( isset($data['data'][$key]) )
					$body = str_replace( '['.$name.']', $data['data'][$key], $body );
			}
		}

		$subject = $this->user_mail[0]['subject2'];
		foreach ( $this->name[0] as $key => $name ) {
			if ( preg_match('/['.$name.']/i', $subject) ) {
				if ( isset($data['data'][$key]) )
					$subject = str_replace( '['.$name.']', $data['data'][$key], $subject );
			}
		}
		$subject = apply_filters( 'tr_pre_auto_reply_mail_subject', $subject, $data['data'], $id );
		$body = apply_filters( 'tr_pre_auto_reply_mail_body', $body, $data['data'], $id );
		
		wp_mail( $to, $subject, $body );
		do_action( 'trust_form_sent_auto_reply_mail', $data, $id, $to );
	}

	/* ==================================================
	 * Send admin mail
	 * @param	none
	 * @return	void
	 * @since	1.0
	 */
	private function _send_admin_mail( $id, $data ){
		$data['data'] = apply_filters( 'tr_pre_admin_mail', $data['data'], $id );
		$body = '';
		if ( !defined( 'TRUST_FORM_DB_SUPPORT' ) || TRUST_FORM_DB_SUPPORT !== false ) {
			$body .= admin_url( '/admin.php?page=trust-form-entries&form='.$id.'&status=new' );
			$body .= "\n\n";
		}
		foreach ( $data['data'] as $key => $res ) {
			if ( $key == 'date' ) {
				$body .= apply_filters( 'tr_pre_date_admin_mail', __( 'Date', TRUST_FORM_DOMAIN ).': '.$res."\n\n", $id );
			} else {
				if ( isset($data['title'][$key]) ) {
					$body .= apply_filters( 'tr_pre_title_admin_mail', $data['title'][$key].': '.$res."\n\n", $id );
				}
			}
		}
		$body = apply_filters( 'tr_pre_send_mail', $body, $data['data'], $id );

		$subject = $this->admin_mail[0]['subject'];
		foreach ( $this->name[0] as $key => $name ) {
			if ( preg_match('/['.$name.']/i', $subject) ) {
				if ( isset($data['data'][$key]) )
					$subject = str_replace( '['.$name.']', $data['data'][$key], $subject );
			}
		}

		$subject = apply_filters( 'tr_subject_admin_mail', $subject, $data['data'], $id );

		$headers = '';
		$headers .= $this->admin_mail[0]['cc'] != '' ? 'cc:' . $this->admin_mail[0]['cc'] . "\n" : '' ;
		$headers .= $this->admin_mail[0]['bcc'] != '' ? 'bcc:' . $this->admin_mail[0]['bcc'] . "\n" : '' ;
		wp_mail( apply_filters( 'trust_form_admin_mail_to', $this->admin_mail[0]['to'], $id ) , $subject, $body, $headers );
		do_action( 'trust_form_sent_admin_mail', $data, $id );
	}

	public function wp_mail_from( $mail_from ) {
		return $this->admin_mail[0]['from'];
	}

	public function wp_mail_from_name( $mail_from_name ) {
		return wp_specialchars_decode($this->admin_mail[0]['from_name'], ENT_QUOTES);
	}
	
	public function wp_mail_from_advanced( $mail_from ) {
		return $this->wp_mail_from;
	}

	public function wp_mail_from_name_advanced( $mail_from_name ) {
		return wp_specialchars_decode($this->wp_mail_from_name, ENT_QUOTES);
	}

	public function wp_user_mail_from( $mail_from ) {
		return $this->user_mail[0]['from2'];
	}

	public function wp_user_mail_from_name( $mail_from_name ) {
		return wp_specialchars_decode($this->user_mail[0]['from_name2'], ENT_QUOTES);
	}

	/* ==================================================
	 * do validation
	 * @param	none
	 * @return	void
	 * @since	1.0
	 */
	public function validate() {
		global $Trust_Form_Validator_Message;

		foreach ( $this->name[0] as $key => $name ) {
			switch ( $this->type[0][$key] ) {
				case 'text':
					//required
					if ( isset($this->validate[0][$key]['required']) && $this->validate[0][$key]['required'] == 'true' && !Trust_Form_Validator::required($_POST[$key]) )
						$this->err_msg[$key][] = $Trust_Form_Validator_Message['required'];
					//length
					if ( isset($_POST[$key]) && $_POST[$key] != '' ) {
						if ( isset($this->validate[0][$key]['min']) && is_numeric($this->validate[0][$key]['min']) 
						&& isset($this->validate[0][$key]['max']) && is_numeric($this->validate[0][$key]['max'])  
						&& (!Trust_Form_Validator::maxlength($_POST[$key], $this->validate[0][$key]['max']) 
						|| !Trust_Form_Validator::minlength($_POST[$key], $this->validate[0][$key]['min']))) {
							$tmp = str_replace( '__maxlength__', $this->validate[0][$key]['max'], $Trust_Form_Validator_Message['bothlength'] );
							$tmp = str_replace( '__minlength__', $this->validate[0][$key]['min'], $tmp );
							$this->err_msg[$key][] = $tmp;
						} elseif ( isset($this->validate[0][$key]['min']) && is_numeric($this->validate[0][$key]['min']) && !Trust_Form_Validator::minlength($_POST[$key], $this->validate[0][$key]['min'])) {
							$this->err_msg[$key][] = str_replace( '__minlength__', $this->validate[0][$key]['min'], $Trust_Form_Validator_Message['minlength'] );
						} elseif ( isset($this->validate[0][$key]['max']) && is_numeric($this->validate[0][$key]['max']) && !Trust_Form_Validator::maxlength($_POST[$key], $this->validate[0][$key]['max'])) {
							$this->err_msg[$key][] = str_replace( '__maxlength__', $this->validate[0][$key]['max'], $Trust_Form_Validator_Message['maxlength'] );
						}
					}
					//charactors
					if ( isset($_POST[$key]) && $_POST[$key] != '' ) {
						if ( isset($this->validate[0][$key]['charactor']) && $this->validate[0][$key]['charactor'] == 'alphabet' && !Trust_Form_Validator::isAlpha($_POST[$key]) ){
							$this->err_msg[$key][] = $Trust_Form_Validator_Message['eiji'];
						} elseif ( isset($this->validate[0][$key]['charactor']) && $this->validate[0][$key]['charactor'] == 'numeric' && !Trust_Form_Validator::isNumber($_POST[$key]) ){
							$this->err_msg[$key][] = $Trust_Form_Validator_Message['number'];
						} elseif ( isset($this->validate[0][$key]['charactor']) && $this->validate[0][$key]['charactor'] == 'alphanumeric' && !Trust_Form_Validator::isHankaku($_POST[$key]) ){
							$this->err_msg[$key][] = $Trust_Form_Validator_Message['hankaku'];
						} elseif ( isset($this->validate[0][$key]['charactor']) && $this->validate[0][$key]['charactor'] == 'alphanumeric-and-code' && !Trust_Form_Validator::isHankaku2($_POST[$key]) ){
							$this->err_msg[$key][] = $Trust_Form_Validator_Message['hankaku2'];
						}
					}
					//multibyte-charactors
					if ( isset($_POST[$key]) && $_POST[$key] != '' ) {
						if ( isset($this->validate[0][$key]['multi-charactor']) && $this->validate[0][$key]['multi-charactor'] == 'multibyte' && !Trust_Form_Validator::isZenkaku($_POST[$key]) ){
							$this->err_msg[$key][] = $Trust_Form_Validator_Message['zenkaku'];
						} elseif( isset($this->validate[0][$key]['multi-charactor']) && $this->validate[0][$key]['multi-charactor'] == 'katakana' && !Trust_Form_Validator::isKatakana($_POST[$key]) ){
							$this->err_msg[$key][] = $Trust_Form_Validator_Message['katakana'];
						} elseif( isset($this->validate[0][$key]['multi-charactor']) && $this->validate[0][$key]['multi-charactor'] == 'hiragana' && !Trust_Form_Validator::isHiragana($_POST[$key]) ){
							$this->err_msg[$key][] = $Trust_Form_Validator_Message['hiragana'];
						}
					}
					break;
				case 'radio':
					//required
					if ( isset($this->validate[0][$key]['required']) && $this->validate[0][$key]['required'] == 'true' && !array_key_exists($key, $_POST) )
						$this->err_msg[$key][] = $Trust_Form_Validator_Message['required'];
					break;
				case 'selectbox':
					//required
					if ( isset($this->validate[0][$key]['required']) && $this->validate[0][$key]['required'] == 'true' && $_POST[$key] == '' )
						$this->err_msg[$key][] = $Trust_Form_Validator_Message['required'];
					break;
				case 'checkbox':
					//required
					$ch_key = rtrim($key, '[]');
					if ( isset($this->validate[0][$key]['required']) && $this->validate[0][$key]['required'] == 'true' && !array_key_exists($ch_key, $_POST) )
						$this->err_msg[$key][] = $Trust_Form_Validator_Message['required'];
					break;
				case 'textarea':
					//required
					if ( isset($this->validate[0][$key]['required']) && $this->validate[0][$key]['required'] == 'true' && !Trust_Form_Validator::required($_POST[$key]) )
						$this->err_msg[$key][] = $Trust_Form_Validator_Message['required'];
					//length
					if ( isset($this->validate[0][$key]['min']) && is_numeric($this->validate[0][$key]['min']) 
					&& isset($this->validate[0][$key]['max']) && is_numeric($this->validate[0][$key]['max'])  
					&& (!Trust_Form_Validator::maxlength($_POST[$key], $this->validate[0][$key]['max']) 
					|| !Trust_Form_Validator::minlength($_POST[$key], $this->validate[0][$key]['min']))) {
						$tmp = str_replace( '__maxlength__', $this->validate[0][$key]['max'], $Trust_Form_Validator_Message['bothlength'] );
						$tmp = str_replace( '__minlength__', $this->validate[0][$key]['min'], $tmp );
						$this->err_msg[$key][] = $tmp;
					} elseif ( isset($this->validate[0][$key]['min']) && is_numeric($this->validate[0][$key]['min']) && !Trust_Form_Validator::minlength($_POST[$key], $this->validate[0][$key]['min'])) {
						$this->err_msg[$key][] = str_replace( '__minlength__', $this->validate[0][$key]['min'], $Trust_Form_Validator_Message['minlength'] );
					} elseif ( isset($this->validate[0][$key]['max']) && is_numeric($this->validate[0][$key]['max']) && !Trust_Form_Validator::maxlength($_POST[$key], $this->validate[0][$key]['max'])) {
						$this->err_msg[$key][] = str_replace( '__maxlength__', $this->validate[0][$key]['max'], $Trust_Form_Validator_Message['maxlength'] );
					}					
					break;
				case 'e-mail':
					//required
					if ( isset($this->validate[0][$key]['required']) && $this->validate[0][$key]['required'] == 'true' && !Trust_Form_Validator::required($_POST[$key]) )
						$this->err_msg[$key][] = $Trust_Form_Validator_Message['required'];
					if ( $_POST[$key] != "" && !is_email($_POST[$key]) )
						$this->err_msg[$key][] = $Trust_Form_Validator_Message['e-mail'];
					if ( isset( $_POST['send-to-confirm'] ) && isset($this->validate[0][$key]['e_mail_confirm']) && $this->validate[0][$key]['e_mail_confirm'] != '' ) {
						foreach ( $this->name[0] as $key_1 => $name_1 ) {
							if ( $this->validate[0][$key]['e_mail_confirm'] == $name_1 ) {
								if ( $_POST[$key] != $_POST[$key_1] )
									$this->err_msg[$key][] = $Trust_Form_Validator_Message['e_mail_confirm'];
							}
						}
					}
						
			}
		}

		$this->err_msg = apply_filters( 'trust_form_validate_error_messages', $this->err_msg, $this->id );

		if ( empty($this->err_msg) ) {
			return true;
		} else {
			return false;
		}
	}
}

class Trust_Form_Validator {

	//必須チェック
	static public function required($str) {
		$str = trim($str);
		if ($str != "") {
			return true;
		}
		return false;
	}
	
	//半角数字チェック
	static public function isNumber($str) {
		if (preg_match("/^[0-9]+$/", $str)) {
    		return true;
		}
		return false;
	}
	
	//半角英字チェック
	static public function isAlpha($str) {
		if (preg_match("/^[a-zA-Z]+$/", $str)) {
			return true;
		}
		return false;
	}
	
	//半角記号チェック
	static public function isSign($str) {
		//if (preg_match('/^[-\/:-@\[-`\{-\~\+]+$/', $str)) {
		if (preg_match('/^[\@-\/]+$/', $str)) {
			return true;
		}
		return false;
	}

	//半角英字記号チェック
	static public function isEijiSign($str) {
		if (preg_match("/^[-\/:-@\[-`\{-\~]+$/", $str)) {
			return true;
		}
		return false;
	}
	
	//半角数値記号チェック
	static public function isNumberSign($str) {
		if (preg_match("/^[\x21-\x39]+$/", $str)) {
			return true;
		}
		return false;
	}
	
	//半角英数記号チェック
	static public function isHankaku2($str) {
		if (preg_match("/^[\x21-\x7E]+$/", $str)) {
			return true;
		}
		return false;
	}

	
	//半角英数字チェック
	static public function isHankaku($str) {
		if (preg_match("/^[a-zA-Z0-9]+$/", $str)) {
			return true;
		}
		return false;
	}
	
	//全角チェック
	static public function isZenkaku($str) {
		if( !preg_match( "/(?:\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F])|[\x20-\x7E]/", $str ) ) {
			return true;
		}
		return false;
	}
	
	//全角ひらがな
	static public function isHiragana($str) {
		if (preg_match("/^[ぁ-ん]+$/u", $str)) {
    		return true;
		}
		return false;
	}
	
	//全角カタカナ
	static public function isKatakana($str) {
		if (preg_match("/^[ァ-ヶー]+$/u", $str)) {  
    		return true;
		}
		return false;
	}
	
	//最大長チェック
	static public function maxlength($str, $max) {
		$str = mb_convert_encoding($str, "UTF-8", "auto");
		if (mb_strlen($str, 'UTF-8') <= $max) {
			return true;
		}
		return false;
	}
	
	//最小長チェック
	static public function minlength($str, $min) {
		$str = mb_convert_encoding($str, "UTF-8", "auto");
		if (mb_strlen($str, 'UTF-8') >= $min) {
			return true;
		}
		return false;
	}
	
	//長さの一致チェック
	static public function fixlength($str, $fix) {
		$str = mb_convert_encoding($str, "UTF-8", "auto");
		if (mb_strlen($str, 'UTF-8') === $fix) {
			return true;
		}
		return false;
	}
	
	//メールアドレスチェック
	static public function check_mail($str, $mode = '') {
		if ($mode == '' && preg_match("/^[!-~]+@[!-~]+$/", $str)) {
			return true;
		} elseif ($mode == 'NOTRFC' && preg_match('/^([\w])+([\w\._-])*\@([\w])+([\w\._-])*\.([a-zA-Z])+$/', $str)) {
			return true;
		} elseif ($mode == 'RFC' && preg_match('/^(?:[^(\040)<>@,;:".\\\\\[\]\000-\037\x80-\xff]+(?![^(\040)<>@,;:".\\\\\[\]\000-\037\x80-\xff])|"[^\\\\\x80-\xff\n\015"]*(?:\\\\[^\x80-\xff][^\\\\\x80-\xff\n\015"]*)*")(?:\.(?:[^(\040)<>@,;:".\\\\\[\]\000-\037\x80-\xff]+(?![^(\040)<>@,;:".\\\\\[\]\000-\037\x80-\xff])|"[^\\\\\x80-\xff\n\015"]*(?:\\\\[^\x80-\xff][^\\\\\x80-\xff\n\015"]*)*"))*@(?:[^(\040)<>@,;:".\\\\\[\]\000-\037\x80-\xff]+(?![^(\040)<>@,;:".\\\\\[\]\000-\037\x80-\xff])|\[(?:[^\\\\\x80-\xff\n\015\[\]]|\\\\[^\x80-\xff])*\])(?:\.(?:[^(\040)<>@,;:".\\\\\[\]\000-\037\x80-\xff]+(?![^(\040)<>@,;:".\\\\\[\]\000-\037\x80-\xff])|\[(?:[^\\\\\x80-\xff\n\015\[\]]|\\\\[^\x80-\xff])*\]))*$/', $str)) {
			return true;
		}
		return false;
	}
	
	//メールアドレスのドメイン部レコードの確認
	static public function check_dns_record($str, $mode="") {
		if ($mode!="" && self::check_mail($str)) {
			$mail = explode('@', $str);
			return checkdnsrr($mail[1], $mode);
		}
		return false;
	}
	
	//配列と一致すればエラー
	static public function check_list( $str, $list=array() ) {
		$flg = true;
		foreach($list as $val) {
			if ($val === $str) {
				$flg = false;
				break;
			}
		}
		return $flg;
	}
	
	//確認用フォームのチェック
	static public function check_confirm( $str, $target ) {
		if ($str === $target) {
			return true;
		}
		return false;
	}
	
	//携帯電話番号チェック
	static public function is_mobile_easy( $str ) {
		if( preg_match( "/^0[57-9]0\d{4}\d{4}$/", $str ) ) {
			return true;
		}
		return false;
	}
	
	//携帯電話番号チェック
	static public function is_mobile_exact( $str1, $str2, $str3 ) {
		$str = $str1.'-'.$str2.'-'.$str3;
		if( preg_match( "/^0[57-9]0-\d{4}-\d{4}$/", $str ) ) {
			return true;
		}
		return false;
	}
	
	//固定電話チェック
	static protected function is_phone_easy( $str ) {
		if( preg_match( "/^0\d{9}$/", $str ) ) {
			return true;
		}
		return false;
	}
	
	//固定電話チェック
	static public function is_phone_exact( $str1, $str2, $str3 ) {
		$str = $str1.'-'.$str2.'-'.$str3;
		if( preg_match( "/^(0(?:[1-9]|[1-9]{2}\d{0,2}))-([2-9]\d{0,3})-(\d{4})$/", $str ) && strlen($str) == 12 ) {
			return true;
		}
		return false;
	}
	
	//住所フォーム都道府県セレクトボックスの突き合わせ
	static public function is_zip_code( $str ) {
		global $iqfm_zip_data;

		if( array_key_exists($str, $iqfm_zip_data) && $str !== '0' ){
			return true;
		}
		return false;
	}
}

$Trust_Form_Validator_Message = array(
	'required'   => __("Please fill required field", TRUST_FORM_DOMAIN),
	'hiragana'   => __("Please enter by using Japanese hiragana", TRUST_FORM_DOMAIN),
	'minlength'  => __("Please enter more than __minlength__ charactors", TRUST_FORM_DOMAIN),
	'maxlength'  => __("Please enter within __maxlength__ charactors", TRUST_FORM_DOMAIN),
	'bothlength' => __("Please enter more than __minlength__ within __maxlength__ charactors", TRUST_FORM_DOMAIN),
	'zenkaku'    => __("Please enter by using multibyte charactor", TRUST_FORM_DOMAIN),
	'eiji'       => __("Please enter by using English", TRUST_FORM_DOMAIN),
	'number'     => __("Please enter by using number", TRUST_FORM_DOMAIN),
	'katakana'   => __("Please enter by using Japanese katakana", TRUST_FORM_DOMAIN),
	'hankaku'    => __("Please enter by using English or number", TRUST_FORM_DOMAIN),
	'hankaku2'   => __("Please enter by using English or number or code", TRUST_FORM_DOMAIN),
	'e-mail'     => __("The format of the e-mail address is invalid", TRUST_FORM_DOMAIN),
	'e_mail_confirm' => __("The e-mail mismatch", TRUST_FORM_DOMAIN)
//	const REQUIRED_SELECT  = 'が選択されていません';
//	const MAXLENGTH        = 'は__maxlength__文字以下で入力してください';
//	const MINLENGTH        = 'は__minlength__文字以上で入力してください';
//	const BOTHLENGTH       = 'は__minlength__文字以上__maxlength__文字以下で入力してください';
//	const FIXLENGTH        = 'は__fixlength__文字で入力してください';
//	const HANKAKU          = 'は半角英数字で入力してください';
//	const EIJI             = 'は半角英字で入力してください';
//	const NUMBER           = 'は半角数字で入力してください';
//	const ZENKAKU          = 'は全角で入力してください';
//	const HIRAGANA         = 'は全角ひらがなで入力してください';
//	const KATAKANA         = 'は全角カタカナで入力してください';
//	const KEISHIKI         = 'の形式が正しくありません';
//	const BANNED           = 'に__banned__は指定できません';
//	const CONFIRM          = '(確認用)と一致しません';
//	const SIGN             = 'は半角記号で入力してください';
//	const SIGNEIJI         = 'は半角英字または半角記号で入力してください';
//	const SIGNUMBER        = 'は半角数字または半角記号で入力してください';
//	const HANKAKU2         = 'は半角英数字または半角記号で入力してください';
);

add_action( 'the_content', 'trust_form_change_validate_message' );
function trust_form_change_validate_message( $content ) {
	global $Trust_Form_Validator_Message;
	$Trust_Form_Validator_Message['required'] = apply_filters( 'tr_validate_message_required', $Trust_Form_Validator_Message['required'] );
	$Trust_Form_Validator_Message['e-mail'] = apply_filters( 'tr_validate_message_mail', $Trust_Form_Validator_Message['e-mail'] );
	$Trust_Form_Validator_Message['hiragana'] = apply_filters( 'tr_validate_message_hiragana', $Trust_Form_Validator_Message['hiragana'] );
	$Trust_Form_Validator_Message['minlength'] = apply_filters( 'tr_validate_message_minlength', $Trust_Form_Validator_Message['minlength'] );
	$Trust_Form_Validator_Message['maxlength'] = apply_filters( 'tr_validate_message_maxlength', $Trust_Form_Validator_Message['maxlength'] );
	$Trust_Form_Validator_Message['bothlength'] = apply_filters( 'tr_validate_message_bothlength', $Trust_Form_Validator_Message['bothlength'] );
	$Trust_Form_Validator_Message['zenkaku'] = apply_filters( 'tr_validate_message_zenkaku', $Trust_Form_Validator_Message['zenkaku'] );
	$Trust_Form_Validator_Message['eiji'] = apply_filters( 'tr_validate_message_eiji', $Trust_Form_Validator_Message['eiji'] );
	$Trust_Form_Validator_Message['number'] = apply_filters( 'tr_validate_message_number', $Trust_Form_Validator_Message['number'] );
	$Trust_Form_Validator_Message['katakana'] = apply_filters( 'tr_validate_message_katakana', $Trust_Form_Validator_Message['katakana'] );
	$Trust_Form_Validator_Message['hankaku'] = apply_filters( 'tr_validate_message_hankaku', $Trust_Form_Validator_Message['hankaku'] );
	$Trust_Form_Validator_Message['hankaku2'] = apply_filters( 'tr_validate_message_hankaku2', $Trust_Form_Validator_Message['hankaku2'] );
	$Trust_Form_Validator_Message['e_mail_confirm'] = apply_filters( 'tr_validate_message_e_mail_confirm', $Trust_Form_Validator_Message['e_mail_confirm'] );
	
	return $content;
}

class Trust_Form_Other_Setting {

	public function __construct() {
		add_filter( 'tr_new_responce', array( $this, 'add_responce' ), 20, 3 );
		add_action( 'tr_entry_action', array( $this, 'entry_action' ), 20, 3 );
		add_filter( 'tr_pre_send_mail', array( $this, 'admin_mail' ), 20, 3 );
	}

	private function _unserialized($data) {
		if ( $data == '' )
			return false;

		$data = explode("\n", $data);
		$data = array_map('trim', $data);
		$data = array_filter($data, 'strlen');
		$data = array_values($data);
	
		return $data;
	}
	
	public function admin_mail( $body, $data, $id ) {
		$other_setting = $this->_unserialized(get_post_meta( $id, 'other_setting', true ));
		
		if ( is_array($other_setting) ) {
			foreach ( $other_setting as $setting ) {
				$setting = explode(':', $setting);
				if ( $setting[0] == 'server' && isset($data[$setting[1]]) ) {
					$body .= $setting[1] .': '. $data[$setting[1]] . "\n\n";
				}
			}
		}
		
		return $body;
	}
	
	public function add_responce($res, $type, $id) {
		$other_setting = $this->_unserialized(get_post_meta( $id, 'other_setting', true ));
		
		if ( is_array($other_setting) ) {
			foreach ( $other_setting as $setting ) {
				$setting = explode(':', $setting);
				if ( $setting[0] == 'server' )
					$res['data'][$setting[1]] = $_SERVER[$setting[1]];
			}
		}
			
		return $res;
	}
	
	public function entry_action( $entry, $form_id, $entry_id ) {
		$other_setting = $this->_unserialized(get_post_meta( $form_id, 'other_setting', true ));
		if ( is_array($other_setting) ) {
			foreach ( $other_setting as $setting ) {
				$setting = explode(':', $setting);
				if ( $setting[0] == 'server' && array_key_exists( $setting[1], $entry['data'] ) )
					echo '<tr><th scope="row">' . esc_html($setting[1]) . '</th><td>' . $entry['data'][$setting[1]] . '</td></tr>';
			}
		}
	}
}
new Trust_Form_Other_Setting();

class Trust_Form_API {

	/* ==================================================
	 * duplicate form
	 * @param	form_id
	 * @return	form_id or wp_die 
	 * @since	1.8.4
	 */
	public function duplicate( $form_id ) {
		$post = get_post( $form_id );

		$post_title = apply_filters( 'trust_form_duplicate_form_title', sprintf( __( 'Copy of %s', TRUST_FORM_DOMAIN ), $post->post_title, $form_id ));

		$new = array(
		    'post_author' => $post->post_author,
		    'post_date' => $post->post_date,
		    'post_date_gmt' => $post->post_date_gmt,
		    'post_content' => $post->post_content,
		    'post_title' => $post_title,
		    'post_excerpt' => $post->post_excerpt,
		    'post_status' => $post->post_status,
		    'comment_status' => $post->comment_status,
		    'ping_status' => $post->ping_status,
		    'post_password' => $post->post_password,
		    'post_name' => $post->post_name,
		    'to_ping' => $post->to_ping,
		    'pinged' => $post->pinged,
		    'post_modified' => $post->post_modified,
		    'post_modified_gmt' => $post->post_modified_gmt,
		    'post_content_filtered' => $post->post_content_filtered,
		    'post_parent' => $post->post_parent,
		    'guid' => $post->guid,
		    'menu_order' => $post->menu_order,
		    'post_type' => $post->post_type,
		    'post_mime_type' => $post->post_mime_type,
		    'comment_count' => $post->comment_count
		);
		if ( !$new_id = wp_insert_post($new) )
		    wp_die( __('Error in duplicating.') );
		
		$data = array( 
		    		'name' => get_post_meta( $form_id, 'name' ),
		    		'attention' => get_post_meta( $form_id, 'attention' ),
		    		'type' => get_post_meta( $form_id, 'type' ),
		    		'validation' => get_post_meta( $form_id, 'validation' ),
		    		'attr' => get_post_meta( $form_id, 'attr' ),
		    		'admin_mail' => get_post_meta( $form_id, 'admin_mail' ),
		    		'user_mail' => get_post_meta( $form_id, 'user_mail' ),
		    		'form_admin_input' => get_post_meta( $form_id, 'form_admin_input' ),
		    		'form_admin_confirm' => get_post_meta( $form_id, 'form_admin_confirm' ),
		    		'form_admin_finish' => get_post_meta( $form_id, 'form_admin_finish' ),
		    		'form_front' => get_post_meta( $form_id, 'form_front' ),
		    		'config' => get_post_meta( $form_id, 'config' ),
		    		'other_setting' => get_post_meta( $form_id, 'other_setting' )
		    	);
		update_post_meta( $new_id, 'name', $data['name'][0] );
		update_post_meta( $new_id, 'attention', $data['attention'][0] );
		update_post_meta( $new_id, 'type', $data['type'][0] );
		update_post_meta( $new_id, 'validation', $data['validation'][0] );
		update_post_meta( $new_id, 'attr', $data['attr'][0] );
		update_post_meta( $new_id, 'admin_mail', $data['admin_mail'][0] );
		update_post_meta( $new_id, 'user_mail', $data['user_mail'][0] );
		update_post_meta( $new_id, 'form_admin_input', $data['form_admin_input'][0] );
		update_post_meta( $new_id, 'form_admin_confirm', $data['form_admin_confirm'][0] );
		update_post_meta( $new_id, 'form_admin_finish', $data['form_admin_finish'][0] );
		update_post_meta( $new_id, 'form_front', $data['form_front'][0] );
		update_post_meta( $new_id, 'config', $data['config'][0] );
		update_post_meta( $new_id, 'other_setting', $data['other_setting'][0] );
		return $new_id;
	 }
}
?>
