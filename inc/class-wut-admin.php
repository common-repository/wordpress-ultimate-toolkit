<?php
/**
 * Admin panel of this plugin.
 *
 * @package WordPress_Ultimate_Toolkit
 * @subpackage admin
 */

/**
 * Admin pages of the WordPress Ultimate Toolkit.
 *
 * WordPress Ultimate Toolkit has its own top level menu, because of its huge
 * amount of functionalities.
 *
 * The top level menu called WUT Opitons, and other submenu items are all under
 * this menu.
 */
class WUT_Admin {

	/**
	 * The Constructor.
	 */
	public function __construct() {
	}

	/**
	 * Option tabs
	 *
	 * @var WUT_Admin_Panel[]
	 */
	protected $tabs = array();

	const MENU_SLUG = 'wut-options-page';
	/**
	 * Add an entry point of admin panel of this plugin to WordPress admin area,
	 * or add admin only features to WordPress admin area.
	 *
	 * @return void
	 */
	public function register_admin_entry() {
		add_action(
			'admin_menu',
			function() {
				add_options_page(
					__( 'WordPress Ultimate Toolkit', 'wordpress-ultimate-toolkit' ),
					__( 'WP Ultimate Toolkit', 'wordpress-ultimate-toolkit' ),
					'activate_plugins',
					self::MENU_SLUG,
					array( $this, 'options_page' )
				);
			}
		);
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueueu_scripts' ) );
		$this->register_options_tabs();
	}

	/**
	 * Enqueue scripts to admin page.
	 *
	 * @return void
	 */
	public function admin_enqueueu_scripts() {
		wp_enqueue_script( 'jquery-ui-tabs' );
	}

	/**
	 * Register option tabs to option page.
	 * The whole options page is designed a single page, every part
	 * of options will be managed by tab.
	 *
	 * @return void
	 */
	public function register_options_tabs() {
		$this->tabs[] = new WUT_Admin_Excerption();
		$this->tabs[] = new WUT_Admin_Related_List();
		$this->tabs[] = new WUT_Admin_Custom_Code();
		$this->tabs[] = new WUT_Admin_Meta_Info();
		$this->tabs[] = new WUT_Admin_About();
	}

	/**
	 * Print navigation tabs.
	 *
	 * @return void
	 */
	public function print_tab_nav() {
		$html = '';
		foreach ( $this->tabs as $idx => $tab ) {
			$html .= '<li data-id="' . $idx . '"><a href="' . $tab->get_tab_anchor() . '" class="nav-tab">';
			$html .= $tab->title;
			$html .= '</a></li>' . PHP_EOL;
		}
		?>
		<h2 class="nav-tab-wrapper"><ul>
			<?php echo $html; ?>
		</ul></h2>
		<?php
	}

	/**
	 * Print tab panels.
	 *
	 * @return void
	 */
	public function print_tab_panels() {
		$html = '';
		foreach ( $this->tabs as $tab ) {
			?>
			<div id="<?php echo $tab->id; ?>">
				<?php $tab->print_form_table(); ?>
			</div>
			<?php
		}
	}

	/**
	 * Process user submitted form data.
	 * This method will dispatch data to each option panel object.
	 *
	 * @return string
	 */
	public function process_submit_and_save() {
		$ret = true;
		$msg = '';
		if ( isset( $_POST['action'] )
			&& 'update' === $_POST['action'] ) {

			if ( isset( $_POST['_wpnonce'] )
				&& wp_verify_nonce( $_POST['_wpnonce'] ) ) {
				foreach ( $this->tabs as $tab ) {
					$tab->process_submit();
				}
				$ret = WUT_Option_Manager::me()->save_options();
				if ( ! $ret ) {
					$msg = __( 'Update failed. Options are not changed or database error occured.', 'wordpress-ultimate-toolkit' );
				} else {
					$msg = __( 'Options saved.', 'wordpress-ultimate-toolkit' );
				}
			} else {
				$ret = false;
				$msg = __( 'Nonce verify failed.', 'wordpress-ultimate-toolkit' );
			}
		}
		return array( $ret, $msg );
	}

	/**
	 * Show a dismissible notice or indismissible error message.
	 *
	 * @param string  $message The message content.
	 * @param boolean $notice Show notice or error.
	 * @return void
	 */
	public function print_message( $message, $notice = true ) {
		if ( $notice ) {
			$class = 'notice';
		} else {
			$class = 'error';
		}
		if ( ! empty( $message ) ) {
			?>
			<div id="message" class="updated <?php echo $class; ?> is-dismissible">
				<p><?php echo $message; ?></p>
			</div>
			<?php
		}
	}

	/**
	 * All WUT options will be set on this page, and seperated by tabs.
	 *
	 * @return void
	 */
	public function options_page() {
		list( $ret, $msg) = $this->process_submit_and_save();
		?>
		<div class="wrap wut-tabs">
			<h1><?php echo __( 'WordPress Ultimate Toolkit: Options', 'wordpress-ultimate-toolkit' ); ?></h1>
			<hr class="wp-header-end" />
			<?php $this->print_message( $msg, $ret ); ?>
			<?php $this->print_tab_nav(); ?>
			<form method="post">
				<input type="hidden" name="action" value="update">
				<?php wp_nonce_field(); ?>
				<?php $this->print_tab_panels(); ?>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes' ); ?>">
				</p>
			</form>
		</div>
		<script>
			(function($) {
				$(function(){
					var active_num = wpCookies.get('wut_active_tab');
					if ( typeof active_num == 'undefined' || null == active_num ) {
						active_num = 0;
					} 
					$('.wut-tabs').tabs({
						active: active_num,
						show: { effect: "fadeIn", duration: 300 },
						activate: function( event, ui ) {
							$(ui.newTab).find('a').addClass('nav-tab-active');
							// This :focus pseudo class of <a> element is ridiculous.
							// I have to remove box-shadow style mannually.
							$(ui.newTab).find('a').css('box-shadow', 'none');
							$(ui.oldTab).find('a').removeClass('nav-tab-active');
							wpCookies.set('wut_active_tab', $(ui.newTab).data('id'), 86400 * 30, '/wp-admin');
						}
					});
					$('a.nav-tab:eq(' + active_num + ')').addClass('nav-tab-active');
				});
			})(jQuery);
		</script>
		<?php
	}

}
