<?php
/**
 * Admin Panel: Meta data settings admin panel.
 *
 * @package WordPress_Ultimate_Toolkit
 * @subpackage admin
 */

/**
 * Meta info.
 *
 * An admin panel could be used to set meta info of the site.
 */
class WUT_Admin_Meta_Info extends WUT_Admin_Panel {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( __( 'Meta Info', 'wordpress-ultimate-toolkit' ), WUT_Option_Manager::SUBKEY_META_INFO );
		$this->id = 'metainfo';
	}

	/**
	 * Form table of admin panel.
	 *
	 * @param array $options The options array.
	 * @return void
	 */
	public function form( $options ) {
		$enabled          = isset( $options['enabled'] ) ? (bool) $options['enabled'] : false;
		$site_keywords    = isset( $options['site_keywords'] ) ? sanitize_text_field( $options['site_keywords'] ) : '';
		$site_description = isset( $options['site_description'] ) ? sanitize_text_field( $options['site_description'] ) : '';
		?>
		<table class="form-table" role="presentation"><tbody>
			<tr valign="top">
				<th scope="row"><label for="meta_info_enabled"><?php _e( 'Enable This Feature ', 'wordpress-ultimate-toolkit' ); ?></label></th>
				<td><input 
						id="<?php echo $this->get_field_id( 'meta_info_enabled' ); ?>"
						name="<?php echo $this->get_field_name( 'enabled' ); ?>"
						type="hidden"
						value="0"/>
					<input 
						id="<?php echo $this->get_field_id( 'meta_info_enabled' ); ?>"
						name="<?php echo $this->get_field_name( 'enabled' ); ?>"
						type="checkbox"
						value="1"<?php checked( $enabled ); ?>/></td></tr>
			<tr valign="top">
				<th scope="row"><label for="site_keywords"><?php _e( 'Site keywords', 'wordpress-ultimate-toolkit' ); ?></label></th>
				<td><input
					id="site_keywords"
					name="<?php echo $this->get_field_name( 'site_keywords' ); ?>"
					type="text" class="regular-text"
					value="<?php echo esc_attr( $site_keywords ); ?>"/></td></tr>
			<tr valign="top">
				<th scope="row"><label for="site_description"><?php _e( 'Site description', 'wordpress-ultimate-toolkit' ); ?></label></th>
				<td><input
					id="site_description"
					name="<?php echo $this->get_field_name( 'site_description' ); ?>"
					type="text" class="regular-text"
					value="<?php echo esc_attr( $site_description ); ?>"/></td></tr>
		</tbody></table>
		<?php
	}

	/**
	 * Filter the user submitted options.
	 *
	 * @param array $new_options User submitted options.
	 * @param array $old_options Original options.
	 * @return array
	 */
	public function update( $new_options, $old_options ) {
		return $new_options;
	}
}
