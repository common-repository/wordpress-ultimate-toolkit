<?php
/**
 * Helper class: Html helper.
 *
 * @package WordPress_Ultimate_Toolkit
 * @subpackage admin
 */

/**
 * This class will generate html code, especially form control.
 */
class WUT_Html_Helper {

	/**
	 * Print out the html code.
	 *
	 * @param string $html Html code to be printed.
	 * @return void
	 */
	public static function e( $html ) {
		echo $html;
	}

	/**
	 * General html tag print method.
	 *
	 * @param string  $tag_name Html tag name.
	 * @param string  $inner_html Inner html string of html tag.
	 * @param array   $properties Html tag properties config.
	 * @param boolean $unary If this tag is unary.
	 * @return void
	 */
	public static function tag( $tag_name, $inner_html = '', $properties = array(), $unary = false ) {
		$html = '<' . $tag_name . self::parse_properties( $properties );
		if ( $unary ) {
			$html .= '/>';
		} else {
			$html .= '>';
			if ( ! empty( $inner_html ) ) {
				$html .= $inner_html;
			}
			$html .= '</' . $tag_name . '>';
		}

		self::e( $html );
	}

	/**
	 * Parse properties array to html attributes string.
	 *
	 * @param array $properties The html tag properties array.
	 * @return string
	 */
	public static function parse_properties( $properties = array() ) {
		$html = '';
		foreach ( $properties as $property => $value ) {
			$html .= ' ' . $property . '="';
			if ( is_array( $value ) ) {
				$html .= implode( ' ', esc_attr( $value ) );
			} else {
				$html .= esc_attr( (string) $value );
			}
			$html .= '"';
		}
		return $html;
	}

	/**
	 * The stack to record which html tag is open.
	 *
	 * @var array
	 */
	public static $stack = array();

	/**
	 * Print first half of a html tag.
	 *
	 * @param string $tag_name The tag name.
	 * @param array  $properties The same with method self::tag().
	 * @return void
	 */
	public static function open_tag( $tag_name, $properties = array() ) {
		array_push( self::$stack, $tag_name );
		self::e( '<' . $tag_name . self::parse_properties( $properties ) . '>' );
	}

	/**
	 * Close last openned tag.
	 *
	 * @return void
	 */
	public static function close_tag() {
		$tag_name = array_pop( self::$stack );
		self::e( '</' . $tag_name . '>' );
	}

	/**
	 * Print a label of a form control.
	 *
	 * @param string $for Id property of label tag.
	 * @param string $caption The label content.
	 * @param array  $properties The other properties config of the label.
	 * @return void
	 */
	public static function label( $for, $caption = '', $properties = array() ) {
		$properties['for'] = $for;
		self::tag( 'label', $caption, $properties );
	}

	/**
	 * General html form control tag print method.
	 *
	 * @param string $type The type of the input tag.
	 * @param string $id The id of the input tag.
	 * @param string $name The name of the input tag.
	 * @param string $value The value of the input tag.
	 * @param array  $properties Other config info of the input tag.
	 * @return void
	 */
	public static function input( $type, $id, $name, $value, $properties = array() ) {
		$properties['type'] = $type;
		if ( ! empty( $id ) ) {
			$properties['id'] = $id;
		}
		$properties['name']  = $name;
		$properties['value'] = $value;
		self::tag( 'input', '', $properties, true );
	}

	/**
	 * Print a checkbox.
	 *
	 * @param string  $id The id of the checkbox.
	 * @param string  $name The name of the checkbox.
	 * @param boolean $checked The current state of the checkbox.
	 * @param array   $properties Other config info of the checkbox.
	 * @return void
	 */
	public static function checkbox( $id, $name, $checked = false, $properties = array() ) {

		self::input( 'hidden', '', $name, 0 );

		if ( $checked ) {
			$properties['checked'] = 'checked';
		}

		if ( isset( $properties['class'] ) ) {
			$properties['class'] .= ' checkbox';
		} else {
			$properties['class'] = 'checkbox';
		}

		self::input( 'checkbox', $id, $name, 1, $properties );
	}

	/**
	 * Print a radio control.
	 *
	 * @param string  $name The property name of this control.
	 * @param string  $value The value this radio option represent.
	 * @param boolean $checked If this radio option is checked.
	 * @param array   $properties Other properties of this option.
	 * @return void
	 */
	public static function radio( $name, $value, $checked = false, $properties = array() ) {
		if ( $checked ) {
			$properties['checked'] = 'checked';
		}

		self::input( 'radio', '', $name, $value, $properties );
	}
}
