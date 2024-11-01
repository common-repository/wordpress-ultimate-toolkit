<?php
/**
 * The form helper class to generate the widget form easily.
 *
 * @package WordPress_Ultimate_Toolkit
 * @subpackage admin
 */

/**
 * The form helper.
 */
class WUT_Form_Helper {

	/**
	 * The reference of WP_Widget object.
	 *
	 * @var WP_Widget The widget reference
	 */
	public $widget;

	/**
	 * The Constructor
	 *
	 * @param WP_Widget $widget the widget reference.
	 */
	public function __construct( $widget ) {
		$this->widget = $widget;
	}

	/**
	 * Print a checkbox form control on page.
	 *
	 * @param string $property The property name.
	 * @param bool   $value The value of the property.
	 * @param string $label The label tip of the checkbox.
	 */
	public function checkbox( $property, $value, $label ) {
		WUT_Html_Helper::open_tag( 'p' );
			$id = $this->widget->get_field_id( $property );
			WUT_Html_Helper::checkbox(
				$id,
				$this->widget->get_field_name( $property ),
				$value
			);
			WUT_Html_Helper::label( $id, $label );
		WUT_Html_Helper::close_tag();
	}

	/**
	 * Print a widefat text input control on page.
	 *
	 * @param string $property The property name.
	 * @param string $value The value of the property.
	 * @param string $label The label tip of the checkbox.
	 * @param string $type The type of input control.
	 * @param string $class The class of input control.
	 */
	public function text( $property, $value, $label, $type = 'text', $class = 'widefat' ) {
		WUT_Html_Helper::open_tag( 'p' );
			$id = $this->widget->get_field_id( $property );
			WUT_Html_Helper::label( $id, $label );
			WUT_Html_Helper::input(
				'text',
				$id,
				$this->widget->get_field_name( $property ),
				$value,
				array( 'class' => $class )
			);
		WUT_Html_Helper::close_tag();
	}

	/**
	 * Check the value type of the sepcific key and give it a default value if not set.
	 *
	 * @param array  $haystack The haystack which contains the key.
	 * @param string $key The key to check.
	 * @param string $type Could be `int`, `uint`, `string`, `bool`.
	 * @param mixed  $default The default value of the key.
	 * @param bool   $allow_empty Dose the value allow empty.
	 * @return mixed The sanitized value or default value if not set.
	 */
	public function default( $haystack, $key, $type, $default, $allow_empty = true ) {
		if ( isset( $haystack[ $key ] ) ) {
			switch ( $type ) {
				case 'string':
					$value = sanitize_text_field( $haystack[ $key ] );
					return ( ! $allow_empty && empty( $value ) ) ? $default : $value;
				case 'int':
					return intval( $haystack[ $key ] );
				case 'uint':
					return absint( $haystack[ $key ] );
				case 'bool':
					return (bool) $haystack[ $key ];
				default:
					return $haystack[ $key ];
			}
		}
		return $default;
	}

	/**
	 * This will print a full date format choose control group.
	 *
	 * $config should be like:
	 * array(
	 *      'date_format_property'   => 'date_format',
	 *      'date_format_value'      => $date_format,
	 *      'date_format_default'    => $site_date_format,
	 *      'custom_format_property' => 'custom_format',
	 *      'custom_format_value'    => $custom_format,
	 * )
	 *
	 * @param array $config The properties and values of this control group.
	 */
	public function date_format_chooser( $config ) {
		WUT_Html_Helper::open_tag( 'p' );
			WUT_Html_Helper::tag( 'span', __( 'Date format:', 'wordpress-ultimate-toolkit' ) );
			WUT_Html_Helper::tag( 'br', '', array(), true );

			$name = $this->widget->get_field_name( $config['date_format_property'] );
			$this->date_format_option( $name, $config['date_format_value'], $config['date_format_default'] );
			$this->date_format_option( $name, $config['date_format_value'], 'M d' );
			$this->date_format_option( $name, $config['date_format_value'], 'd F y' );
			$this->date_format_option(
				$name,
				$config['date_format_value'],
				'custom',
				$config['custom_format_property'],
				$config['custom_format_value']
			);
			WUT_Html_Helper::e( __( 'Preview: ', 'wordpress-ultimate-toolkit' ) );
			WUT_Html_Helper::tag(
				'span',
				'custom' === $config['date_format_value'] ? date_i18n( $config['custom_format_value'] ) : date_i18n( $config['date_format_value'] )
			);
		WUT_Html_Helper::close_tag();
	}

	/**
	 * Print each option of date_format chooser.
	 *
	 * @param string $name The name of the control.
	 * @param string $value The value of the option.
	 * @param string $option The option to choose.
	 * @param string $custom_property The custom option name.
	 * @param string $custom_value  The custom option value.
	 * @return void
	 */
	private function date_format_option( $name, $value, $option, $custom_property = '', $custom_value = '' ) {
		WUT_Html_Helper::open_tag( 'label' );
		WUT_Html_Helper::radio( $name, $option, $option === $value );
		if ( 'custom' === $option ) {
			WUT_Html_Helper::tag(
				'span',
				__( 'Custom', 'wordpress-ultimate-toolkit' ),
				array( 'style' => 'display:inline-block;min-width:10em;' )
			);
			WUT_Html_Helper::input(
				'text',
				$this->widget->get_field_id( $custom_property ),
				$this->widget->get_field_name( $custom_property ),
				$custom_value,
				array(
					'step'  => 1,
					'min'   => 1,
					'size'  => 6,
					'class' => 'medium-text',
				)
			);
		} else {
			WUT_Html_Helper::tag(
				'span',
				date_i18n( $option ),
				array( 'style' => 'display:inline-block;min-width:10em;' )
			);
			WUT_Html_Helper::tag( 'code', $option );
		}
		WUT_Html_Helper::close_tag();
		WUT_Html_Helper::tag( 'br', '', array(), true );
	}

	/**
	 * This helper is used to print a widget content.
	 *
	 * @param array  $args Site widget configurations.
	 * @param string $title Widget title.
	 * @param string $content Widget content.
	 * @return void
	 */
	public function print_widget( $args, $title, $content ) {
		$html = $args['before_widget'];
		if ( $title ) {
			$html .= $args['before_title'] . $title . $args['after_title'];
		}
		$html .= $content . $args['after_widget'];
		WUT_Html_Helper::e( $html );
	}

}
