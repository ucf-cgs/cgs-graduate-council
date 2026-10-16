<?php


namespace {
  if (!defined('ABSPATH')) // wp-config.php defines an ABSPATH variable, this trap ensures that only WP can use this code.
  exit;
}

namespace gs_migration_settings {

  function gs_migration_admin_styles() {
    wp_enqueue_style( 'gs-migration-admin-style', get_template_directory_uri() . '/css/migration.css', array(), null );
  }
  add_action('admin_enqueue_scripts', 'gs_migration_settings\gs_migration_admin_styles');

  function gs_add_migration_settings_page() {
    add_menu_page(
      'GS Migration Settings',
      'GS Migration',
      'manage_options',
      'gs-council-migration-settings',
      'gs_migration_settings\gs_render_migration_settings_page',
      'dashicons-update',
      100
    );
  }

  function gs_migration_get_post_types() {
    return ['gs_meetings', 'gs_file', 'gs_member'];
  }

  function gs_render_migration_settings_page() {
    if ( ! is_super_admin() ) {
      echo '<div class="wrap">';
      echo '<h1>This page is only for Multisite Network Administrators</h1>';
      echo '</div>';
      return;
    }

    /*
    * Run Migration
    */
    if (
      isset( $_POST['gs_migrate_meta_to_taxonomies'] ) &&
      check_admin_referer( 'gs_migration_nonce' )
    ) {
      gs_migrate_meta_to_taxonomies();
    }

    /*
    * Load Summary
    */
    $migration_data = get_option(
      'gs_migration_affected_records',
      [
        'migration_date' => '',
        'record_count'   => 0,
        'records'        => [],
      ]
    );
    $migration_date = $migration_data['migration_date'] ?? '';
    $record_count   = intval( $migration_data['record_count'] ?? 0 );
    $records        = $migration_data['records'] ?? [];
    $post_types = gs_migration_get_post_types();

    /*
    * Filters
    */
    $filter_post_type = sanitize_key(
      wp_unslash( $_GET['filter_post_type'] ?? '' )
    );
    $filter_post_id = absint(
      $_GET['filter_post_id'] ?? 0
    );
    $filter_failed_only = ! empty(
      $_GET['filter_failed_only']
    );

    /*
    * Pagination
    */
    $paged = max(
      1,
      absint( $_GET['paged'] ?? 1 )
    );
    $per_page = 50;
    echo '<div class="wrap">';
    echo '<h1>GS Migration Tool</h1>';
    echo '<p>This tool will migrate post meta fields (<code>year</code>, <code>committee</code>, <code>document-type</code>) to taxonomy terms for custom post types <code>gs_meetings</code>, <code>gs_file</code>, and <code>gs_member</code>.</p>';

    /*
    * Migration Controls
    */
    if ( get_option( 'gs_migration_completed' ) ) {
      echo '<p><strong>Migration has been completed.</strong></p>';
    } else {
      echo '<form method="post">';
      wp_nonce_field( 'gs_migration_nonce' );
      echo '<input
        type="submit"
        name="gs_migrate_meta_to_taxonomies"
        class="button button-primary"
        value="Run Migration"
      >';
      $disabled = $record_count ? '' : ' disabled';
      echo '<input
        type="checkbox"
        id="gs_migrate_write_to_taxonomies"
        name="gs_migrate_write_to_taxonomies"
        value="1"
        style="display:block;margin:5px;"
        ' . $disabled . '
      >';
      echo '<label for="gs_migrate_write_to_taxonomies">';
      echo 'Check here to enable migration write to taxonomies (CAUTION: effects are permanent)';
      echo '</label>';
      echo '</form>';
    }

    /*
    * Summary Header
    */
    if ( $record_count > 0 ) {
      echo '<hr>';
      echo '<p><strong>Migration Date:</strong> '
        . esc_html( $migration_date )
        . '</p>';
      echo '<p><strong>Total Records:</strong> '
        . number_format_i18n( $record_count )
        . '</p>';
    }

    /*
    * Filter Records
    */
    $filtered_rows = [];
    foreach ( $records as $row ) {
      if ( $filter_post_type && $row['type'] !== $filter_post_type ) {
        continue;
      }
      if ( $filter_post_id && intval( $row['ID'] ) !== $filter_post_id ) {
        continue;
      }
      if ( $filter_failed_only && empty( $row['not_migrated_count'] ) ) {
        continue;
      }
      $filtered_rows[] = $row;
    }
    $total_items = count( $filtered_rows );
    $total_pages = max(
      1,
      ceil( $total_items / $per_page )
    );
    $display_rows = array_slice(
      $filtered_rows,
      ( $paged - 1 ) * $per_page,
      $per_page
    );

    if ( $record_count > 0 ) {
      echo '<h2>Migration Summary</h2>';

      /*
      * Filter Form
      */
      echo '<form method="get" style="margin-bottom:20px;">';
      echo '<input type="hidden" name="page" value="' . esc_attr( $_GET['page'] ?? '' ) . '">';
      echo '<label for="filter_post_type"><strong>Post Type:</strong></label> ';
      echo '<select id="filter_post_type" name="filter_post_type">';
      echo '<option value="">All</option>';

      foreach ( $post_types as $post_type ) {
        printf(
          '<option value="%s" %s>%s</option>',
          esc_attr( $post_type ),
          selected( $filter_post_type, $post_type, false ),
          esc_html( $post_type )
        );
      }
      echo '</select> ';
      echo '<label for="filter_post_id" style="margin-left:15px;">';
      echo '<strong>Post ID:</strong>';
      echo '</label> ';
      echo '<input
        type="text"
        id="filter_post_id"
        name="filter_post_id"
        value="' . esc_attr( $filter_post_id ?: '' ) . '"
      > ';
      echo '<label style="margin-left:15px;">';
      echo '<input
        type="checkbox"
        name="filter_failed_only"
        value="1"
        ' . checked( $filter_failed_only, true, false ) . '
      > Failed Terms Only';
      echo '</label> ';
      submit_button(
        'Filter',
        'secondary',
        '',
        false,
        [ 'style' => 'margin-left:10px;' ]
      );
      $clear_url = remove_query_arg(
        [
          'filter_post_type',
          'filter_post_id',
          'filter_failed_only',
          'paged',
        ]
      );
      echo ' <a class="button" href="' . esc_url( $clear_url ) . '">Clear Filters</a>';
      echo '</form>';
      echo '<p><strong>Matching Records:</strong> ' .
        number_format_i18n( $total_items ) .
        '</p>';

      /*
      * Top Pagination
      */
      if ( $total_pages > 1 ) {
        echo '<div class="tablenav top">';
        echo '<div class="tablenav-pages">';
        echo paginate_links(
          [
            'base'      => add_query_arg( 'paged', '%#%' ),
            'format'    => '',
            'current'   => $paged,
            'total'     => $total_pages,
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
          ]
        );
        echo '</div>';
        echo '</div>';
      }

      /*
      * Results Table
      */
      echo '<table class="widefat fixed striped">';
      echo '<thead>';
      echo '<tr>';
      echo '<th style="width:90px;">Post ID</th>';
      echo '<th>Title</th>';
      echo '<th style="width:150px;">Post Type</th>';
      echo '<th style="width:250px;">Migrated Terms</th>';
      echo '<th style="width:250px;">Failed Terms</th>';
      echo '</tr>';
      echo '</thead>';
      echo '<tbody>';
      if ( empty( $display_rows ) ) {
        echo '<tr>';
        echo '<td colspan="5">No matching records found.</td>';
        echo '</tr>';
      } else {
        foreach ( $display_rows as $row ) {
          echo '<tr>';
          echo '<td>' . esc_html( $row['ID'] ) . '</td>';
          echo '<td>' . esc_html( $row['title'] ) . '</td>';
          echo '<td>' . esc_html( $row['type'] ) . '</td>';
          /*
          * Migrated Terms
          */
          echo '<td>';
          if ( ! empty( $row['migrated'] ) ) {
            echo implode(
              '<br>',
              array_map(
                'esc_html',
                $row['migrated']
              )
            );
          }
          echo '</td>';

          /*
          * Failed Terms
          */
          echo '<td>';
          if ( ! empty( $row['not_migrated'] ) ) {
            echo '<span class="invalid-tax-term">';
            echo implode(
              '<br>',
              array_map(
                'esc_html',
                $row['not_migrated']
              )
            );
            echo '</span>';
          }
          echo '</td>';
          echo '</tr>';
        }
      }
      echo '</tbody>';
      echo '</table>';

      /*
      * Bottom Pagination
      */
      if ( $total_pages > 1 ) {
        echo '<div class="tablenav bottom">';
        echo '<div class="tablenav-pages">';
        echo paginate_links(
          [
            'base'      => add_query_arg( 'paged', '%#%' ),
            'format'    => '',
            'current'   => $paged,
            'total'     => $total_pages,
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
          ]
        );
        echo '</div>';
        echo '</div>';
      }
    }
    echo '</div>';
  }

  function gs_log($message) {
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
      error_log('[GS Migration] ' . $message);
    }
  }

  function is_a_valid_tax_term( $mapped_meta_value, $taxonomy ) {
    return in_array($mapped_meta_value, 
        wp_list_pluck(get_terms([
            'taxonomy'      => $taxonomy,
            'hide_empty'    => false,
            'orderBy'       => 'name',
            'order'         => 'DESC',
        ]), 'slug')
      );
  }

  function get_membership_post_id( $committee, $year, $role ) {
    $posts = get_posts([
          'post_type' => 'membership',
          'posts_per_page' => 1,
          'orderby'        => 'ID',
          'order'          => 'DESC',
          'post_status' => 'any',
          'fields' => 'ids',
          'tax_query' => [
            'relation' => 'AND',
            [
                'taxonomy' => 'committee',
                'field'    => 'slug',
                'terms'    => $committee,
            ],
            [
                'taxonomy' => 'committee-year',
                'field'    => 'slug',
                'terms'    => $year,
            ],
          ],
          'meta_query' => array(
            array(
              'key'   => 'display_role',
              'value' => $role,
            )
	        )
      ]);
    return $posts[0] ?? 0;
  }

  function add_member_to_membership( $member_id, $membership_id ) {
    // Using $format_value = false to ignore ACF relationship return type setting
    $current_members = get_field( 'member', $membership_id, false );
    if ( !is_array( $current_members ) ) {
      $current_members = [];
    }
    // Normalize for strict in_array check
    $current_members = array_map( 'intval', $current_members );
    $member_id       = (int) $member_id;
    if ( !in_array( $member_id, $current_members, true ) ) {
      $current_members[] = $member_id;
    }
    update_field( 'member', $current_members, $membership_id );
  }

  function gs_migrate_meta_to_taxonomies() {
    if (!is_super_admin()) {
      gs_log('Attempt made by non-Network Admin.');
      return;
    }
    if (get_option('gs_migration_completed')) {
      gs_log('Already completed.');
      return;
    }

    $post_types = gs_migration_get_post_types();
    $meta_to_tax = [
      'gs_meetings' => [
        'date' => 'committee-year',
        'council' => 'committee'
      ],
      'gs_file' => [
        'year' => 'committee-year',
        'committee' => 'committee',
        'document-type' => 'document-type'
      ],
      'gs_member' => [
        'curriculum_serving_years' => ['committee', 'committee-year'],
        'policy_serving_years' => ['committee', 'committee-year'],
        'appeals_serving_years' => ['committee', 'committee-year'],
        'program_serving_years' => ['committee', 'committee-year']
      ],
    ];
    $search_replace_map = [
      'date' => ['needle' => '', 'replace' => ''],
      'council' => ['needle' => '_serving_years', 'replace' => '-committee'],
      'year' => ['needle' => '', 'replace' => ''],
      'committee' => ['needle' => '_serving_years', 'replace' => '-committee'],
      'document-type' => ['needle' => '', 'replace' => '']
    ];

    $write_to_tax = $_POST['gs_migrate_write_to_taxonomies'];
    $log_msg = 'Starting' . ($write_to_tax ? '' : ' test') . ' migration.';
    gs_log($log_msg);
    $summary = [];
    $summary_with_error = [];

    foreach ($post_types as $post_type) {
      $posts = get_posts([
          'post_type' => $post_type,
          'posts_per_page' => -1,
          'post_status' => 'any'
      ]);

      foreach ($posts as $post) {
        $migrated = [];
        $not_migrated = [];

        foreach ($meta_to_tax[$post_type] as $meta_key => $taxonomy) {
          $meta_value = get_post_meta($post->ID, $meta_key, true);
          if ('gs_member' == $post_type && !empty($meta_value)) {
            $meta_array_value = explode( ',', $meta_value );
            foreach ($meta_array_value as $serving_year) {
              [$year, $role] = explode(' ', $serving_year, 2);
              $committee = str_replace('_serving_years', '', $meta_key);
              $membership_id = get_membership_post_id( $committee . '-committee', $year, $role );
              if ( $write_to_tax && $membership_id ) {
                add_member_to_membership ($post->ID, $membership_id);
              }
              if ( $membership_id ) {
                $migrated[] = "Member($meta_key) → Committee, Year, Role: $committee, $year, $role";
              } else {
                $not_migrated[] = "Member($meta_key) → Committee, Year, Role: $committee, $year, $role";
              }              
            }
          }
          elseif (!empty($meta_value)) {
            $mapped_meta_value = $search_replace_map[$meta_key]['needle'] ?
              str_replace($search_replace_map[$meta_key]['needle'], $search_replace_map[$meta_key]['replace'], $meta_value) : $meta_value;
            $is_a_valid_tax_term = is_a_valid_tax_term( $mapped_meta_value, $taxonomy );
            if ($write_to_tax && $is_a_valid_tax_term) {
              // TODO: some extra processing for member/membership relationship
              //wp_set_object_terms($post->ID, $mapped_meta_value, $taxonomy, true);
            }
            if ( $is_a_valid_tax_term ) {
              $migrated[] = "$meta_key → $taxonomy: $mapped_meta_value";
            } else {
              $not_migrated[] = "$meta_key → $taxonomy: $mapped_meta_value";
            }
          }
        }

        if (!empty($not_migrated)) {
          $summary_with_error[] = [
            'ID'                 => $post->ID,
            'title'              => $post->post_title,
            'type'               => $post_type,
            'migrated'           => $migrated,
            'not_migrated'       => $not_migrated,
            'migrated_count'     => count( $migrated ),
            'not_migrated_count' => count( $not_migrated ),
          ];
        }
        elseif (!empty($migrated)) {
          $summary[] = [
            'ID'                 => $post->ID,
            'title'              => $post->post_title,
            'type'               => $post_type,
            'migrated'           => $migrated,
            'not_migrated'       => [],
            'migrated_count'     => count( $migrated ),
            'not_migrated_count' => 0,
          ];
        }
      }
    } 

    // Sort errors to the top
    $sorted_summary = array_merge( $summary_with_error, $summary );
    gs_log( sprintf( '%s arrays merged: %d terms saved, %d terms not saved due to errors.', $post_type, count($summary), count($summary_with_error) ) );
    update_option( 'gs_migration_affected_records',
      [
          'migration_date' => current_time( 'mysql' ),
          'record_count'   => count( $sorted_summary ),
          'records'        => $sorted_summary,
      ]
    );
    if ($write_to_tax) {
      update_option('gs_migration_completed', true);
    }
    gs_log("Data saved to options table.");

    echo '<div class="notice notice-success"><p>Migration' . ($write_to_tax ? '' : ' test') . ' completed successfully.</p></div>';
  }

  function gs_migrate_options_to_taxonomies_init() {
    if (add_option('gs_migration_completed', false)) {
      gs_log("Option gs_migration_completed created and initialized to false.");
    }
    if (add_option('gs_migration_affected_records', '')) {
      gs_log("Option gs_migration_affected_records created and initialized to ''.");
    }
  }

  // Hooks to register options and add admin menu
  add_action('admin_init', 'gs_migration_settings\gs_migrate_options_to_taxonomies_init');
  add_action('admin_menu', 'gs_migration_settings\gs_add_migration_settings_page');
}