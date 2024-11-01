<?php
/**
 * Admin Panel: Options of excerption.
 *
 * @package WordPress_Ultimate_Toolkit
 * @subpackage admin
 */

/**
 * The class of auto excerption admin panel.
 */
class WUT_Admin_Excerption extends WUT_Admin_Panel {

	/**
	 * Create the Auto Excerption option page.
	 */
	public function __construct() {
		parent::__construct( __( 'Auto Excerption', 'wordpress-ultimate-toolkit' ), WUT_Option_Manager::SUBKEY_EXCERPTION );
		$this->id = 'admin-auto-excerption';
	}

	/**
	 * Print a form table of auto excerption featrue.
	 *
	 * @param array $options The options array of this feature.
	 * @return void
	 */
	public function form( $options ) {
		$enabled    = isset( $options['enabled'] ) ? (bool) $options['enabled'] : true;
		$paragraphs = isset( $options['paragraphs'] ) ? absint( $options['paragraphs'] ) : 3;
		$words      = isset( $options['words'] ) ? absint( $options['words'] ) : 250;
		?>
		<table class="form-table" role="presentation"><tbody>
			<tr valign="top">
				<th scope="row"><label for="excerpt_enabled"><?php _e( 'Enable This Feature ', 'wordpress-ultimate-toolkit' ); ?></label></th>
				<td><input 
						id="<?php echo $this->get_field_id( 'excerpt_enabled' ); ?>"
						name="<?php echo $this->get_field_name( 'enabled' ); ?>"
						type="hidden"
						value="0"/>
					<input 
						id="<?php echo $this->get_field_id( 'excerpt_enabled' ); ?>"
						name="<?php echo $this->get_field_name( 'enabled' ); ?>"
						type="checkbox"
						value="1"<?php checked( $enabled ); ?>/></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="excerpt_paragraphs_number"><?php _e( 'Paragraphs Number', 'wordpress-ultimate-toolkit' ); ?></label></th>
				<td><input 
					id="<?php echo $this->get_field_id( 'excerpt_paragraphs_number' ); ?>"
					name="<?php echo $this->get_field_name( 'paragraphs' ); ?>"
					type="text" size="10" 
					value="<?php echo $paragraphs; ?>"/></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="excerpt_words_number"><?php _e( 'Words Number', 'wordpress-ultimate-toolkit' ); ?></label></th>
				<td><input 
					id="<?php echo $this->get_field_id( 'excerpt_words_number' ); ?>"
					name="<?php echo $this->get_field_name( 'words' ); ?>"
					type="text" size="10" 
					value="<?php echo $words; ?>"/></td>
			</tr>
			<tr>
				<th scope="row"><label for="excerpt_continue_reading_tip_template"><?php _e( '"Continue Reading" tip template:', 'wordpress-ultimate-toolkit' ); ?></label></th>
				<td><fieldset>
					<p><textarea 
							id="<?php echo $this->get_field_id( 'excerpt_continue_reading_tip_template' ); ?>"
							name="<?php echo $this->get_field_name( 'tip_template' ); ?>"
							class="large-text code" 
							rows="3"><?php echo esc_attr( $options['tip_template'] ); ?></textarea></p>
					<p><?php _e( 'Use variables:', 'wordpress-ultimate-toolkit' ); ?></p>
					<ul>
						<li><code>%total_words%</code> --- <?php _e( 'The number of words in the post.', 'wordpress-ultimate-toolkit' ); ?></li>
						<li><code>%title%</code> --- <?php _e( 'Post title.', 'wordpress-ultimate-toolkit' ); ?></li>
						<li><code>%permalink%</code> --- <?php _e( 'The permanent link of the post.', 'wordpress-ultimate-toolkit' ); ?></li>
						<li><code><?php echo esc_html( '<br/>' ); ?></code> --- <?php _e( 'New line.', 'wordpress-ultimate-toolkit' ); ?></li>
					</ul>
					<p><?php _e( 'HTML tags supported.', 'wordpress-ultimate-toolkit' ); ?></p>
				</filedset></td>
			</tr>
		</tbody></table>
		<?php
	}

	/**
	 * Filter user submitted options value.
	 *
	 * @param array $new_options The new submitted options array.
	 * @param array $old_options The original options array.
	 * @return array
	 */
	public function update( $new_options, $old_options ) {
		return $new_options;
	}
}
