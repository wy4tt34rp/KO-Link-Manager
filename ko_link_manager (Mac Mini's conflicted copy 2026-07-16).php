<?php
/**
 * Plugin Name: Ocular Acumen Link Manager
 * Description: Manage categorized, ordered link lists and display them with the [ko_links] shortcode.
 * Version: 2.4.3
 * Author: KO
 * Author URI: mailto:6822858@kevinoineill.us
 * Text Domain: ko-link-manager
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'KO_LINK_MANAGER_VERSION', '2.4.3' );
define( 'KO_LINK_MANAGER_URL', plugin_dir_url( __FILE__ ) );

function ko_link_manager_register_content() {
	register_post_type(
		'ko_link',
		array(
			'labels' => array(
				'name'          => __( 'Links', 'ko-link-manager' ),
				'singular_name' => __( 'Link', 'ko-link-manager' ),
				'add_new_item'  => __( 'Add New Link', 'ko-link-manager' ),
				'edit_item'     => __( 'Edit Link', 'ko-link-manager' ),
				'new_item'      => __( 'New Link', 'ko-link-manager' ),
				'view_item'     => __( 'View Link', 'ko-link-manager' ),
				'search_items'  => __( 'Search Links', 'ko-link-manager' ),
				'not_found'     => __( 'No links found.', 'ko-link-manager' ),
			),
			'public'       => false,
			'show_ui'      => true,
			// Navigation is registered explicitly below so taxonomy tools remain visible.
			'show_in_menu' => false,
			'menu_icon'    => 'dashicons-admin-links',
			'supports'     => array( 'title' ),
			'map_meta_cap' => true,
		)
	);

	register_taxonomy(
		'ko_category',
		'ko_link',
		array(
			'labels' => array(
				'name'          => __( 'Link Categories', 'ko-link-manager' ),
				'singular_name' => __( 'Link Category', 'ko-link-manager' ),
			),
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => false,
		)
	);
}
add_action( 'init', 'ko_link_manager_register_content' );

function ko_link_manager_admin_menu() {
	add_menu_page(
		__( 'Ocular Acumen Link Manager', 'ko-link-manager' ),
		__( 'Link Manager', 'ko-link-manager' ),
		'edit_posts',
		'ko-link-manager',
		'ko_link_manager_render_dashboard',
		'dashicons-admin-links',
		58
	);

	add_submenu_page(
		'ko-link-manager',
		__( 'Link Manager Overview', 'ko-link-manager' ),
		__( 'Overview', 'ko-link-manager' ),
		'edit_posts',
		'ko-link-manager',
		'ko_link_manager_render_dashboard'
	);

	add_submenu_page(
		'ko-link-manager',
		__( 'All Links', 'ko-link-manager' ),
		__( 'All Links', 'ko-link-manager' ),
		'edit_posts',
		'edit.php?post_type=ko_link'
	);

	add_submenu_page(
		'ko-link-manager',
		__( 'Add New Link', 'ko-link-manager' ),
		__( 'Add New', 'ko-link-manager' ),
		'edit_posts',
		'post-new.php?post_type=ko_link'
	);

	add_submenu_page(
		'ko-link-manager',
		__( 'Link Categories', 'ko-link-manager' ),
		__( 'Categories', 'ko-link-manager' ),
		'manage_categories',
		'edit-tags.php?taxonomy=ko_category&post_type=ko_link'
	);
}
add_action( 'admin_menu', 'ko_link_manager_admin_menu', 20 );

function ko_link_manager_parent_menu( $parent_file ) {
	$screen = get_current_screen();
	if ( $screen && ( 'ko_link' === $screen->post_type || 'ko_category' === $screen->taxonomy ) ) {
		return 'ko-link-manager';
	}

	return $parent_file;
}
add_filter( 'parent_file', 'ko_link_manager_parent_menu' );

function ko_link_manager_render_dashboard() {
	$link_count     = wp_count_posts( 'ko_link' );
	$published      = isset( $link_count->publish ) ? (int) $link_count->publish : 0;
	$category_count = wp_count_terms( array( 'taxonomy' => 'ko_category', 'hide_empty' => false ) );
	$category_count = is_wp_error( $category_count ) ? 0 : (int) $category_count;
	$placements     = ko_link_manager_find_shortcode_placements();
	?>
	<div class="wrap ko-lm-wrap">
		<header class="ko-lm-hero">
			<img src="<?php echo esc_url( KO_LINK_MANAGER_URL . 'assets/images/oa-logo-white.svg' ); ?>" alt="<?php esc_attr_e( 'Ocular Acumen', 'ko-link-manager' ); ?>">
			<div><span><?php esc_html_e( 'Ocular Acumen', 'ko-link-manager' ); ?></span><h1><?php esc_html_e( 'Link Manager', 'ko-link-manager' ); ?></h1><p><?php esc_html_e( 'Create, organize, and publish consistent link groups throughout this site.', 'ko-link-manager' ); ?></p></div>
		</header>
		<div class="ko-lm-grid">
			<section class="ko-lm-panel ko-lm-stats">
				<h2><?php esc_html_e( 'At a glance', 'ko-link-manager' ); ?></h2>
				<div><strong><?php echo esc_html( $published ); ?></strong><span><?php esc_html_e( 'Published links', 'ko-link-manager' ); ?></span></div>
				<div><strong><?php echo esc_html( $category_count ); ?></strong><span><?php esc_html_e( 'Categories', 'ko-link-manager' ); ?></span></div>
			</section>
			<section class="ko-lm-panel">
				<h2><?php esc_html_e( 'Manage links', 'ko-link-manager' ); ?></h2>
				<p><?php esc_html_e( 'Add a title and destination, choose an optional category, and use the order value to control placement.', 'ko-link-manager' ); ?></p>
				<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ko_link' ) ); ?>"><?php esc_html_e( 'Add New Link', 'ko-link-manager' ); ?></a> <a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=ko_link' ) ); ?>"><?php esc_html_e( 'View All Links', 'ko-link-manager' ); ?></a> <a class="button" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=ko_category&post_type=ko_link' ) ); ?>"><?php esc_html_e( 'Manage Link Categories', 'ko-link-manager' ); ?></a></p>
			</section>
			<section class="ko-lm-panel">
				<h2><?php esc_html_e( 'Publish a link list', 'ko-link-manager' ); ?></h2>
				<p><?php esc_html_e( 'Add the shortcode to a page, post, widget, or template-supported content area.', 'ko-link-manager' ); ?></p>
				<code>[ko_links category="footer" title="Explore"]</code>
				<p class="description"><?php esc_html_e( 'Category accepts a category slug or ID. Omit title to use the category name.', 'ko-link-manager' ); ?></p>
			</section>
			<section class="ko-lm-panel ko-lm-guide">
				<h2><?php esc_html_e( 'How to use Link Manager', 'ko-link-manager' ); ?></h2>
				<div class="ko-lm-guide-steps">
					<div><span>1</span><h3><?php esc_html_e( 'Create a Link Category', 'ko-link-manager' ); ?></h3><p><?php esc_html_e( 'Create a group such as Footer, Travel Help, Resources, or Partners.', 'ko-link-manager' ); ?></p><a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=ko_category&post_type=ko_link' ) ); ?>"><?php esc_html_e( 'Manage Link Categories', 'ko-link-manager' ); ?></a></div>
					<div><span>2</span><h3><?php esc_html_e( 'Add and assign links', 'ko-link-manager' ); ?></h3><p><?php esc_html_e( 'Choose a custom URL, page, post, or WordPress category as the destination, then assign the link to a Link Category.', 'ko-link-manager' ); ?></p><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ko_link' ) ); ?>"><?php esc_html_e( 'Add New Link', 'ko-link-manager' ); ?></a></div>
					<div><span>3</span><h3><?php esc_html_e( 'Reorder the links', 'ko-link-manager' ); ?></h3><p><?php esc_html_e( 'Open Link Manager > Categories, edit the desired category, and drag links in the Link order section. Click Update to save.', 'ko-link-manager' ); ?></p><a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=ko_category&post_type=ko_link' ) ); ?>"><?php esc_html_e( 'Choose a Category', 'ko-link-manager' ); ?></a></div>
					<div><span>4</span><h3><?php esc_html_e( 'Place the shortcode', 'ko-link-manager' ); ?></h3><p><?php esc_html_e( 'Copy the category shortcode from the Categories screen and paste it into a page, post, footer, or supported template area.', 'ko-link-manager' ); ?></p><code>[ko_links category="footer"]</code></div>
				</div>
				<div class="ko-lm-css-reference">
					<h3><?php esc_html_e( 'CSS styling reference', 'ko-link-manager' ); ?></h3>
					<p><?php esc_html_e( 'Use these selectors in your theme or custom CSS:', 'ko-link-manager' ); ?></p>
					<ul>
						<li><code>.ko-links-title</code> — <?php esc_html_e( 'the optional list heading', 'ko-link-manager' ); ?></li>
						<li><code>ul.ko-links</code> — <?php esc_html_e( 'the unordered list wrapper; use this element-specific selector when overriding theme styles', 'ko-link-manager' ); ?></li>
						<li><code>.ko-links li</code> — <?php esc_html_e( 'each link item', 'ko-link-manager' ); ?></li>
						<li><code>.ko-links a</code> — <?php esc_html_e( 'each link anchor', 'ko-link-manager' ); ?></li>
						<li><code>.ko-links-empty</code> — <?php esc_html_e( 'the no-links-found message', 'ko-link-manager' ); ?></li>
					</ul>
				</div>
			</section>
			<section class="ko-lm-panel ko-lm-usage">
				<h2><?php esc_html_e( 'Shortcode placements', 'ko-link-manager' ); ?></h2>
				<?php if ( $placements ) : ?>
					<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Location', 'ko-link-manager' ); ?></th><th><?php esc_html_e( 'Source', 'ko-link-manager' ); ?></th><th><?php esc_html_e( 'Status', 'ko-link-manager' ); ?></th><th><?php esc_html_e( 'Shortcode', 'ko-link-manager' ); ?></th></tr></thead><tbody>
					<?php foreach ( $placements as $placement ) : ?>
						<tr><td><?php if ( $placement['edit_url'] ) : ?><a href="<?php echo esc_url( $placement['edit_url'] ); ?>"><?php echo esc_html( $placement['title'] ); ?></a><?php else : ?><?php echo esc_html( $placement['title'] ); ?><?php endif; ?></td><td><?php echo esc_html( $placement['source'] ); ?></td><td><?php echo esc_html( $placement['status'] ); ?></td><td><code><?php echo esc_html( $placement['shortcode'] ); ?></code> <button type="button" class="button button-small ko-lm-copy" data-copy="<?php echo esc_attr( $placement['shortcode'] ); ?>"><?php esc_html_e( 'Copy', 'ko-link-manager' ); ?></button></td></tr>
					<?php endforeach; ?>
					</tbody></table>
				<?php else : ?>
					<p><?php esc_html_e( 'No shortcode placements were found in stored WordPress content or active theme PHP files.', 'ko-link-manager' ); ?></p>
				<?php endif; ?>
				<p class="description"><?php esc_html_e( 'This reference scans all stored post types plus active parent and child theme PHP files. Placements saved only in plugin options or external systems may not appear here.', 'ko-link-manager' ); ?></p>
			</section>
		</div>
	</div>
	<?php
}

function ko_link_manager_find_shortcode_placements() {
	global $wpdb;
	$sql         = "SELECT ID, post_title, post_type, post_status, post_content FROM {$wpdb->posts} WHERE post_content LIKE %s AND post_type NOT IN ('ko_link', 'attachment', 'revision') AND post_status NOT IN ('trash', 'auto-draft') ORDER BY post_type, post_title";
	$posts       = $wpdb->get_results( $wpdb->prepare( $sql, '%[ko_links%' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$placements = array();

	foreach ( $posts as $matched_post ) {
		if ( ! preg_match_all( '/\[ko_links(?:\s[^\]]*)?\]/', $matched_post->post_content, $matches ) ) {
			continue;
		}
		$type_object   = get_post_type_object( $matched_post->post_type );
		$status_object = get_post_status_object( $matched_post->post_status );
		$shortcodes = array_unique( array_map( 'ko_link_manager_normalize_shortcode', $matches[0] ) );
		foreach ( $shortcodes as $shortcode ) {
			$placements[] = array(
				'title'     => $matched_post->post_title ?: __( '(no title)', 'ko-link-manager' ),
				'source'    => $type_object ? $type_object->labels->singular_name : $matched_post->post_type,
				'status'    => $status_object ? $status_object->label : $matched_post->post_status,
				'shortcode' => $shortcode,
				'edit_url'  => get_edit_post_link( $matched_post->ID, 'raw' ),
			);
		}
	}

	$theme = wp_get_theme();
	foreach ( $theme->get_files( 'php', -1, true ) as $relative_file => $absolute_file ) {
		if ( ! is_readable( $absolute_file ) ) {
			continue;
		}
		$contents = file_get_contents( $absolute_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( false === $contents || ! preg_match_all( '/\[ko_links(?:\s[^\]]*)?\]/', $contents, $matches ) ) {
			continue;
		}
		$shortcodes = array_unique( array_map( 'ko_link_manager_normalize_shortcode', $matches[0] ) );
		foreach ( $shortcodes as $shortcode ) {
			$placements[] = array(
				'title'     => $relative_file,
				'source'    => sprintf( __( 'Theme: %s', 'ko-link-manager' ), $theme->get( 'Name' ) ),
				'status'    => __( 'Active', 'ko-link-manager' ),
				'shortcode' => $shortcode,
				'edit_url'  => '',
			);
		}
	}
	return $placements;
}

function ko_link_manager_normalize_shortcode( $shortcode ) {
	$shortcode = html_entity_decode( $shortcode, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$shortcode = str_replace(
		array( '\\u0022', '\\u0027', '\\"', "\\'" ),
		array( '"', "'", '"', "'" ),
		$shortcode
	);
	return $shortcode;
}

function ko_link_manager_admin_assets( $hook ) {
	$screen = get_current_screen();
	if ( ! $screen || ( 'ko_link' !== $screen->post_type && 'toplevel_page_ko-link-manager' !== $hook ) ) {
		return;
	}

	wp_enqueue_style( 'ko-link-manager-admin', KO_LINK_MANAGER_URL . 'assets/css/admin.css', array(), KO_LINK_MANAGER_VERSION );
	wp_enqueue_style( 'ko-link-manager-destinations', KO_LINK_MANAGER_URL . 'assets/css/destinations.css', array( 'ko-link-manager-admin' ), KO_LINK_MANAGER_VERSION );
	wp_enqueue_style( 'ko-link-manager-sortable', KO_LINK_MANAGER_URL . 'assets/css/sortable.css', array( 'ko-link-manager-admin' ), KO_LINK_MANAGER_VERSION );
	wp_enqueue_style( 'ko-link-manager-list-table', KO_LINK_MANAGER_URL . 'assets/css/list-table.css', array( 'ko-link-manager-admin' ), KO_LINK_MANAGER_VERSION );
	wp_enqueue_style( 'ko-link-manager-guide', KO_LINK_MANAGER_URL . 'assets/css/guide.css', array( 'ko-link-manager-admin' ), KO_LINK_MANAGER_VERSION );
	wp_enqueue_style( 'ko-link-manager-reference', KO_LINK_MANAGER_URL . 'assets/css/reference.css', array( 'ko-link-manager-guide' ), KO_LINK_MANAGER_VERSION );
	wp_enqueue_script( 'ko-link-manager-admin', KO_LINK_MANAGER_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable' ), KO_LINK_MANAGER_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'ko_link_manager_admin_assets' );

function ko_link_manager_add_meta_boxes() {
	add_meta_box( 'ko_link_details', __( 'Link Details', 'ko-link-manager' ), 'ko_link_manager_details_meta_box', 'ko_link', 'normal', 'high' );
}
add_action( 'add_meta_boxes_ko_link', 'ko_link_manager_add_meta_boxes' );

function ko_link_manager_details_meta_box( $post ) {
	$url              = get_post_meta( $post->ID, '_ko_link_url', true );
	$target           = get_post_meta( $post->ID, '_ko_link_target', true );
	$order            = get_post_meta( $post->ID, '_ko_link_order', true );
	$destination_type = get_post_meta( $post->ID, '_ko_link_destination_type', true );
	$destination_id   = (int) get_post_meta( $post->ID, '_ko_link_destination_id', true );
	$destination_type = in_array( $destination_type, array( 'external', 'page', 'post', 'category' ), true ) ? $destination_type : 'external';
	$posts            = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
	wp_nonce_field( 'ko_link_manager_save_link', 'ko_link_manager_nonce' );
	?>
	<div class="ko-lm-fields">
		<p><label for="ko_link_destination_type"><strong><?php esc_html_e( 'Destination type', 'ko-link-manager' ); ?></strong></label></p>
		<select id="ko_link_destination_type" name="ko_link_destination_type" class="widefat">
			<option value="external" <?php selected( $destination_type, 'external' ); ?>><?php esc_html_e( 'External or custom URL', 'ko-link-manager' ); ?></option>
			<option value="page" <?php selected( $destination_type, 'page' ); ?>><?php esc_html_e( 'Page', 'ko-link-manager' ); ?></option>
			<option value="post" <?php selected( $destination_type, 'post' ); ?>><?php esc_html_e( 'Post', 'ko-link-manager' ); ?></option>
			<option value="category" <?php selected( $destination_type, 'category' ); ?>><?php esc_html_e( 'WordPress Category', 'ko-link-manager' ); ?></option>
		</select>
		<p class="ko-lm-destination" data-destination="external"><label for="ko_link_url"><strong><?php esc_html_e( 'Destination URL', 'ko-link-manager' ); ?></strong></label><input type="url" id="ko_link_url" name="ko_link_url" value="<?php echo esc_attr( $url ); ?>" placeholder="https://example.com or /about/" class="widefat"></p>
		<div class="ko-lm-destination" data-destination="page"><label for="ko_link_page_id"><strong><?php esc_html_e( 'Select a page', 'ko-link-manager' ); ?></strong></label><?php wp_dropdown_pages( array( 'name' => 'ko_link_page_id', 'id' => 'ko_link_page_id', 'selected' => 'page' === $destination_type ? $destination_id : 0, 'show_option_none' => __( 'Select a page', 'ko-link-manager' ), 'option_none_value' => '0', 'post_status' => 'publish', 'class' => 'widefat' ) ); ?></div>
		<div class="ko-lm-destination" data-destination="post"><label for="ko_link_post_id"><strong><?php esc_html_e( 'Select a post', 'ko-link-manager' ); ?></strong></label><select id="ko_link_post_id" name="ko_link_post_id" class="widefat"><option value="0"><?php esc_html_e( 'Select a post', 'ko-link-manager' ); ?></option><?php foreach ( $posts as $available_post ) : ?><option value="<?php echo esc_attr( $available_post->ID ); ?>" <?php selected( 'post' === $destination_type ? $destination_id : 0, $available_post->ID ); ?>><?php echo esc_html( $available_post->post_title ); ?></option><?php endforeach; ?></select></div>
		<div class="ko-lm-destination" data-destination="category"><label for="ko_link_category_id"><strong><?php esc_html_e( 'Select a WordPress category', 'ko-link-manager' ); ?></strong></label><?php wp_dropdown_categories( array( 'taxonomy' => 'category', 'name' => 'ko_link_category_id', 'id' => 'ko_link_category_id', 'selected' => 'category' === $destination_type ? $destination_id : 0, 'show_option_none' => __( 'Select a category', 'ko-link-manager' ), 'option_none_value' => '0', 'hide_empty' => false, 'hierarchical' => true, 'class' => 'widefat' ) ); ?></div>
		<p><label for="ko_link_order"><strong><?php esc_html_e( 'Display order', 'ko-link-manager' ); ?></strong></label><input type="number" id="ko_link_order" name="ko_link_order" value="<?php echo esc_attr( '' === $order ? 0 : $order ); ?>" step="1"><span class="description"><?php esc_html_e( 'Lower numbers appear first.', 'ko-link-manager' ); ?></span></p>
		<p><label><input type="checkbox" name="ko_link_target" value="_blank" <?php checked( $target, '_blank' ); ?>> <?php esc_html_e( 'Open in a new tab', 'ko-link-manager' ); ?></label></p>
	</div>
	<?php
}

function ko_link_manager_save_link( $post_id, $post ) {
	if ( ! isset( $_POST['ko_link_manager_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ko_link_manager_nonce'] ) ), 'ko_link_manager_save_link' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( 'ko_link' !== $post->post_type || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$destination_type = isset( $_POST['ko_link_destination_type'] ) ? sanitize_key( wp_unslash( $_POST['ko_link_destination_type'] ) ) : 'external';
	$destination_type = in_array( $destination_type, array( 'external', 'page', 'post', 'category' ), true ) ? $destination_type : 'external';
	$destination_id   = 0;
	if ( 'page' === $destination_type && isset( $_POST['ko_link_page_id'] ) ) {
		$destination_id = absint( $_POST['ko_link_page_id'] );
	} elseif ( 'post' === $destination_type && isset( $_POST['ko_link_post_id'] ) ) {
		$destination_id = absint( $_POST['ko_link_post_id'] );
	} elseif ( 'category' === $destination_type && isset( $_POST['ko_link_category_id'] ) ) {
		$destination_id = absint( $_POST['ko_link_category_id'] );
	}
	$url = isset( $_POST['ko_link_url'] ) ? esc_url_raw( trim( wp_unslash( $_POST['ko_link_url'] ) ) ) : '';
	update_post_meta( $post_id, '_ko_link_url', $url );
	update_post_meta( $post_id, '_ko_link_destination_type', $destination_type );
	update_post_meta( $post_id, '_ko_link_destination_id', $destination_id );
	update_post_meta( $post_id, '_ko_link_target', isset( $_POST['ko_link_target'] ) ? '_blank' : '_self' );
	update_post_meta( $post_id, '_ko_link_order', isset( $_POST['ko_link_order'] ) ? (int) $_POST['ko_link_order'] : 0 );
}
add_action( 'save_post_ko_link', 'ko_link_manager_save_link', 10, 2 );

function ko_link_manager_columns( $columns ) {
	return array(
		'cb'          => $columns['cb'],
		'title'       => __( 'Link', 'ko-link-manager' ),
		'ko_url'      => __( 'Destination', 'ko-link-manager' ),
		'taxonomy-ko_category' => __( 'Categories', 'ko-link-manager' ),
		'ko_target'   => __( 'Opens', 'ko-link-manager' ),
		'ko_order'    => __( 'Order', 'ko-link-manager' ),
		'date'        => $columns['date'],
	);
}
add_filter( 'manage_ko_link_posts_columns', 'ko_link_manager_columns' );

function ko_link_manager_column_content( $column, $post_id ) {
	if ( 'ko_url' === $column ) {
		echo '<code>' . esc_html( ko_link_manager_get_destination( $post_id ) ) . '</code>';
	} elseif ( 'ko_target' === $column ) {
		echo '_blank' === get_post_meta( $post_id, '_ko_link_target', true ) ? esc_html__( 'New tab', 'ko-link-manager' ) : esc_html__( 'Same tab', 'ko-link-manager' );
	} elseif ( 'ko_order' === $column ) {
		echo esc_html( (int) get_post_meta( $post_id, '_ko_link_order', true ) );
	}
}
add_action( 'manage_ko_link_posts_custom_column', 'ko_link_manager_column_content', 10, 2 );

function ko_link_manager_category_columns( $columns ) {
	$columns['ko_shortcode'] = __( 'Shortcode', 'ko-link-manager' );
	return $columns;
}
add_filter( 'manage_edit-ko_category_columns', 'ko_link_manager_category_columns' );

function ko_link_manager_category_column_content( $content, $column, $term_id ) {
	if ( 'ko_shortcode' !== $column ) {
		return $content;
	}
	$term = get_term( $term_id, 'ko_category' );
	if ( ! $term || is_wp_error( $term ) ) {
		return $content;
	}
	$shortcode = '[ko_links category="' . $term->slug . '"]';
	return '<code>' . esc_html( $shortcode ) . '</code> <button type="button" class="button button-small ko-lm-copy" data-copy="' . esc_attr( $shortcode ) . '">' . esc_html__( 'Copy', 'ko-link-manager' ) . '</button>';
}
add_filter( 'manage_ko_category_custom_column', 'ko_link_manager_category_column_content', 10, 3 );

function ko_link_manager_category_add_reference() {
	?>
	<div class="form-field ko-lm-shortcode-reference"><p><strong><?php esc_html_e( 'Shortcode reference', 'ko-link-manager' ); ?></strong></p><p><?php esc_html_e( 'After creating the category, its copy-ready shortcode will appear in the Categories list.', 'ko-link-manager' ); ?></p><code>[ko_links category="category-slug"]</code> <button type="button" class="button ko-lm-copy" data-copy="[ko_links category=&quot;category-slug&quot;]"><?php esc_html_e( 'Copy', 'ko-link-manager' ); ?></button></div>
	<?php
}
add_action( 'ko_category_add_form_fields', 'ko_link_manager_category_add_reference' );

function ko_link_manager_category_edit_reference( $term ) {
	$shortcode = '[ko_links category="' . $term->slug . '"]';
	?>
	<tr class="form-field ko-lm-shortcode-reference"><th scope="row"><?php esc_html_e( 'Shortcode', 'ko-link-manager' ); ?></th><td><code><?php echo esc_html( $shortcode ); ?></code> <button type="button" class="button ko-lm-copy" data-copy="<?php echo esc_attr( $shortcode ); ?>"><?php esc_html_e( 'Copy', 'ko-link-manager' ); ?></button><p class="description"><?php esc_html_e( 'Paste this shortcode wherever this link category should appear.', 'ko-link-manager' ); ?></p></td></tr>
	<?php
}
add_action( 'ko_category_edit_form_fields', 'ko_link_manager_category_edit_reference' );

function ko_link_manager_category_order_field( $term ) {
	$links = get_posts(
		array(
			'post_type'      => 'ko_link',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'meta_key'       => '_ko_link_order',
			'orderby'        => array( 'meta_value_num' => 'ASC', 'title' => 'ASC' ),
			'tax_query'      => array( array( 'taxonomy' => 'ko_category', 'field' => 'term_id', 'terms' => $term->term_id ) ),
		)
	);
	$links = ko_link_manager_apply_category_order( $links, $term->term_id );
	wp_nonce_field( 'ko_link_manager_save_category_order_' . $term->term_id, 'ko_link_category_order_nonce' );
	?>
	<tr class="form-field ko-lm-order-field"><th scope="row"><?php esc_html_e( 'Link order', 'ko-link-manager' ); ?></th><td>
		<?php if ( $links ) : ?>
			<p class="description"><?php esc_html_e( 'Drag links into the order they should appear for this category, then click Update.', 'ko-link-manager' ); ?></p>
			<ul class="ko-lm-sortable">
			<?php foreach ( $links as $link ) : ?>
				<li data-link-id="<?php echo esc_attr( $link->ID ); ?>"><span class="dashicons dashicons-menu ko-lm-drag" aria-hidden="true"></span><strong><?php echo esc_html( $link->post_title ); ?></strong><span class="ko-lm-link-status"><?php echo esc_html( get_post_status_object( $link->post_status )->label ); ?></span><a href="<?php echo esc_url( get_edit_post_link( $link->ID ) ); ?>"><?php esc_html_e( 'Edit', 'ko-link-manager' ); ?></a></li>
			<?php endforeach; ?>
			</ul>
			<input type="hidden" id="ko_link_category_order" name="ko_link_category_order" value="<?php echo esc_attr( implode( ',', wp_list_pluck( $links, 'ID' ) ) ); ?>">
		<?php else : ?>
			<p><?php esc_html_e( 'No links are assigned to this category yet.', 'ko-link-manager' ); ?></p>
		<?php endif; ?>
	</td></tr>
	<?php
}
add_action( 'ko_category_edit_form_fields', 'ko_link_manager_category_order_field', 20 );

function ko_link_manager_save_category_order( $term_id ) {
	if ( ! current_user_can( 'manage_categories' ) || ! isset( $_POST['ko_link_category_order_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ko_link_category_order_nonce'] ) ), 'ko_link_manager_save_category_order_' . $term_id ) ) {
		return;
	}
	if ( ! isset( $_POST['ko_link_category_order'] ) ) {
		return;
	}
	$submitted = array_values( array_unique( array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['ko_link_category_order'] ) ) ) ) ) ) );
	$assigned  = get_objects_in_term( $term_id, 'ko_category' );
	if ( is_wp_error( $assigned ) ) {
		return;
	}
	$assigned = array_map( 'absint', $assigned );
	update_term_meta( $term_id, '_ko_link_category_order', array_values( array_intersect( $submitted, $assigned ) ) );
}
add_action( 'edited_ko_category', 'ko_link_manager_save_category_order' );

function ko_link_manager_reconcile_category_order( $object_id, $terms, $term_taxonomy_ids, $taxonomy, $append, $old_term_taxonomy_ids ) {
	if ( 'ko_category' !== $taxonomy || 'ko_link' !== get_post_type( $object_id ) ) {
		return;
	}

	$term_ids = wp_get_object_terms( $object_id, 'ko_category', array( 'fields' => 'ids' ) );
	if ( is_wp_error( $term_ids ) ) {
		return;
	}
	foreach ( $old_term_taxonomy_ids as $term_taxonomy_id ) {
		$old_term = get_term_by( 'term_taxonomy_id', $term_taxonomy_id, 'ko_category' );
		if ( $old_term && ! is_wp_error( $old_term ) ) {
			$term_ids[] = (int) $old_term->term_id;
		}
	}
	$term_ids = array_values( array_unique( array_map( 'absint', $term_ids ) ) );

	foreach ( $term_ids as $term_id ) {
		$assigned_ids = get_posts(
			array(
				'post_type'      => 'ko_link',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => '_ko_link_order',
				'orderby'        => array( 'meta_value_num' => 'ASC', 'title' => 'ASC' ),
				'tax_query'      => array( array( 'taxonomy' => 'ko_category', 'field' => 'term_id', 'terms' => $term_id ) ),
			)
		);
		$assigned_ids = array_map( 'absint', $assigned_ids );
		$saved_order  = get_term_meta( $term_id, '_ko_link_category_order', true );
		$saved_order  = is_array( $saved_order ) ? array_map( 'absint', $saved_order ) : array();
		$preserved    = array_values( array_intersect( $saved_order, $assigned_ids ) );
		$reconciled   = array_merge( $preserved, array_values( array_diff( $assigned_ids, $preserved ) ) );

		if ( $reconciled !== $saved_order ) {
			update_term_meta( $term_id, '_ko_link_category_order', $reconciled );
		}
	}
}
add_action( 'set_object_terms', 'ko_link_manager_reconcile_category_order', 20, 6 );

function ko_link_manager_apply_category_order( $posts, $term_id ) {
	$saved_order = get_term_meta( $term_id, '_ko_link_category_order', true );
	if ( ! is_array( $saved_order ) || ! $saved_order ) {
		return $posts;
	}
	$positions = array_flip( array_map( 'intval', $saved_order ) );
	$original  = array_flip( array_map( 'intval', wp_list_pluck( $posts, 'ID' ) ) );
	usort(
		$posts,
		function ( $first, $second ) use ( $positions, $original ) {
			$first_saved  = isset( $positions[ $first->ID ] );
			$second_saved = isset( $positions[ $second->ID ] );
			if ( $first_saved && $second_saved ) {
				return $positions[ $first->ID ] <=> $positions[ $second->ID ];
			}
			if ( $first_saved !== $second_saved ) {
				return $first_saved ? -1 : 1;
			}
			return $original[ $first->ID ] <=> $original[ $second->ID ];
		}
	);
	return $posts;
}

function ko_link_manager_get_destination( $post_id ) {
	$type      = get_post_meta( $post_id, '_ko_link_destination_type', true );
	$object_id = (int) get_post_meta( $post_id, '_ko_link_destination_id', true );
	if ( in_array( $type, array( 'page', 'post' ), true ) && $object_id ) {
		return get_permalink( $object_id ) ?: '';
	}
	if ( 'category' === $type && $object_id ) {
		$url = get_category_link( $object_id );
		return is_wp_error( $url ) ? '' : $url;
	}
	$url = get_post_meta( $post_id, '_ko_link_url', true );
	if ( '' !== $url && null === wp_parse_url( $url, PHP_URL_SCHEME ) ) {
		$url = home_url( '/' . ltrim( $url, '/' ) );
	}
	return $url;
}

function ko_links_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'category' => '', 'title' => '' ), $atts, 'ko_links' );
	$category = null;
	$args = array(
		'post_type'      => 'ko_link',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_key'       => '_ko_link_order',
		'orderby'        => array( 'meta_value_num' => 'ASC', 'title' => 'ASC' ),
		'no_found_rows'  => true,
	);

	if ( '' !== trim( $atts['category'] ) ) {
		$category = is_numeric( $atts['category'] ) ? get_term( (int) $atts['category'], 'ko_category' ) : get_term_by( 'slug', sanitize_title( $atts['category'] ), 'ko_category' );
		if ( ! $category || is_wp_error( $category ) ) {
			return '<p class="ko-links-empty">' . esc_html__( 'No links found.', 'ko-link-manager' ) . '</p>';
		}
		if ( '' === trim( $atts['title'] ) ) {
			$atts['title'] = $category->name;
		}
		$args['tax_query'] = array( array( 'taxonomy' => 'ko_category', 'field' => 'term_id', 'terms' => $category->term_id ) );
	}

	$links = new WP_Query( $args );
	if ( $category ) {
		$links->posts = ko_link_manager_apply_category_order( $links->posts, $category->term_id );
	}
	if ( ! $links->have_posts() ) {
		return '<p class="ko-links-empty">' . esc_html__( 'No links found.', 'ko-link-manager' ) . '</p>';
	}

	$output = '' !== trim( $atts['title'] ) ? '<h4 class="ko-links-title">' . esc_html( $atts['title'] ) . '</h4>' : '';
	$output .= '<ul class="ko-links">';
	while ( $links->have_posts() ) {
		$links->the_post();
		$url    = ko_link_manager_get_destination( get_the_ID() );
		$target = '_blank' === get_post_meta( get_the_ID(), '_ko_link_target', true ) ? '_blank' : '_self';
		if ( '' === $url ) {
			continue;
		}
		$rel = '_blank' === $target ? ' rel="noopener"' : '';
		$output .= '<li><a href="' . esc_url( $url ) . '" target="' . esc_attr( $target ) . '"' . $rel . '>' . esc_html( get_the_title() ) . '</a></li>';
	}
	wp_reset_postdata();
	return $output . '</ul>';
}
add_shortcode( 'ko_links', 'ko_links_shortcode' );
