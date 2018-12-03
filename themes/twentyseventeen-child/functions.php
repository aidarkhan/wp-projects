<?php
/**
 * Twentyseventeen child theme
 */
add_action('wp_enqueue_scripts', 'enqueue_parent_styles');
function enqueue_parent_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}

require get_stylesheet_directory() . '/inc/metaboxes.php';

/**Custom metabox for post*/
add_action('add_meta_boxes', 'my_new_metabox');
function my_new_metabox()
{
    add_meta_box('contact_email', 'Contact Email', 'contact_email_callback', 'post');
}

function contact_email_callback($post)
{
    wp_nonce_field(basename( __FILE__ ), 'contact_email_metabox_nonce');
    $value = get_post_meta( $post->ID, 'email_value', true );

    $html = '<label for="contact_email_field">User Email</label> ';
    $html .= '<input id="contact_email_field" type="email" value="' . esc_attr($value) . '" name="contact_email_field" size="25">';

    echo $html;
}

add_action('save_post', 'true_save_box_data');
function true_save_box_data($post_id)
{
    if (!isset( $_POST['contact_email_metabox_nonce']) || !wp_verify_nonce( $_POST['contact_email_metabox_nonce'], basename( __FILE__ ))) {
        return $post_id;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
    if (!current_user_can( 'edit_post', $post_id )) {
        return $post_id;
    }
    $post = get_post($post_id);
    if ($post->post_type == 'post') {
        update_post_meta($post_id, 'email_value', esc_attr($_POST['contact_email_field']));
    }
    return $post_id;
}

/**Custom fields for taxonomy and category */
add_action( 'category_add_form_fields', 'taxonomy_add_new_meta_field', 10, 2 ); //Вместо "category" любая другая кастомная таксономия
function taxonomy_add_new_meta_field()
{
    $html = '
    <div class="form-field">
        <label for="custom_term_meta">Демо-поле</label>
        <input type="text" name="custom_term_meta" id="custom_term_meta" value="">
        <p class="description">Enter a value for this field</p>
    </div>';
    echo $html;
}

add_action( 'category_edit_form_fields', 'taxonomy_edit_meta_field', 10, 2 );
function taxonomy_edit_meta_field($term)
{
    $term_meta = get_term_meta($term->term_id, 'custom_term_meta', true) ?: '';

    $html = '
    <tr class="form-field">
        <th scope="row" valign="top"><label for="custom_term_meta">Демо-поле</label></th>
        <td>
            <input type="text" name="custom_term_meta" id="custom_term_meta" value="' . $term_meta . '">
            <p class="description">Укажите тут значение</p>
        </td>
    </tr>';
    echo $html;
}

add_action( 'edited_category', 'save_taxonomy_custom_meta', 10, 2 );
add_action( 'create_category', 'save_taxonomy_custom_meta', 10, 2 );
function save_taxonomy_custom_meta($term_id)
{
    if (!isset($_POST['custom_term_meta']) || !current_user_can('edit_term', $term_id)) {
        return false;
    }
    // Verify nonce
    if (isset($_POST['_wpnonce']) && !wp_verify_nonce($_POST['_wpnonce'], 'update-tag_' . $term_id)) {
        return false;
    }
    if (isset($_POST['_wpnonce_add-tag']) && !wp_verify_nonce($_POST['_wpnonce_add-tag'], 'add-tag')) {
        return false;
    }
    // Remove slashes from a string
    $extra = wp_unslash($_POST['custom_term_meta']);

    if(empty($extra)) {
        delete_term_meta($term_id, 'custom_term_meta');
    } else {
        update_term_meta($term_id, 'custom_term_meta', $_POST['custom_term_meta']);
    }
    return $term_id;
}

/**Off WP ftp  */
define('FS_METHOD', 'direct');

/**Add page to console */
add_action('admin_menu', 'newPage');
function newPage()
{
    add_submenu_page('options-general.php', 'test_page_title', 'test_menu_title', 'edit_pages', 'test_menu_slug', 'test_function_test_page');
    function test_function_test_page()
    {
        echo '<h1>TEST PAGE</h2>' . '<p>This is Content</p>';
    }
}
