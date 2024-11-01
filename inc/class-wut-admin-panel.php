<?php
/**
 * Admin Panel: Abstract class
 *
 * @package WordPress_Ultimate_Toolkit
 * @subpackage admin
 */

/**
 * This is the abstract class of admin panel.
 *
 * The design of this abstract class is inspired by the wp-class-widget.php
 * It provides a template for developer to create an option panel tab.
 * Therefor the admin part of a plugin could be created easily.
 */
abstract class WUT_Admin_Panel {

	/**
	 * The title on tab of option page of WUT.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * The id base of this tab page.
	 * This is used to generate the form control.
	 * This must be set in child class.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * The option key which is used to retrieve
	 * options from Option Manager.
	 *
	 * @var string
	 */
	public $option_name;

	/**
	 * Constructor to create a new option panel tab.
	 * The title of this tab and option key should be
	 * specified.
	 *
	 * @param string $title The title of option panel tab.
	 * @param string $option_name The option key of options in this panel. If not provided, it will be generated from title.
	 */
	public function __construct( $title, $option_name ) {
		$this->title       = $title;
		$this->option_name = $option_name;
	}

	/**
	 * This method must be implemented.
	 * Print the form of option panel.
	 *
	 * @param array $options Retrieved options array.
	 * @return void
	 */
	abstract public function form( $options );

	/**
	 * Filter and sanitize user submitted options.
	 *
	 * @param array $new_options New options submitted.
	 * @param array $old_options Old options.
	 * @return array
	 */
	abstract public function update( $new_options, $old_options );

	/**
	 * Print the form table, this method will be called in
	 * admin object.
	 *
	 * @return void
	 */
	public function print_form_table() {
		$options = WUT_Option_Manager::me()->get_options_by_key( $this->option_name );
		$this->form( $options );
	}

	/**
	 * This is used to generate the anchor of this tab. The usage
	 * of it should reference of jQuery UI tabs.
	 *
	 * @return string
	 */
	public function get_tab_anchor() {
		return '#' . $this->id;
	}

	/**
	 * This is used to generate id attribute of a form control.
	 *
	 * @param string $field The field name of a form control.
	 * @return string
	 */
	public function get_field_id( $field ) {
		return $this->id . '[' . $field . ']';
	}

	/**
	 * This is used in form page to generate name attribute of a form control.
	 *
	 * @param string $field The field name of a form control.
	 * @return string
	 */
	public function get_field_name( $field ) {
		return $this->id . '[' . $field . ']';
	}

	/**
	 * This will call method update() to filter and sanitize
	 * user sumitted form data for security reasons.
	 *
	 * @return void
	 */
	public function process_submit() {
		$new_options = $this->retrieve_submit();
		$manager     = WUT_Option_Manager::me();
		$processed   = $this->update( $new_options, $manager->get_options_by_key( $this->option_name ) );
		$manager->set_options_by_key( $this->option_name, $processed );
	}

	/**
	 * Retrieve submitted form data.
	 *
	 * @return array
	 */
	public function retrieve_submit() {
		return isset( $_POST[ $this->id ] ) ? wp_unslash( $_POST[ $this->id ] ) : array();
	}
}
