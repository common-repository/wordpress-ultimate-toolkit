<?php
/**
 * Component: Category Chooser
 *
 * @package WordPress_Ultimate_Toolkit
 * @subpackage admin
 */

/**
 * Category Chooser is a component implementation.
 *
 * This class is a child class of Walker which is used for
 * displaying a category tree to help people finding categories
 * he or she want include or exclude from.
 */
class WUT_Category_Chooser extends Walker {
	const TREE_BOX_ID = 'jstree_category';

	/**
	 * The static files dependency.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		add_thickbox();
		wp_enqueue_style( 'jstree' );
		wp_enqueue_script( 'jstree' );
		wp_enqueue_style( 'wut-category-chooser' );
		wp_enqueue_script( 'wut-category-chooser' );
	}

	/**
	 * Call this will display a link, which will display a panel with category tree.
	 *
	 * @return void
	 */
	public function inject_link( $field_id ) {
		?>
		<a
			title="<?php esc_attr_e( 'Category Chooser', 'wordpress-ultimate-toolkit' ); ?>"
			href="admin-ajax.php?action=wut_category_chooser&height=300&width=300"
			data-target-field="<?php echo esc_attr( $field_id ); ?>"
			class="thickbox category-chooser-link"><?php esc_html_e( 'Choose categories', 'wordpress-ultimate-toolkit' ); ?></a>
		<?php
	}

	public static function show_category_tree() {
		?>
		<script>
			var wut_tree_data = [
				<?php
					$categories        = get_categories();
					$walker            = new WUT_Category_Chooser();
					$walker->db_fields = array(
						'id'     => 'term_id',
						'parent' => 'parent',
					);
					echo $walker->walk( $categories, 0 );
					?>
			];
			wut_category_chooser();
		</script>
		<p><?php echo __( 'Choose the categories:', 'wordpress-ultimate-toolkit' ); ?></p>
		<div id="<?php echo self::TREE_BOX_ID; ?>" role="tree" class="category-chooser"></div>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Decide', 'wordpress-ultimate-toolkit' ); ?>"></p
		<?php
		wp_die();
	}

	// The walker implementation.

	/**
	 * Starts the list before the elements are added.
	 *
	 * The $args parameter holds additional values that may be used with the child
	 * class methods. This method is called at the start of the output list.
	 *
	 * @since 2.1.0
	 * @abstract
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param int    $depth  Depth of the item.
	 * @param array  $args   An array of additional arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= '"children": [';
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * The $args parameter holds additional values that may be used with the child
	 * class methods. This method finishes the list at the end of output of the elements.
	 *
	 * @since 2.1.0
	 * @abstract
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param int    $depth  Depth of the item.
	 * @param array  $args   An array of additional arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= ']';
	}

	/**
	 * Start the element output.
	 *
	 * The $args parameter holds additional values that may be used with the child
	 * class methods. Includes the element output also.
	 *
	 * @since 2.1.0
	 * @abstract
	 *
	 * @param string $output            Used to append additional content (passed by reference).
	 * @param object $object            The data object.
	 * @param int    $depth             Depth of the item.
	 * @param array  $args              An array of additional arguments.
	 * @param int    $current_object_id ID of the current item.
	 */
	public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
		$output .= '{';
		$output .= '"id": ' . $object->term_id . ',';
		$output .= '"text": "' . $object->name . '",';
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * The $args parameter holds additional values that may be used with the child class methods.
	 *
	 * @since 2.1.0
	 * @abstract
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param object $object The data object.
	 * @param int    $depth  Depth of the item.
	 * @param array  $args   An array of additional arguments.
	 */
	public function end_el( &$output, $object, $depth = 0, $args = array() ) {
		$output .= '},';
	}


}
