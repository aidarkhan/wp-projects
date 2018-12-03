<?php
/**
 * Twentyseventeen child theme
 */

 add_action('wp_enqueue_scripts', 'enqueue_parent_styles');
 function enqueue_parent_styles()
 {
     wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
 }
 
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
    // проверяем, пришёл ли запрос со страницы с метабоксом
    if (!isset( $_POST['contact_email_metabox_nonce']) || !wp_verify_nonce( $_POST['contact_email_metabox_nonce'], basename( __FILE__ ))) {
        return $post_id;
    }
    // проверяем, является ли запрос автосохранением
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
    // проверяем, права пользователя, может ли он редактировать записи
    if (!current_user_can( 'edit_post', $post_id )) {
        return $post_id;
    }
    // теперь также проверим тип записи	
    $post = get_post($post_id);
    if ($post->post_type == 'post') {
        update_post_meta($post_id, 'email_value', esc_attr($_POST['contact_email_field']));
    }
    return $post_id;
}

/**Custom metabox for term */
// add_action( 'category_edit_form_fields', 'my_term_metabox');
// function my_term_metabox()
// {
    
// }

// Создание метаполя

add_action('init', 'wpm_category_register_meta');

function wpm_category_register_meta() {
    register_meta('term', 'details', 'wpm_sanitize_details');
}

function wpm_sanitize_details( $details ) {
    return wp_kses_post( $details );
}

// Создание метабокса в категории товара

add_action('category_edit_form_fields', 'wpm_category_edit_details_meta');

function wpm_category_edit_details_meta($term)
{
    $category_details = get_term_meta($term->term_id, 'details', true);
    if (!$category_details) {
        $category_details = '';
    }
    $settings = array('textarea_name' => 'wpm-category-details');

    wp_nonce_field(basename(__FILE__), 'wpm_category_details_nonce');
    wp_editor(wpm_sanitize_details($category_details), 'category_details', $settings);

    $html = '
    <tr class="form-field">
        <th scope="row" valign="top"><label for="wpm-category-details">Наименование (им.п.)</label></th>
        <td>
            <p class="description">Вписать название категории в родительном падеже</p>
        </td>
    </tr>';
    echo $html;
}

// Сохранение данных метаполя

add_action('create_category', 'wpm_category_details_meta_save');
add_action('edit_category', 'wpm_category_details_meta_save');

function wpm_category_details_meta_save($term_id)
{
    if (!isset( $_POST['wpm_category_details_nonce']) || !wp_verify_nonce($_POST['wpm_category_details_nonce'], basename(__FILE__))) {
        return;
    }

    $old_details = get_term_meta($term_id, 'details', true);
    $new_details = isset($_POST['wpm-category-details']) ? $_POST['wpm-category-details'] : '';

    if ($old_details && '' === $new_details) {
        delete_term_meta($term_id, 'details');
    } else if ($old_details !== $new_details) {
        update_term_meta($term_id, 'details', wpm_sanitize_details($new_details));
    }
}