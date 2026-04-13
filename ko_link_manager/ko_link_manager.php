<?php
/*
Plugin Name: KO Link Manager
Description: Link manager with categories and link ordering. Display links using shortcode [ko_links]. Shortcode attributes are: category="category-slug OR ID" and title="Custom Title"]. EXAMPLE USAGE: [ko_links category="ID" title="Custom Title"]. Link order defaults to ASC but can be set manually in the link's edit screen.
Version: 1.9
Author: Kevin ONeill
Author URI: mailto:6822858@kevinoneill.us
*/

// Register Custom Post Type for Links
function ko_register_link_post_type() {
    register_post_type('ko_link', [
        'labels' => [
            'name' => __('Links'),
            'singular_name' => __('Link'),
            'add_new_item' => __('Add New Link'),
            'edit_item' => __('Edit Link'),
            'new_item' => __('New Link'),
            'view_item' => __('View Link'),
            'search_items' => __('Search Links'),
        ],
        'public' => false,
        'show_ui' => true,
        'supports' => ['title'],
    ]);

    // Register Custom Taxonomy for Categories
    register_taxonomy('ko_category', 'ko_link', [
        'labels' => [
            'name' => __('Link Categories'),
            'singular_name' => __('Link Category'),
        ],
        'hierarchical' => true,
    ]);
}
add_action('init', 'ko_register_link_post_type');

// Add Custom Meta Boxes for Link URL, Target, and Order
function ko_add_meta_boxes() {
    add_meta_box('ko_link_url', __('Link URL'), 'ko_link_url_meta_box_callback', 'ko_link', 'normal', 'default');
    add_meta_box('ko_link_target', __('Open in New Tab?'), 'ko_link_target_meta_box_callback', 'ko_link', 'normal', 'default');
    add_meta_box('ko_link_order', __('Link Order'), 'ko_link_order_meta_box_callback', 'ko_link', 'normal', 'default');
}
add_action('add_meta_boxes', 'ko_add_meta_boxes');

function ko_link_url_meta_box_callback($post) {
    $url = get_post_meta($post->ID, '_ko_link_url', true);
    echo '<label for="ko_link_url">' . __('URL', 'ko') . ':</label> ';
    echo '<input type="text" id="ko_link_url" name="ko_link_url" value="' . esc_attr($url) . '" size="50" />';
}

function ko_link_target_meta_box_callback($post) {
    $target = get_post_meta($post->ID, '_ko_link_target', true);
    echo '<label for="ko_link_target">' . __('Open in new tab?', 'ko') . '</label> ';
    echo '<input type="checkbox" id="ko_link_target" name="ko_link_target" value="_blank" ' . checked($target, '_blank', false) . ' />';
}

function ko_link_order_meta_box_callback($post) {
    $order = get_post_meta($post->ID, '_ko_link_order', true);
    echo '<label for="ko_link_order">' . __('Order:', 'ko') . '</label> ';
    echo '<input type="number" id="ko_link_order" name="ko_link_order" value="' . esc_attr($order) . '" size="5" />';
    echo '<p>' . __('Set a custom order for the link. Lower numbers will appear first.', 'ko') . '</p>';
}

// Save Meta Box Data
function ko_save_meta_box_data($post_id) {
    if (array_key_exists('ko_link_url', $_POST)) {
        update_post_meta($post_id, '_ko_link_url', sanitize_text_field($_POST['ko_link_url']));
    }
    
    $target = isset($_POST['ko_link_target']) ? '_blank' : '_self';
    update_post_meta($post_id, '_ko_link_target', $target);
    
    if (array_key_exists('ko_link_order', $_POST)) {
        update_post_meta($post_id, '_ko_link_order', intval($_POST['ko_link_order']));
    }
}
add_action('save_post', 'ko_save_meta_box_data');

// Shortcode to Display Links
function ko_links_shortcode($atts) {
    $atts = shortcode_atts([
        'category' => '',
        'title' => '',
    ], $atts, 'ko_links');

    $args = [
        'post_type' => 'ko_link',
        'posts_per_page' => -1,
        'orderby' => 'meta_value_num',
        'meta_key' => '_ko_link_order',
        'order' => 'ASC',
    ];

    if (!empty($atts['category'])) {
        if (is_numeric($atts['category'])) {
            $category = get_term($atts['category'], 'ko_category');
        } else {
            $category = get_term_by('slug', $atts['category'], 'ko_category');
        }

        if ($category && !is_wp_error($category)) {
            if (empty($atts['title'])) {
                $atts['title'] = $category->name;
            }
            $args['tax_query'] = [[
                'taxonomy' => 'ko_category',
                'field' => 'term_id',
                'terms' => $category->term_id,
            ]];
        }
    }

    $links = new WP_Query($args);
    if ($links->have_posts()) {
        $output = '';
        if (!empty($atts['title'])) {
            $output .= '<h4 class="ko_links_title">' . esc_html($atts['title']) . '</h4>';
        }
        $output .= '<ul class="ko-links">';
        while ($links->have_posts()) {
            $links->the_post();
            $url = get_post_meta(get_the_ID(), '_ko_link_url', true);
            $target = get_post_meta(get_the_ID(), '_ko_link_target', true);

            if (!empty($url) && parse_url($url, PHP_URL_SCHEME) === null) {
                $url = site_url($url);
            }

            $output .= '<li><a href="' . esc_url($url) . '" target="' . esc_attr($target) . '">' . get_the_title() . '</a></li>';
        }
        wp_reset_postdata();
        $output .= '</ul>';
    } else {
        $output = '<p>' . __('No links found.', 'ko') . '</p>';
    }

    return $output;
}
add_shortcode('ko_links', 'ko_links_shortcode');
