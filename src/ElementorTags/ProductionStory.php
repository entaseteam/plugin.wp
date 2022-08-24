<?php

namespace Entase\Plugins\WP\ElementorTags;


class ProductionStory extends \Elementor\Core\DynamicTags\Tag
{
    private static $tagName = 'entase_story';
    private static $tagTitle = 'Story - Entase';


    /**
     * Get dynamic tag name.
     *
     * Retrieve the name of the photo tag.
     *
     * @since 1.0.0
     * @access public
     * @return string Dynamic tag name.
     */
	public function get_name() {
		return self::$tagName;
	}


	/**
     * Get dynamic tag title.
     *
     * Returns the title of the photo tag.
     *
     * @since 1.0.0
     * @access public
     * @return string Dynamic tag title.
     */
	public function get_title() {
		return self::$tagTitle;
	}


	/**
     * Get dynamic tag groups.
     *
     * Retrieve the list of groups the photo tag belongs to.
     *
     * @since 1.0.0
     * @access public
     * @return array Dynamic tag groups.
     */
	public function get_group() {
		return [ 'post' ];
	}


    /**
     * Get dynamic tag categories.
     *
     * Retrieve the list of categories the random number tag belongs to.
     *
     * @since 1.0.0
     * @access public
     * @return array Dynamic tag categories.
     */
	public function get_categories() {
		return [ 
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY, 
        ];
	}


    /**
     * Register dynamic tag controls.
     *
     * Add input fields to allow the user to customize the server variable tag settings.
     *
     * @since 1.0.0
     * @access protected
     * @return void
     */

	protected function register_controls() {

		$this->add_control('searchurl', [
            'type' => \Elementor\Controls_Manager::TEXT,
            'label' => 'Search URL',
            'placeholder' => 'https://www.entase.bg/?search=$tag',
        ]);
        
        $this->add_control('linktarget', [
            'type' => \Elementor\Controls_Manager::CHOOSE,
            'label' => 'Target',
            'options' => [
                '_same' => [
                    'title' => 'Same window',
                    'icon' => 'eicon-image',
                ],
                '_blank' => [
                    'title' => 'New window',
                    'icon' => 'eicon-image',
                ]
            ]
        ]);
	}

	/**
     * Render tag output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access public
     * @return void
     */
	public function render() 
    {
        $searchurl = $this->get_settings('searchurl');
        $linktarget = $this->get_settings('linktarget');
        
        $atts = [];
        if ($searchurl != '') $atts['searchurl'] = $searchurl;
        if ($linktarget != '') $atts['linktarget'] = $linktarget != '_same' ? $linktarget : '';

        echo \Entase\Plugins\WP\Shortcodes\Meta::Do($atts, '', 'entase_story');
	}
}