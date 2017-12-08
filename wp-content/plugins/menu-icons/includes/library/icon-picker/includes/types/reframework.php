<?php
/**
 * Genericons
 *
 * @package Icon_Picker
 * @author  Dzikri Aziz <kvcrvt@gmail.com>
 */
class Icon_Picker_Type_Reframework extends Icon_Picker_Type_Font {

	/**
	 * Icon type ID
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $id = 'reframework';

	/**
	 * Icon type name
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $name = 'Reframework';

	/**
	 * Icon type version
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $version = '0.1';

	/**
	 * Stylesheet ID
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $stylesheet_id = 'reframework';


	/**
	 * Get icon groups
	 *
	 * @since  0.1.0
	 * @return array
	 */
	public function get_groups() {
		$groups = array(
			array(
				'id'   => 'reframe',
				'name' => __( 'Reframe', 'icon-picker' ),
			),
		);

		/**
		 * Filter genericon groups
		 *
		 * @since 0.1.0
		 * @param array $groups Icon groups.
		 */
		$groups = apply_filters( 'icon_picker_genericon_groups', $groups );

		return $groups;
	}


	/**
	 * Get icon names
	 *
	 * @since  0.1.0
	 * @return array
	 */
	public function get_items() {
		$items = array(
			array(
				'id'   => 'reframework-stories',
                'group'=> 'reframe',
				'name' => __( 'Stories', 'icon-picker' ),
			),
            array(
				'id'   => 'reframework-services',
                'group'=> 'reframe',
				'name' => __( 'Services', 'icon-picker' ),
			),
            array(
				'id'   => 'reframework-about',
                'group'=> 'reframe',
				'name' => __( 'About', 'icon-picker' ),
			),
            array(
				'id'   => 'reframework-contact',
                'group'=> 'reframe',
				'name' => __( 'Contact', 'icon-picker' ),
			),
            array(
				'id'   => 'reframework-calendar',
                'group'=> 'reframe',
				'name' => __( 'Calendar', 'icon-picker' ),
			),
            array(
				'id'   => 'reframework-meeting',
                'group'=> 'reframe',
				'name' => __( 'Meeting', 'icon-picker' ),
			),
		);

		/**
		 * Filter genericon items
		 *
		 * @since 0.1.0
		 * @param array $items Icon names.
		 */
		$items = apply_filters( 'icon_picker_genericon_items', $items );

		return $items;
	}
}
