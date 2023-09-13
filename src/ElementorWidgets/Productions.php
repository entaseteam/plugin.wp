<?php

namespace Entase\Plugins\WP\ElementorWidgets;

use \Entase\Plugins\WP\Conf;

class Productions extends \Elementor\Widget_Base
{
    private static $widgetName = 'entase_productions';
    private static $widgetTitle = 'Productions - Entase';

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
		return 'eicon-post-list';
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
                    'post_tags' => 'Post tags',
                    'entase_title' => '[Production] Title',
                    'entase_story' => '[Production] Story',
                    'entase_photo_poster' => '[Production] Photo poster',
                    'entase_photo_og' => '[Production] Photo OG',
					'multisource_image' => 'Multi-source image'
                ],
                'default' => ['post_title', 'entase_photo_poster']
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
			'contentchars',
			[
				'label' => 'Max content chars',
				'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 200,
                'placeholder' => 'Default: 30'
			]
		);

		$this->add_control(
			'cssnames',
			[
				'label' => 'Dynamic class names',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => [
					'category' => 'Relative categories',
                    'tag' => 'Relative tags',
                ],
                'default' => []
			]
		);

        $this->end_controls_section();


		/* ************************* */
        /* MULTISOURCE IMAGE SECTION */
        /* ************************* */
		$this->start_controls_section(
			'multisource-image-section',
			[
				'label' => 'Multi-source image',
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'source',
			[
				'label' => 'Source',                
				'type' => \Elementor\Controls_Manager::SELECT,
				'multiple' => false,
				'options' => [
                    'post_feature_image' => 'Post feature image',
                    'entase_photo_poster' => '[Production] Photo poster',
                    'entase_photo_og' => '[Production] Photo OG',
                ],
                'default' => 'post_feature_image'
			]
		);

		$this->add_control(
			'multisource_image',
			[
				'label' => 'Add sources for multi-source image',
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'title_field' => '{{{ source }}}',
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

        $categories = get_categories(['hide_empty' => false]);
        $categoryArr = [];
        foreach ($categories as $category) $categoryArr[$category->term_id] = $category->name;
        $this->add_control(
			'filter_categories',
			[
				'label' => 'Categories',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => $categoryArr,
			]
		);
        
        $this->add_control(
			'filter_current_categories',
			[
				'label' => 'All in queried object categories',
				'type' => \Elementor\Controls_Manager::SWITCHER
			]
		);

        $tags = get_tags(['hide_empty' => false]);
        $tagArr = [];
        foreach ($tags as $tag) $tagArr[$tag->term_id] = $tag->name;
        $this->add_control(
			'filter_tags',
			[
				'label' => 'Tags',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => $tagArr,
			]
		);

        $this->add_control(
			'filter_current_tags',
			[
				'label' => 'All in queried object tags',
				'type' => \Elementor\Controls_Manager::SWITCHER
			]
		);

        $this->end_controls_section();


		/* ******************* */
        /* CUSTOM META SECTION */
        /* ******************* */
        $this->start_controls_section(
			'meta_section',
			[
				'label' => 'Custom Meta Fields',
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'field', [
				'label' => 'Meta Field',
				'type' => \Elementor\Controls_Manager::TEXT
			]
		);

		$this->add_control(
			'metafields',
			[
				'label' => 'Display additional custom meta fields',
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'title_field' => '{{{ field }}}',
			]
		);
		$this->end_controls_section();

	}

    public function get_style_depends() {

		wp_register_style('entase-widget-productions', Conf::CSSUrl.'/front/widgets/productions-classic.css');

		return [
			'entase-widget-productions',
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
        echo \Entase\Plugins\WP\Shortcodes\Productions::Do($settings, '', 'entase_productions');
	}
}