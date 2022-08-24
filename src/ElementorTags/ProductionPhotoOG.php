<?php

namespace Entase\Plugins\WP\ElementorTags;


class ProductionPhotoOG extends \Elementor\Core\DynamicTags\Data_Tag 
{
    private static $tagName = 'entase_photo_og';
    private static $tagTitle = 'OG photo - Entase';


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
            \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY
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

		$this->add_control('source', [
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'label' => 'Source',
				'options' => [
				    'small' => [
					    'title' => 'Small',
					    'icon' => 'eicon-image',
				    ],
				    'meidum' => [
					    'title' => 'Medium',
					    'icon' => 'eicon-image',
                    ],
                    'large' => [
					    'title' => 'Large',
					    'icon' => 'eicon-image',
                    ],
                ]
			]
		);

	}

    public function get_value($options=[])
    {
        $size = $this->get_settings('source');
        
        $atts = ['srconly' => 1];
        if ($size != '') $atts['size'] = $size;

        return ['url' => \Entase\Plugins\WP\Shortcodes\Photo::Do($atts, '', 'entase_photo_og')];
    }
	
}