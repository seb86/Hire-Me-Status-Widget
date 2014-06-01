<?php
/**
 * Displays the status of your hire availability.
 *
 * @author 		Sebastien Dumont
 * @category 	Widgets
 * @package 	Hire Me Status Widget/Widgets
 * @version 	1.0.0
 * @extends 	WP_Widget
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Widget - Hire Me Status
 */
class Hire_Me_Status extends WP_Widget {

	public $widget_cssclass;
	public $widget_description;
	public $widget_id;
	public $widget_name;
	public $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass 		= 'hire-me-status widget';
		$this->widget_description 	= __( 'Display your hiring status for a single person or a team.', 'hire-me-status-widget' );
		$this->widget_id 			= 'hire_me_status_widget';
		$this->widget_name 			= __( 'Hire Me Status', 'hire-me-status-widget' );

		$this->settings = array(
			'title' 			=> array(
				'type' 			=> 'text',
				'std' 			=> __( 'Availability Status', 'hire-me-status-widget' ),
				'label' 		=> __( 'Title', 'hire-me-status-widget' )
			),
			'team_members'		=> array(
				'type' 			=> 'select',
				'options' 		=> apply_filters( 'hire_me_status_team_members', array(
					'freelancer' 	=> __( 'I am a Freelancer', 'hire-me-status-widget' ),
					'studio' 		=> __( 'We are a Studio', 'hire-me-status-widget' ),
				) ),
				'label' => __( 'Team Members', 'hire-me-status-widget')
			),
			'status' 				=> array(
				'type' 			=> 'select',
				'options' 		=> array(
					'available' 	=> __( 'Available', 'hire-me-status-widget' ),
					'unavailable' 	=> __( 'Unavailable', 'hire-me-status-widget' ),
				),
				'label' => __( 'Status', 'hire-me-status-widget')
			),
		);

		$widget_ops = array(
			'classname'   => $this->widget_cssclass,
			'description' => $this->widget_description
		);

		// Refreshing the widget's cached output with each new post
		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

		parent::__construct(
			$this->widget_id, // Base ID
			$this->widget_name, // Name,
			$widget_ops // Args
		);
	}

	/**
	 * Return the widget slug.
	 *
	 * @return  Plugin slug variable.
	 */
	public function get_widget_slug() {
		return $this->widget_id;
	}

	/**
	 * Outputs the content of the widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @access public
	 *
	 * @param array $args 		Widgets arguments.
	 * @param array $instance 	Saved values from database.
	 */
	public function widget( $args, $instance ) {
		// Check if there is a cached output
		$cache = wp_cache_get( $this->get_widget_slug(), 'widget' );

		if( !is_array( $cache ) )
			$cache = array();

		if( !isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->get_widget_slug();

		if( isset( $cache[ $args['widget_id'] ] ) )
			return print $cache[ $args['widget_id'] ];

		ob_start();
		extract( $args, EXTR_SKIP );

		// these are the widget options
		$title           = $instance['title'];
		$team_members    = $instance['team_members'];
		$status          = $instance['status'];

		echo $args['before_widget'];

		// Display Widget
		echo '<div class="widget-text hire-me-status">';

		// if the title is set
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if( $team_members == 'freelancer' ) { echo __( 'I am', 'hire-me-status-widget' ); }
		elseif( $team_members == 'studio' ) { echo __( 'We are', 'hire-me-status-widget' ); }
		else{
			echo apply_filters( 'hire_me_status_display_team_member', '' ); // Use this filter along with filter 'hire_me_status_team_members'.
		}

		if( strtolower( $status ) == 'available' ) { $color = 'green'; }
		elseif( strtolower( $status ) == 'unavailable' ) { $color = 'orangered'; }

		echo ' <span class="' . strtolower( $status ) . '" style="color:' . $color . '"><strong>';

		if( $status == 'available' ) { echo __( 'Available', 'hire-me-status-widget' ); }
		elseif( $status == 'unavailable' ) { echo __( 'Unavailable', 'hire-me-status-widget' ); }

		echo __( 'for Hire', 'hire-me-status-widget' );
		echo '</strong></span> ';

		echo '</div>';

		echo $args['after_widget'];

		$content = ob_get_clean();

		echo $content;

		$this->cache_widget( $args, $content );
	}

	/**
	 * Cache the widget
	 */
	public function cache_widget( $args, $content ) {
		$cache[ $args['widget_id'] ] = $content;

		wp_cache_set( $this->widget_id, $cache, 'widget' );
	}

	/**
	 * Flush the cache
	 * @return [type]
	 */
	public function flush_widget_cache() {
		wp_cache_delete( $this->get_widget_slug(), 'widget' );
	}

	/**
	 * Processes the widget's options to be saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array new_instance - The new instance of values to be generated via the update.
	 * @param array old_instance - The previous instance of values before the update.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] 			= ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['team_members'] 	= ( !empty( $new_instance['team_members'] ) ) ? strip_tags( $new_instance['team_members'] ) : 'freelancer';
		$instance['status'] 		= strip_tags( $new_instance['status'] );

		$this->flush_widget_cache();

		return $instance;
	}

	/**
	 * Generates the administration form for the widget.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args(
			(array) $instance
		);

		$title 			= esc_attr( $instance['title'] );
		$team_members 	= esc_attr( $instance['team_members'] );
		$status 		= esc_attr( $instance['status'] );

		if( ! $this->settings )
			return;

		foreach( $this->settings as $key => $setting ) {

			$value   = isset( $instance[ $key ] ) ? $instance[ $key ] : $setting['std'];

			switch ( $setting['type'] ) {
				case "text" :
					?>
					<p>
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" />
					</p>
					<?php
				break;
				case "number" :
					?>
					<p>
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="number" step="<?php echo esc_attr( $setting['step'] ); ?>" min="<?php echo esc_attr( $setting['min'] ); ?>" max="<?php echo esc_attr( $setting['max'] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
					</p>
					<?php
				break;
				case "select" :
					?>
					<p>
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
						<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>">
							<?php foreach ( $setting['options'] as $option_key => $option_value ) : ?>
								<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $option_key, $value ); ?>><?php echo esc_html( $option_value ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<?php
				break;
				case "checkbox" :
					?>
					<p>
						<input id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="checkbox" value="1" <?php checked( $value, 1 ); ?> />
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
					</p>
					<?php
				break;
			}
		}
	}

}

register_widget('Hire_Me_Status');

?>