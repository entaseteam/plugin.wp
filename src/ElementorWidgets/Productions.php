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
		return 'eicon-code';
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
                    'entase_title' => 'Entase title',
                    'entase_story' => 'Entase story',
                    'entase_photo_poster' => 'Entase photo poster',
                    'entase_photo_og' => 'Entase photo og',
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

	}

    public function get_style_depends() {

		wp_register_style('entase-widget-productions', Conf::CSSUrl.'/front/elementor-widgets/productions-classic.css');

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
        $limit = $settings['limit'] ?? 0;
        $categories = $settings['filter_categories'] ?? [];
        $tags = $settings['filter_tags'] ?? [];

        if ($settings['filter_current_categories'] == 'yes')
        {
            $post = get_queried_object();
            if ($post)
            {
                $terms = get_the_terms($post->ID, 'category');
                foreach($terms as $term) $categories[] = $term->term_id;
            }
        }

        if ($settings['filter_current_tags'] == 'yes')
        {
            $post = get_queried_object();
            if ($post)
            {
                $terms = get_the_terms($post->ID, 'post_tag');
                foreach($terms as $term) $tags[] = $term->term_id;
            }
        }

        $query = [
            'post_type' => 'production',
            'posts_per_page' => $limit > 0 ? $limit : -1,
            'tax_query' => []
        ];

        if (count($categories) > 0)
        {
            $query['tax_query'][] = [
                  'taxonomy' => 'category',
                  'field' => 'term_id', 
                  'terms' => $categories,
                  'include_children' => true
            ];
        }

        if (count($tags) > 0)
        {
            $query['tax_query'][] = [
                  'taxonomy' => 'post_tag',
                  'field' => 'term_id', 
                  'terms' => $tags,
                  'include_children' => true
            ];
        }

        $items = [];
        $productions = get_posts($query);
        if ($productions && count($productions) > 0)
        {
            foreach($productions as $production)
            {
                $photo = null;
                $row = [];
                foreach ($settings['fields'] as $field)
                {
                    switch($field)
                    {
                        case 'post_title':
                            $row[] = ['key' => 'post_title', 'val' => $production->post_title];
                            break;
                        case 'post_content':
                            $row[] = ['key' => 'post_content', 'val' => $production->post_content];
                            break;
                        case 'post_feature_image':
                            $row[] = ['key' => 'post_feature_image', 'val' => get_the_post_thumbnail($production->ID, 'large')];
                            break;
                        case 'post_tags':
                            //$tags = get_the_tags($production->ID);
                            $tags = get_tags(['object_ids' => $production->ID, 'orderby' => 'count', 'order' => 'DESC']);
                            $tagsArr = [];
                            foreach($tags as $key => $tag) $tagsArr[] = $tag->name;
                            if (count($tagsArr) > 3) array_splice($tagsArr, 3);
                            $row[] = ['key' => 'post_tags', 'val' => implode(', ', $tagsArr)];
                            break;
                        case 'entase_title':
                            $row[] = ['key' => 'entase_title', 'val' => get_post_meta($production->ID, 'entase_title', true)];
                            break;
                        case 'entase_story':
                            $row[] = ['key' => 'entase_story', 'val' =>  get_post_meta($production->ID, 'entase_story', true)];
                            break;
                        case 'entase_photo_poster':
                            if ($photo == null)
                            {
                                $meta =  get_post_meta($production->ID, 'entase_photo', true);
                                $photo = @json_decode($meta) ?? null;
                            }
                            $row[] = ['key' => 'entase_photo_poster', 'val' => $photo != null ? '<img src="'.$photo->poster->medium.'" />' : ''];
                            break;
                        case 'entase_photo_og':
                            if ($photo == null)
                            {
                                $meta =  get_post_meta($production->ID, 'entase_photo', true);
                                $photo = @json_decode($meta) ?? null;
                            }
                            $row[] = ['key' => 'entase_photo_og', 'val' => $photo != null ? '<img src="'.$photo->og->large.'" />' : ''];
                            break;
                    }
                }
                $items[] = $row;
            }
        }

        $atmf = \ATMF\Setup::GetEngine();
        $atmf->vars['items'] = $items;
        $atmf->RendTemplate('ElementorWidgets/Productions_Classic');

	}
}