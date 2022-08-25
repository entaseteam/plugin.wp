<?php

namespace Entase\Plugins\WP\ElementorWidgets;

use \Entase\Plugins\WP\Conf;

class Events extends \Elementor\Widget_Base
{
    private static $widgetName = 'entase_events';
    private static $widgetTitle = 'Events - Entase';

    /**
	 * Get widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name() {
		return self::$widgetName;
	}

	/**
	 * Get widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return self::$widgetTitle;
	}

	/**
	 * Get widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-calendar';
	}

	/**
	 * Get custom help URL.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget help URL.
	 */
	public function get_custom_help_url() {
		return 'https://github.com/entaseteam/plugin.wp';
	}

	/**
	 * Get widget categories.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'general' ];
	}

	/**
	 * Get widget keywords.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'entase', 'post', 'production', 'event' ];
	}

	/**
	 * Register oEmbed widget controls.
	 *
	 * Add input fields to allow the user to customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() 
    {

        /* ************** */
        /* CONTENT SECTION */
        /* ************** */
		$this->start_controls_section(
			'content_section',
			[
				'label' => 'Content',
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'fields',
			[
				'label' => 'Fields',                
				'type' => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => [
                    'post_title' => 'Post title',
                    'post_content' => 'Post content',
                    'post_feature_image' => 'Post feature image',
                    'entase_title' => '[Entase] Title',
                    'entase_story' => '[Entase] Story',
                    'entase_dateStart' => '[Entase] Start date - Full',
                    'entase_dateonly' => '[Entase] Start date - Date only',
                    'entase_timeonly' => '[Entase] Start date - Time only',
                    'entase_book' => '[Entase] Book button',
                    'entase_photo_poster' => '[Entase] Photo - Poster',
                    'entase_photo_og' => '[Entase] Photo - OG',
					'entase_location_countryCode' => '[Entase] Location - Country code',
					'entase_location_countryName' => '[Entase] Location - Country name',
					'entase_location_cityName' => '[Entase] Location - City name',
					'entase_location_postCode' => '[Entase] Location - Post code',
					'entase_location_address' => '[Entase] Location - Address',
					'entase_location_placeName' => '[Entase] Location - Place name',
					'entase_location_lat' => '[Entase] Location - Latitude',
					'entase_location_lng' => '[Entase] Location - Longitude',
                ],
                'default' => ['entase_photo_poster', 'post_title', 'entase_dateonly', 'entase_timeonly', 'entase_book']
			]
		);

        $this->add_control(
			'skin',
			[
				'label' => 'Skin',                
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
                    'classic' => 'Classic'
                ],
                'default' => 'classic'
			]
		);

		$this->add_control(
			'sort',
			[
				'label' => 'Sort',                
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
                    'entase_dateStart/asc' => 'Date ASC',
					'entase_dateStart/desc' => 'Date DESC',
                ],
                'default' => 'entase_dateStart/asc'
			]
		);

        $this->add_control(
			'limit',
			[
				'label' => 'Limit',
                'description' => 'Use "0" for "no limit"',
				'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => '0'
			]
		);

		$this->end_controls_section();


        /* ***************** */
        /* CUSTOMIZE SECTION */
        /* ***************** */
		$this->start_controls_section(
			'customize_section',
			[
				'label' => 'Customize',
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

        $this->add_control(
			'targeturl',
			[
				'label' => 'Default click action',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
                    'book' => 'Open book window',
                    'production' => 'Redirect to production page',
                ],
                'default' => 'book'
			]
		);

        $this->add_control(
			'booklabel',
			[
				'label' => 'Book button label',
				'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Book',
                'placeholder' => 'Default: Book',
			]
		);

        $this->add_control(
			'dateformat',
			[
				'label' => 'Date format',
				'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'd/m',
                'placeholder' => 'Default: d/m'
			]
		);

        $this->add_control(
			'timeformat',
			[
				'label' => 'Time format',
				'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'H:i',
                'placeholder' => 'Default: H:i'
			]
		);

        $this->end_controls_section();

        /* ************** */
        /* QUERY SECTION */
        /* ************** */
        $this->start_controls_section(
			'query_section',
			[
				'label' => 'Query',
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

        
        $this->add_control(
			'filter_status',
			[
				'label' => 'Status',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => ['Pending', 'Open Sell', 'Closed Sell', 'Finished', 'Canceled'],
                'default' => 1
			]
		);

        $productions = get_posts(['post_type' => 'production']);
        $productionsArr = [];
        foreach ($productions as $production) 
		{
			$productionID = get_post_meta($production->ID, 'entase_id', true);
			$productionsArr[$productionID] = $production->post_title;
		}
        $this->add_control(
			'filter_productions',
			[
				'label' => 'Productions',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => $productionsArr,
			]
		);
        
        $this->add_control(
			'filter_current_production',
			[
				'label' => 'All in queried production',
				'type' => \Elementor\Controls_Manager::SWITCHER
			]
		);

        $this->add_control(
			'allow_qs_production',
			[
				'label' => 'Allow production filter in query string',
                'description' => 'Use "?prod=...."',
				'type' => \Elementor\Controls_Manager::SWITCHER
			]
		);

        $this->add_control(
			'allow_qs_date',
			[
				'label' => 'Allow date filter in query string',
                'description' => 'Use "?date=YYYMMDD-YYYMMDD"',
				'type' => \Elementor\Controls_Manager::SWITCHER
			]
		);

        $this->end_controls_section();

	}

    public function get_style_depends() {

		wp_register_style('entase-widget-events', Conf::CSSUrl.'/front/widgets/events-classic.css');

		return [
			'entase-widget-events',
		];

	}

	/**
	 * Render widget output on the frontend.
	 *
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() 
    {
        $settings = $this->get_settings_for_display();
		$settings['nostyles'] = true;
        if (is_admin()) $settings['targeturl'] = '';
        echo \Entase\Plugins\WP\Shortcodes\Events::Do($settings, '', 'entase_events');
	}
}