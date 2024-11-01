<?php
/**
 * Admin panel: Custom code panel.
 *
 * @package WordPress_Ultimate_Toolkit
 * @subpackage admin
 */

/**
 * Create the custom code panel.
 */
class WUT_Admin_Custom_Code extends WUT_Admin_Panel {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( __( 'Custom Code', 'wordpress-ultimate-toolkit' ), WUT_Option_Manager::SUBKEY_CUSTOM_CODE );
		$this->id = 'admin-custom-code';

		// register an ajax form table.
		add_action( 'wp_ajax_wut_custom_code', array( $this, 'print_custom_form' ) );
		add_action( 'wp_ajax_wut_custom_code_submit', array( $this, 'process_submit' ) );
		add_action( 'wp_ajax_wut_custom_code_delete', array( $this, 'process_delete' ) );

		// add thickbox and its behaviors.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * The styles and scripts dependent by this admin panel.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( false !== strpos( $screen->base, WUT_Admin::MENU_SLUG ) ) { // TODO: !!! This is too ugly.
			add_thickbox();
			ob_start();
			$this->javascript();
			$js = ob_get_contents();
			ob_end_clean();
			$js  = trim( preg_replace( '#<script[^>]*>(.*)</script>#is', '$1', $js ) );
			$ret = wp_add_inline_script( 'thickbox', $js );
		}
	}

	/**
	 * This is used to make it compatible with old version of options' format.
	 *
	 * @param array $options The old format options.
	 * @return array
	 */
	public function transfer( $options ) {
		$key = '';
		foreach ( $options as $k => $v ) {
			$key = $k;
			break;
		}
		if ( ! empty( $options ) && 0 !== strpos( $key, 'wut_' ) ) {
			$new_options = array();
			foreach ( $options as $id => $snippet ) {
				$new_struct['title']                   = $snippet['name'];
				$new_struct['source']                  = $snippet['source'];
				$new_struct['priority']                = $snippet['priority'];
				$new_struct['date_time']               = date( 'Y-m-d H:i:s' );
				$new_struct['remark']                  = '';
				$new_struct['code_id']                 = uniqid( 'wut_' );
				$new_struct['hook']                    = $snippet['hookto'];
				$new_options[ $new_struct['code_id'] ] = $new_struct;
			}
			$manager = WUT_Option_Manager::me();
			$manager->set_options_by_key( $this->option_name, $new_options );
			$manager->save_options();
			return $new_options;
		}
		return $options;
	}

	/**
	 * This method must be implemented.
	 * Print the form of option panel.
	 *
	 * @param array $options Retrieved options array.
	 * @return void
	 */
	public function form( $options ) {
		$options = $this->transfer( $options );
		$length  = count( $options );
		?>
		<div class="tablenav top">
			<a class="button thickbox" href="admin-ajax.php?action=wut_custom_code&KeepThis=true&TB_iframe=true&height=400&width=600"><?php _e( '+ Add New', 'wordpress-ultimate-toolkit' ); ?></a>
		</div>
		<table class="wp-list-table widefat fixed striped table-view-list">
			<thead>
				<tr>
					<td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox"/></td>
					<th scope="col" id="title" class="manage-column column-title column-primary"><?php _e( 'Title', 'wordpress-ultimate-toolkit' ); ?></th>
					<th scope="col" id="remark" class="manage-column column-remark"><?php _e( 'Remark', 'wordpress-ultimate-toolkit' ); ?></th>
					<th scope="col" id="hook" class="manage-column column-hook"><?php _e( 'Hook', 'wordpress-ultimate-toolkit' ); ?></th>
					<th scope="col" id="priority" class="manage-column column-priority"><?php _e( 'Priority', 'wordpress-ultimate-toolkit' ); ?></th>
					<th scope="col" id="date" class="manage-column column-date"><?php _e( 'Date', 'wordpress-ultimate-toolkit' ); ?></th>
					<th scope="col" id="action" class="manage-column column-action"><?php _e( 'Actions', 'wordpress-ultimate-toolkit' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $options as $id => $snippet ) : ?>
			<tr>
				<th scope="row" class="check-column"><input type="checkbox" name="code_id" value="<?php echo $id; ?>"/></th>
				<td scope="col"><?php echo $snippet['title']; ?></td>
				<td scope="col"><?php echo $snippet['remark']; ?></td>
				<td scope="col"><?php echo $snippet['hook']; ?></td>
				<td scope="col"><?php echo $snippet['priority']; ?></td>
				<td scope="col"><?php echo $snippet['date_time']; ?></td>
				<td scope="col">
					<a class="thickbox" href="admin-ajax.php?action=wut_custom_code&edit=1&id=<?php echo $id; ?>&KeepThis=true&TB_iframe=true"><?php _e( 'Edit', 'wordpress-ultimate-toolkit' ); ?></a>
					<a class="delete" style="color:red;" href="admin-ajax.php?action=wut_custom_code_delete&delete=1&id=<?php echo $id; ?>"><?php _e( 'Delete', 'wordpress-ultimate-toolkit' ); ?></a>
				</td>
			</tr>
			<?php endforeach; ?>
			</tbody>
			<?php if ( $length > 10 ) : ?>
			<tfoot>
				<tr>
					<td class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox"/></td>
					<th scope="col" class="manage-column column-title column-primary"><?php _e( 'Title', 'wordpress-ultimate-toolkit' ); ?></th>
					<th scope="col" class="manage-column column-remark"><?php _e( 'Remark', 'wordpress-ultimate-toolkit' ); ?></th>
					<th scope="col" class="manage-column column-hook"><?php _e( 'Hook', 'wordpress-ultimate-toolkit' ); ?></th>
					<th scope="col" class="manage-column column-date"><?php _e( 'Date', 'wordpress-ultimate-toolkit' ); ?></th>
					<th scope="col" class="manage-column column-action"><?php _e( 'Actions', 'wordpress-ultimate-toolkit' ); ?></th>
				</tr>
			</tfoot>
			<?php endif; ?>
		</table>
		<?php
	}

	/**
	 * Filter and sanitize user submitted options.
	 *
	 * @param array $new_options New options submitted.
	 * @param array $old_options Old options.
	 * @return array
	 */
	public function update( $new_options, $old_options ) {
		// nothing summitted.
		if ( empty( $new_options ) ) {
			return $old_options;
		}

		$snippet  = array();
		$validate = true;
		$error    = array();
		$id       = '';
		if ( isset( $new_options['code_id'] ) ) {
			$id                 = sanitize_text_field( $new_options['code_id'] );
			$snippet['code_id'] = $id;
			if ( empty( $new_options['title'] ) ) {
				$validate       = false;
				$error['title'] = __( 'Title should not be empty.', 'wordpress-ultimate-toolkit' );
			} else {
				$snippet['title'] = sanitize_text_field( $new_options['title'] );
			}

			if ( ! empty( $new_options['remark'] ) ) {
				$snippet['remark'] = sanitize_text_field( $new_options['remark'] );
			}

			if ( empty( $new_options['hook'] ) || 'wp_head' === $new_options['hook'] ) {
				$snippet['hook'] = 'wp_head';
			} else {
				$snippet['hook'] = 'wp_footer';
			}

			if ( empty( $new_options['source'] ) ) {
				$validate        = false;
				$error['source'] = __( 'Source code should not be empty.', 'wordpress-ultimate-toolkit' );
			} else {
				if ( false === stripos( $new_options['source'], '</script>' ) && false === stripos( $new_options['source'], '</style>' ) ) {
					$validate        = false;
					$error['source'] = __( 'Source code should be enclosed by &lt;script&gt; or &lt;style&gt; tag.', 'wordpress-ultimate-toolkit' );
				} else {
					$snippet['source'] = $new_options['source'];
				}
			}

			if ( empty( $new_options['priority'] ) || 0 === absint( $new_options['priority'] ) ) {
				$validate          = false;
				$error['priority'] = __( 'Priority should not be empty.', 'wordpress-ultimate-toolkit' );
			} else {
				$snippet['priority'] = absint( $new_options['priority'] );
			}

			$snippet['date_time'] = date( 'Y-m-d H:i:s', strtotime( $new_options['date_time'] ) );
		} else {
			$validate = false;
		}

		if ( $validate ) {
			$old_options[ $id ] = $snippet;
			$manager            = WUT_Option_Manager::me();
			$manager->set_options_by_key( $this->option_name, $old_options );
			$manager->save_options();
		} else {
			$snippet = $new_options;
		}

		$this->print_custom_form( $snippet, $validate, $error );
	}

	/**
	 * Dependency javascript code.
	 *
	 * @return void
	 */
	public function javascript() {
		?>
		<script>
		jQuery( document ).ready( function( $ ){
			var tbWindow;

			window.tb_position = function() {
				var width = $( window ).width(),
					H = $( window ).height() - ( ( 792 < width ) ? 89 : 49 ),
					W = ( 792 < width ) ? 772 : width - 20;

				tbWindow = $( '#TB_window' );

				if ( tbWindow.length ) {
					tbWindow.width( W ).height( H );
					$( '#TB_iframeContent' ).width( W ).height( H );
					tbWindow.css({
						'margin-left': '-' + parseInt( ( W / 2 ), 10 ) + 'px'
					});
					if ( typeof document.body.style.maxWidth !== 'undefined' ) {
						tbWindow.css({
							'top': '30px',
							'margin-top': '0'
						});
					}
				}

				return $( 'a.thickbox' ).each( function() {
					var href = $( this ).attr( 'href' );
					if ( ! href ) {
						return;
					}
					href = href.replace( /&width=[0-9]+/g, '' );
					href = href.replace( /&height=[0-9]+/g, '' );
					$(this).attr( 'href', href + '&width=' + W + '&height=' + ( H ) );
				});
			};

			$( window ).resize( function() {
				tb_position();
			});

			$( 'a.delete' ).click(function($e) {
				$e.preventDefault();
				$.ajax( $(this).attr('href'), {
					method: 'POST',
					success: function( data, status, xhr) {
						console.log(data);
						document.location.reload();
					}
				} );
			});
		} );
		</script>
		<?php
	}

	/**
	 * After user submitted a form table or clicked EDIT link,
	 * a thickbox wrapped form table will show up. This method
	 * is used to fetch the options value used by that form table.
	 *
	 * @return array
	 */
	public function process_edit() {
		if ( isset( $_GET['edit'] ) && 1 === absint( $_GET['edit'] ) ) {
			$id      = sanitize_text_field( $_GET['id'] );
			$manager = WUT_Option_Manager::me();
			$options = $manager->get_options_by_key( $this->option_name );
			return $options[ $id ];
		}
		return array();
	}

	/**
	 * This method is used to process the delete action.
	 *
	 * @return void
	 */
	public function process_delete() {
		if ( isset( $_GET['delete'] ) && 1 === absint( $_GET['delete'] ) ) {
			$id      = sanitize_text_field( $_GET['id'] );
			$manager = WUT_Option_Manager::me();
			$options = $manager->get_options_by_key( $this->option_name );
			if ( ! isset( $options[ $id ] ) ) {
				echo wp_json_encode( array( 'ret' => true ) );
				wp_die();
			}
			unset( $options[ $id ] );
			$manager->set_options_by_key( $this->option_name, $options );
			$ret = $manager->save_options();
			if ( ! $ret ) {
				echo wp_json_encode( array( 'ret' => false ) );
				wp_die();
			}
		}
		echo wp_json_encode( array( 'ret' => true ) );
		wp_die();
	}

	/**
	 * This is an dialog page to display a empty form table.
	 *
	 * @param array   $options Options submitted.
	 * @param boolean $valid Sumitted data is valid or not.
	 * @param array   $error Error messages.
	 * @return void
	 */
	public function print_custom_form( $options = array(), $valid = false, $error = array() ) {
		$is_error = false;
		$is_new   = true;
		if ( ! empty( $options ) ) {
			if ( ! $valid ) {
				$is_error = true;
			}
		} else {
			$options = $this->process_edit();
		}
		if ( ! empty( $options ) ) {
			$is_new = false;
		}
		$title     = isset( $options['title'] ) ? sanitize_text_field( $options['title'] ) : '';
		$remark    = isset( $options['remark'] ) ? sanitize_text_field( $options['remark'] ) : '';
		$priority  = isset( $options['priority'] ) ? absint( $options['priority'] ) : 9;
		$source    = isset( $options['source'] ) ? $options['source'] : '';
		$code_id   = isset( $options['code_id'] ) ? sanitize_text_field( $options['code_id'] ) : uniqid( 'wut_' );
		$date_time = isset( $options['date_time'] ) ? sanitize_text_field( $options['date_time'] ) : date( 'Y-m-d H:i:s' );
		wp_enqueue_style( 'colors' );
		?>
		<html>
		<head>
			<?php wp_print_styles(); ?>
			<style>.error { box-shadow: 0 0 4px #f00;}</style>
			<?php if ( ! empty( $options ) && $valid ) : ?>
			<script>window.parent.tb_remove();window.parent.document.location.reload();</script>
			<?php endif; ?>
		</head>
		<body class="wp-core-ui"><div style="padding-left:20px;"><div class="wpbody"><div class="wpbody-content"><div class="wrap">
			<?php if ( $is_new ) : ?>
			<h1>Create New Custom Code</h1>
			<?php else : ?>
			<h1>Edit Custom Code</h1>
			<?php endif; ?>
			<hr class="wp-header-end"/>
			<?php if ( $is_error ) : ?>
				<div id="message" class="updated error is-dismissible">
					<p><?php echo __( 'Some error occured.', 'wordpress-ultimate-toolkit' ); ?></p>
				</div>
			<?php endif; ?>
			<form method="post" action="admin-ajax.php?action=wut_custom_code_submit">
				<input type="hidden"
					name="<?php echo $this->get_field_name( 'code_id' ); ?>"
					value="<?php echo $code_id; ?>"/>
				<input type="hidden"
					name="<?php echo $this->get_field_name( 'date_time' ); ?>"
					value="<?php echo $date_time; ?>"/>
				<table class="form-table"><tbody>
				<tr>
					<th scope="row"><label for="custom-code-title"> Title: </label></th>
					<td><input 
							name="<?php echo $this->get_field_name( 'title' ); ?>" 
							type="text" id="custom-code-title" 
							value="<?php echo $title; ?>"
							class="regular-text <?php echo isset( $error['title'] ) ? 'error' : ''; ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="custom-code-remark"> Remark: </label></th>
					<td><input 
							name="<?php echo $this->get_field_name( 'remark' ); ?>"
							type="text" id="custom-code-remark"
							value="<?php echo $remark; ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="custom-code-hook"> Hook Position: </label></th>
					<td><select name="<?php echo $this->get_field_name( 'hook' ); ?>"
							type="text" id="custom-code-hook">
							<option value="wp_head" selected="selected">Inject to header part.(wp_head)</option>
							<option value="wp_footer">Inject to footer part.(wp_footer)</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="custom-code-priority"> Priority: </label></th>
					<td><input 
							name="<?php echo $this->get_field_name( 'priority' ); ?>"
							type="text" id="custom-code-priority"
							value="<?php echo $priority; ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="custom-code-source"> Source Code: </label></th>
					<td><textarea
							name="<?php echo $this->get_field_name( 'source' ); ?>" 
							rows="8" class="<?php echo isset( $error['source'] ) ? 'error' : ''; ?>"
							type="text" id="custom-code-source"><?php echo $source; ?></textarea></td>
				</tr>
				</tbody></table>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save', 'wordpress-ultimate-toolkit' ); ?>"></p>
			</form>
		</div></div></div></div></body>
		</html>
		<?php
		wp_die();
	}
}
