<?php
namespace {
    if (!defined('ABSPATH')) // wp-config.php defines an ABSPATH variable, this trap ensures that only WP can use this code.
        exit;
}

namespace membership_type{


    if (!function_exists('membership_type\new_post_type_membership')) {
        add_action('wp_enqueue_scripts', 'membership_type\plugin_scripts', 0); // action, array, priority ( 0 lowest, 10 normal, 10+ higher)
        add_action('admin_init', 'membership_type\plugin_meta_box'); // admin_init is triggered before any other hook when a user accesses the admin area.
        add_action('admin_enqueue_scripts', 'membership_type\plugin_admin_scripts');
        add_action('save_post', 'membership_type\plugin_save_post', 10, 2);        

        function plugin_admin_scripts($page)
        {
            if ('post.php' != $page && 'post-new.php' != $page) {
                return;
            }
            wp_enqueue_media();
        }

        // function new_post_type_membership_posting()
        // {
        //     $labels = array(
        //         'name' => __('Memberships'),
        //         'singular_name' => __('Membership'),
        //         'add_new' => 'Add New Membership',
        //         'add_new_item' => 'Add New Membership',
        //         'edit_item' => 'Edit Membership',
        //         'view_item' => 'View Membership',
        //         'search_items' => 'Search Memberships',
        //         'not_found' => 'No Memberships found',
        //         'not_found_in_trash' => 'No Memberships found in Trash',
        //         'parent' => 'Parent Membership'
        //     );

        //     register_post_type('gs_membership',
        //         array(
        //             'labels' => $labels,
        //             'description' => 'Membership details',
        //             'public' => true,
        //             'has_archive' => false,
        //             'rewrite' => array('slug' => 'membership'),
        //             'supports' => array( 'title', 'page-attributes' ),
        //             'show_in_menu' => true,
        //             'menu_icon' => get_template_directory_uri() . '/images/images-alt2.svg',
        //             'menu_position' => 61,
        //         )
        //     );
        // }
        // add_action('init', 'membership_type\new_post_type_membership_posting');

        // Alternatively from ACF php export:
        add_action( 'init', function() {
            register_post_type( 'membership', array(
            'labels' => array(
                'name' => 'Memberships',
                'singular_name' => 'Membership',
                'menu_name' => 'Memberships',
                'all_items' => 'All Memberships',
                'edit_item' => 'Edit Membership',
                'view_item' => 'View Membership',
                'view_items' => 'View Memberships',
                'add_new_item' => 'Add New Membership',
                'add_new' => 'Add New Membership',
                'new_item' => 'New Membership',
                'parent_item_colon' => 'Parent Membership:',
                'search_items' => 'Search Memberships',
                'not_found' => 'No memberships found',
                'not_found_in_trash' => 'No memberships found in Trash',
                'archives' => 'Membership Archives',
                'attributes' => 'Membership Attributes',
                'insert_into_item' => 'Insert into membership',
                'uploaded_to_this_item' => 'Uploaded to this membership',
                'filter_items_list' => 'Filter memberships list',
                'filter_by_date' => 'Filter memberships by date',
                'items_list_navigation' => 'Memberships list navigation',
                'items_list' => 'Memberships list',
                'item_published' => 'Membership published.',
                'item_published_privately' => 'Membership published privately.',
                'item_reverted_to_draft' => 'Membership reverted to draft.',
                'item_scheduled' => 'Membership scheduled.',
                'item_updated' => 'Membership updated.',
                'item_link' => 'Membership Link',
                'item_link_description' => 'A link to a membership.',
            ),
            'public' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-admin-post',
            'supports' => array(
                0 => 'title',
            ),
            'taxonomies' => array(
                0 => 'committee',
                1 => 'committee-year',
            ),
            'delete_with_user' => false,
        ) );
        } );



        function remove_tax_meta_boxes () {
            remove_meta_box( 'tagsdiv-committee', 'membership', 'side' );
            remove_meta_box( 'tagsdiv-committee-year', 'membership', 'side' );
        }
        add_action( 'admin_menu' , 'membership_type\remove_tax_meta_boxes' );


        function plugin_meta_box() {
            add_meta_box(
                'membership',                     // is the required HTML id attribute
                'Membership Details',                     // is the text visible in the heading of the meta box section
                'membership_type\plugin_display_details_meta_box',  // is the callback which renders the contents of the meta box
                'membership',                     // is the name of the custom post type where the meta box will be displayed
                'normal',                           // defines the part of the page where the edit screen section should be shown
                'high'                              // defines the priority within the context where the boxes should show
            );
        }
        function valueFromMeta( $meta, $key ) {
            if( !empty( $meta[ $key ] ) )
                return $meta[ $key ][0];
            else
                return '';
        }
        function valueFromMetaArray( $meta, $key ) {
            if( !empty( $meta[ $key ] ) )
                return explode( ',', $meta[ $key ][0] );
            else
                return array();
        }
        function membership_data( $id ) {
            $meta   = get_post_meta( $id );
            $data  = array();
            $taxonomies = get_post_taxonomies( $id );
            foreach ($taxonomies as $taxonomy) {
                $current_terms      = wp_get_post_terms($id, $taxonomy, ['fields' => 'all_with_object_id']);
                $data[$taxonomy]    = esc_html( $current_terms[0]->slug ?? (( !empty( $meta[$taxonomy]  ) )? $meta[$taxonomy][0]  : '') );
            }
            $data['role'] = esc_html( (( !empty( $meta['role']  ) )? $meta['role'][0]  : '') );

            return $data;
        }

        add_action( 'admin_notices', 'membership_type\gs_save_taxonomy_error_notice' );
        function gs_save_taxonomy_error_notice() {
            $messages = get_transient('gs_save_taxonomy_error_' . get_current_user_id()) ?? [];
            if ( ! $messages ) return;

            delete_transient('gs_save_taxonomy_error_' . get_current_user_id());
            ?>
            <div class="notice notice-error is-dismissible">
                <p><strong>The post was saved, but some taxonomies could not be updated:</strong></p>
                <ul>
                    <?php foreach ( $messages as $message ) { ?>
                        <li><?= esc_html( $message ); ?></li>
                    <?php } ?>
                </ul>
            </div>
            <?php
        }

        add_action( 'admin_notices', 'membership_type\gs_create_membership_error_notice' );
        function gs_create_membership_error_notice() {
            $messages = get_transient('gs_create_membership_error_' . get_current_user_id()) ?? [];
            if ( ! $messages ) return;

            delete_transient('gs_create_membership_error_' . get_current_user_id());
            ?>
            <div class="notice notice-error is-dismissible">
                <p><strong>The following posts could not be saved:</strong></p>
                <ul>
                    <?php foreach ( $messages as $message ) { ?>
                        <li><?= esc_html( $message ); ?></li>
                    <?php } ?>
                </ul>
            </div>
            <?php
        }

        add_action( 'admin_notices', 'membership_type\gs_copy_memberships_success_notice' );
        function gs_copy_memberships_success_notice() {
            $messages = get_transient('gs_copy_memberships_success_' . get_current_user_id()) ?? [];
            if ( ! $messages ) return;

            delete_transient('gs_copy_memberships_success_' . get_current_user_id());
            ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Memberships successfully processed for the following councils and corresponding target year:</strong></p>
                <ul>
                    <?php foreach ( $messages as $message ) { ?>
                        <li><?= esc_html( $message ); ?></li>
                    <?php } ?>
                </ul>
            </div>
            <?php
        }

        // Since v2.0: function save_field returns an error string (default = '' when no save error)
        function save_field($id, $post_key, $meta_key, $default = '') {
            $taxonomies = get_post_taxonomies( $id );
            if (isset($_POST[$post_key]) && $_POST[$post_key] != '') {
                if ( in_array($meta_key, $taxonomies) ) {
                    $tax_term = get_term_by('slug', $_POST[$post_key], $meta_key);
                    $result = wp_set_object_terms($id, [$tax_term->term_id], $meta_key);
                    // return array('error' => 'TEST ERROR: The admin notice is working.',);
                    if ( is_wp_error( $result ) ) {
                        return array(
                            'error' => sprintf('%s taxonomy: %s', get_taxonomy( $meta_key )->labels->singular_name, $result->get_error_message()),
                        );
                    }
                    return $tax_term->name;
                }
                else {
                    update_post_meta($id, $meta_key, trim($_POST[$post_key]));
                    return $_POST[$post_key];
                }
            } else {
                update_post_meta($id, $meta_key, $default);
                return $default;
            }
        }


        function save_array($id, $post_key, $meta_key) {
            if (isset($_POST[$post_key]) && is_array($_POST[$post_key]) && !empty($_POST[$post_key]))
                update_post_meta($id, $meta_key, implode( ',', $_POST[$post_key] ) );
        }

        function plugin_save_post($id, $post) {
            if ($post->post_type == 'membership') {
                $errors = [];
                
                $role = get_field('display_role');
                $committee = save_field( $id, 'committee', 'committee');
                if (is_array($committee)) {
                    $errors[] = $committee['error'];
                    $committee = '';
                }
                $year = save_field( $id, 'committee-year', 'committee-year');
                if (is_array($year)) {
                    $errors[] = $year['error'];
                    $year = '';
                }

                $errors = array_filter ( $errors );
                if ( ! empty( $errors ) ) {
                    set_transient('gs_save_taxonomy_error_' . get_current_user_id(), $errors, 60);
                }

                remove_action( 'save_post', 'membership_type\plugin_save_post'  );
                wp_update_post(array(
                    'ID' => $id,
                    'post_title' => $committee . ' - ' . $role . ', ' . $year,
                    'post_name' =>  $committee . '-' . $role . '-' . $year,
                ));
                add_action('save_post', 'membership_type\plugin_save_post', 10, 2);
            }
        }
        function plugin_display_details_meta_box($post) {
            $data = membership_data( $post->ID );
            ?>
            <div>
                <style>
                    #member_details_table th {
                        text-align: right;
                        padding: 10px  10px 0 25px;
                        width: 1%;
                        white-space: nowrap;
                    }
                    #member_details_table select {
                        width: 100%;
                    }
                </style>
                <table id="member_details_table" width="100%">
                    <tr>
                        <th><label for="committee">Committee:</label></th>
                        <td>
                            <select name="committee" id="committee">
								<option></option>
                                <?php
                                    $taxonomy = 'committee';
                                    $terms = get_terms([
                                        'taxonomy' => $taxonomy,
                                        'hide_empty' => false,
                                        'orderBy'       => 'name',
                                        'order'         => 'ASC',
                                    ]);
                                    foreach ($terms as $term) {
                                        echo '<option value="' . $term->slug . '" ' . selected($data[$taxonomy],$term->slug) . '>' . esc_html($term->name) . '</option>';
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="committee-year">Council Serving Year:</label></th>
                        <td>
                            <select name="committee-year" id="committee-year">
								<option></option>
                                <?php
                                    $taxonomy = 'committee-year';
                                    $terms = get_terms([
                                        'taxonomy' => $taxonomy,
                                        'hide_empty' => false,
                                        'orderBy'       => 'name',
                                        'order'         => 'DESC',
                                    ]);
                                    foreach ($terms as $term) {
                                        echo '<option value="' . $term->slug . '" ' . selected($data[$taxonomy],$term->slug) . '>' . esc_html($term->name) . '</option>';
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        <?php
        }

        function select_categories( $tax, $name, $data, $usingSelected = true ) {
            $my_terms = get_terms( $tax, array(
                'orderby' => 'name',
                'order' => 'DESC',
                'hide_empty' => 0,
            ));
            echo '<select name="'. $name .'" id="'. $name .'">';
            echo '<option></option>';
            foreach ( $my_terms as $tax_term ) {
                $selected = '';
                if( $usingSelected )
                    $selected = !empty( $data ) && in_array( $tax_term->name, $data ) ? ' selected="selected" ' : '';
                echo '<option '. $selected .'>'. $tax_term->name .'</option>';
            }
            echo '</select>';
        }

        function plugin_scripts()
        {
            if (!is_admin()) {
                wp_deregister_script('jquery');
                wp_register_script('jquery', ("http://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"), false, '1.11.3');
                wp_enqueue_script('jquery');
            }
        }

    /*
        The following section creates a membership admin page for year rollover membership automation
    */

        function get_previous_committee_year_slug( $year_slug ) {
            // Example: 2026
            if ( preg_match( '/^\d{4}$/', $year_slug ) ) {
                return (string) ( (int) $year_slug - 1 );
            }

            // Example: 2026-2027
            if ( preg_match( '/^(\d{4})-(\d{4})$/', $year_slug, $matches ) ) {

                $start = (int) $matches[1];
                $end   = (int) $matches[2];

                return ($start - 1) . '-' . ($end - 1);
            }
            return false;
        }

        /**
         * Create a Membership post and populate the ACF field and taxonomies.
         *
         * @param string $content    Post content.
         * @param string $role       Value for the ACF field.
         *
         * @return int|WP_Error Post ID on success, WP_Error on failure.
         */
        function create_new_membership( $committee, $year, $role ) {
            if ( ! $role ) {
                return new \WP_Error( 'missing_parameter', 'The role parameter is required.' );
            }

            // Temporarily disable custom post save function to avoid null references
            remove_action( 'save_post', 'membership_type\plugin_save_post' );

            // Validate $committee and $year tax terms
            $committee_term = get_term_by( 'slug', $committee, 'committee' );
            $year_term = get_term_by( 'slug', $year, 'committee-year' );
            if ( ! $committee_term || ! $year_term ) {
                return new \WP_Error( 'invalid_term', 'One or more taxonomy terms could not be found.' );
            }

            $post_id = wp_insert_post(
                array(
                    'post_title'   => $committee_term->name . ' - ' . $role . ', ' . $year,
                    'post_content' => '',
                    'post_type'    => 'membership',
                    'post_status'  => 'publish',
                ),
                true // Return WP_Error on failure.
            );

            if ( is_wp_error( $post_id ) ) {
                return $post_id;
            }

            // Associate tax terms and update ACF field
            wp_set_object_terms( $post_id, array( (int) $committee_term->term_id ), 'committee' );
            wp_set_object_terms( $post_id, array( (int) $year_term->term_id ), 'committee-year' );
            update_field( 'display_role', $role, $post_id );

            // Restore Membership page custom post save function
            add_action('save_post', 'membership_type\plugin_save_post', 10, 2);
            return $post_id;
        }

        function copy_committee_memberships($committee_slug, $source_year, $target_year) {

            $memberships = get_posts([
                'post_type'      => 'membership',
                'posts_per_page' => -1,
                'tax_query' => [
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'committee',
                        'field'    => 'slug',
                        'terms'    => $committee_slug,
                    ],
                    [
                        'taxonomy' => 'committee-year',
                        'field'    => 'slug',
                        'terms'    => $source_year,
                    ],
                ],
            ]);

            // Make the copy function idempotent
            $existing_memberships = get_posts([
                'post_type'      => 'membership',
                'posts_per_page' => -1,
                'tax_query' => [
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'committee',
                        'field'    => 'slug',
                        'terms'    => $committee_slug,
                    ],
                    [
                        'taxonomy' => 'committee-year',
                        'field'    => 'slug',
                        'terms'    => $target_year,
                    ],
                ],
            ]);
            if ( !empty($existing_memberships) ) {
                $memberships = array_filter( $memberships, function ($membership) use ($existing_memberships) {
                    $role = get_field( 'display_role', $membership->ID );
                    $found = array_filter(
                        $existing_memberships, function ($post) use ($role) {
                        return $role === get_field( 'display_role', $post->ID );
                    });
                    return empty( $found );
                });
            }

            // Use filtered source list to create new memberships
            $errors = [];
            foreach ( $memberships as $key => $membership ) {
                $role = get_field( 'display_role', $membership->ID );
                $post_id = create_new_membership ( $committee_slug, $target_year, $role);
                if ( is_wp_error( $post_id ) ) {
                    $errors[] = sprintf('%s - %s, %s: %s', $committee_slug, $role, $target_year, $post_id->get_error_message());
                    unset( $memberships[$key] );
                }
            }

            if ( ! empty( $errors ) ) {
                set_transient('gs_create_membership_error_' . get_current_user_id(), $errors, 60);
            }

            return $memberships;

        }

        function copy_memberships() {
            if ( ! empty( $_POST ) && check_admin_referer( 'copy_memberships' ) ) {
                set_transient('gs_copy_memberships_clicked_' . get_current_user_id(), 'True', 60);
                $committee_years = $_POST['committee_years'] ?? '[]';

                if ( ! empty( $committee_years ) ) {
                    $processed = [];
                    foreach ( $committee_years as $committee_slug => $target_year ) {
                        if ( empty( $target_year ) ) {
                            continue;
                        }
                        $source_year = get_previous_committee_year_slug( $target_year );
                        $processed[$committee_slug] = copy_committee_memberships( $committee_slug, $source_year, $target_year );
                    }

                    if ( ! empty( $processed ) ) {
                        foreach ($processed as $committee_slug => $posts_array) {
                            $processed[$committee_slug] = array_map( function ($post) {
                                return (object) [
                                    'id' => $post->ID,
                                    'title' => $post->post_title,
                                    'date_published' => $post->post_date
                                ];
                            }, $posts_array);
                        }
                        set_transient('gs_copy_memberships_processed_' . get_current_user_id(), $processed, 60);
                    }

                    // Use Committee tax name in notification and only show if at least one membership successfully copied
                    $terms = get_terms(['taxonomy' => 'committee', 'hide_empty' => false]);
                    $committee_slugs = array_keys(array_filter($committee_years));
                    if (!empty($terms)) {
                        $terms = array_filter( $terms, function ($term) use ( $committee_slugs, $processed ) {
                            return in_array($term->slug, $committee_slugs) && !empty($processed[$term->slug]);
                        });
                    }
                    set_transient(
                        'gs_copy_memberships_success_' . get_current_user_id(),
                        array_map(function($term) use ( $committee_years) {
                            return $term->name . ': ' . $committee_years[$term->slug];
                        }, $terms),
                        60
                    );
                }
                $url = admin_url( 'edit.php?post_type=membership&page=membership-admin' );
                wp_safe_redirect( add_query_arg( 'copied', 1, $url ) );
                exit;
            }
        }
        add_action('admin_post_copy_memberships', 'membership_type\copy_memberships');

        function register_membership_admin_page() {
            add_submenu_page(
                'edit.php?post_type=membership',
                'Membership Admin',
                'Membership Admin',
                'manage_options',
                'membership-admin',
                'membership_type\render_membership_admin_page'
            );
        }
        add_action( 'admin_menu', 'membership_type\register_membership_admin_page' );

        function render_membership_admin_page() {
            $committees = get_terms([
                'taxonomy'   => 'committee',
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ]);

            $committee_labels = !empty($committees) ? array_column($committees, 'name', 'slug') : [];

            $years = get_terms([
                'taxonomy'   => 'committee-year',
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'DESC',
            ]);

            ?>

            <div class="wrap">
                <h1>Membership Admin</h1>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="copy_memberships">
                    <?php wp_nonce_field( 'copy_memberships' ); ?>
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>Committee</th>
                                <th>Target Year</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $committees as $committee ) { ?>
                            <tr>
                                <td>
                                    <?= esc_html( $committee->name ); ?>
                                </td>
                                <td>
                                    <select
                                        name="committee_years[<?= esc_attr( $committee->slug ); ?>]">
                                        <option value="">
                                            -- Skip Committee --
                                        </option>
                                        <?php foreach ( $years as $year ) { ?>
                                            <option value="<?php echo esc_attr( $year->slug ); ?>">
                                                <?php echo esc_html( $year->name ); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <p>
                        <button class="button button-primary">
                            Copy Memberships
                        </button>
                    </p>
                </form>
            </div>
            <?php $processed_memberships = get_transient('gs_copy_memberships_processed_' . get_current_user_id()) ?? [];
            if (get_transient('gs_copy_memberships_clicked_' . get_current_user_id()) ?? '') {
                delete_transient('gs_copy_memberships_clicked_' . get_current_user_id());
                if (!$processed_memberships) {
                    echo '<p>No posts copied.</p>';
                }
                else {
                    ?>
                    <div class="wrap">
                        <h1>Memberships Copied:</h1>
                        <?php foreach ($processed_memberships as $committee_slug => $memberships) { ?>
                        <h2><?= $committee_labels[$committee_slug] ?? $committee_slug; ?></h2>
                        <?php if (empty($memberships)) {
                            echo '<p>No uncopied memberships exist in prior year for this council.</p>';
                        }
                        else { ?>
                            <table class="widefat">
                                <thead>
                                    <tr>
                                        <th>Post ID</th>
                                        <th>Membership Title</th>
                                        <th>Date Published</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($memberships as $membership) { ?>
                                    <tr><td><?= $membership->id; ?></td>
                                        <td><?= $membership->title; ?></td>
                                        <td><?= $membership->date_published; ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } } ?>
                    </div>
                    <?php
                }
            }
            delete_transient('gs_copy_memberships_processed_' . get_current_user_id());
        }
    }
}
