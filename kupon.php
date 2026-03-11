<?php
/*
Plugin Name: Simple Coupons & Deals
Description: Manage coupons and deals with code copying, logo, and link support.
Version: 1.2
Requires at least: 5.0
Requires PHP: 7.4
Author: Adam Hornof
Author URI: https://hornof.dev
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: kupon
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Load text domain
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'kupon', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

// Register CPT
add_action( 'init', function() {
    register_post_type( 'scp_offer', [
        'labels' => [
            'name'               => __( 'Offers', 'kupon' ),
            'singular_name'      => __( 'Offer', 'kupon' ),
            'add_new'            => __( 'Add New Offer', 'kupon' ),
            'add_new_item'       => __( 'Add New Offer', 'kupon' ),
            'edit_item'          => __( 'Edit Offer', 'kupon' ),
            'new_item'           => __( 'New Offer', 'kupon' ),
            'view_item'          => __( 'View Offer', 'kupon' ),
            'search_items'       => __( 'Search Offers', 'kupon' ),
            'not_found'          => __( 'No offers found', 'kupon' ),
            'not_found_in_trash' => __( 'No offers in trash', 'kupon' ),
            'menu_name'          => __( 'Coupons & Deals', 'kupon' ),
        ],
        'public'        => true,
        'has_archive'   => false,
        'rewrite'       => [ 'slug' => 'scp_offer', 'with_front' => false ],
        'show_in_rest'  => true,
        'supports'      => [ 'title', 'editor' ],
        'menu_position' => 20,
        'menu_icon'     => 'dashicons-tickets-alt',
    ] );
} );

// Settings page
add_action( 'admin_menu', function() {
    add_options_page(
        __( 'Simple Coupons & Deals Settings', 'kupon' ),
        __( 'Coupons & Deals', 'kupon' ),
        'manage_options',
        'scp-settings',
        'scp_settings_page_callback'
    );
} );

function scp_settings_page_callback() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    if ( isset( $_POST['scp_settings_nonce'] ) &&
         wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['scp_settings_nonce'] ) ), 'scp_save_settings' ) ) {
        $reklama_shortcode = isset( $_POST['scp_reklama_shortcode'] )
            ? sanitize_text_field( wp_unslash( $_POST['scp_reklama_shortcode'] ) )
            : '';
        update_option( 'scp_reklama_shortcode', $reklama_shortcode );
        echo '<div class="updated"><p>' . esc_html__( 'Settings saved.', 'kupon' ) . '</p></div>';
    }

    $reklama = get_option( 'scp_reklama_shortcode', '' );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Simple Coupons & Deals Settings', 'kupon' ); ?></h1>
        <form method="post">
            <?php wp_nonce_field( 'scp_save_settings', 'scp_settings_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="scp_reklama_shortcode"><?php esc_html_e( 'Ad Shortcode (shown every 4 offers)', 'kupon' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="scp_reklama_shortcode" id="scp_reklama_shortcode"
                               value="<?php echo esc_attr( $reklama ); ?>" class="regular-text"
                               placeholder="[my_ad_shortcode]" />
                        <p class="description"><?php esc_html_e( 'Optional shortcode inserted after every 4th offer.', 'kupon' ); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// [nabidky] shortcode — list of offers
add_shortcode( 'nabidky', function( $atts ) {
    $atts = shortcode_atts( [
        'type'   => '',
        'count'  => -1,
        'obchod' => '',
    ], $atts, 'nabidky' );

    global $wpdb;
    $obchody = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != '' ORDER BY meta_value ASC",
            '_scp_obchod'
        )
    );

    $selected_obchod = isset( $_GET['scp_obchod'] )
        ? sanitize_text_field( wp_unslash( $_GET['scp_obchod'] ) )
        : sanitize_text_field( $atts['obchod'] );

    $meta_query = [
        [
            'key'   => '_scp_aktivni',
            'value' => 'yes',
        ],
    ];

    if ( $atts['type'] === 'kupon' || $atts['type'] === 'akce' ) {
        $meta_query[] = [
            'key'   => '_scp_offer_type',
            'value' => $atts['type'],
        ];
    }

    if ( $selected_obchod ) {
        $meta_query[] = [
            'key'     => '_scp_obchod',
            'value'   => $selected_obchod,
            'compare' => 'LIKE',
        ];
    }

    $query = new WP_Query( [
        'post_type'      => 'scp_offer',
        'posts_per_page' => intval( $atts['count'] ),
        'meta_query'     => $meta_query,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );

    $reklama_shortcode = get_option( 'scp_reklama_shortcode' );

    ob_start();

    if ( $obchody && count( $obchody ) > 1 ) {
        echo '<form method="get" style="margin-bottom:20px;">';
        echo '<select name="scp_obchod" onchange="this.form.submit()" style="padding:6px 12px; min-width:180px;">';
        echo '<option value="">' . esc_html__( 'All stores', 'kupon' ) . '</option>';
        foreach ( $obchody as $obchod ) {
            echo '<option value="' . esc_attr( $obchod ) . '" ' . selected( $selected_obchod, $obchod, false ) . '>' . esc_html( $obchod ) . '</option>';
        }
        echo '</select>';
        foreach ( $_GET as $key => $val ) {
            if ( $key !== 'scp_obchod' && is_string( $val ) ) {
                echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( sanitize_text_field( wp_unslash( $val ) ) ) . '" />';
            }
        }
        echo '</form>';
    }

    echo '<div class="scp-offers-list" style="display:flex; flex-wrap:wrap; gap:20px;">';

    $i = 0;
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $id = get_the_ID();
            $platnost = get_post_meta( $id, '_scp_platnost', true );
            if ( $platnost && strtotime( $platnost ) < strtotime( 'today' ) ) {
                delete_post_meta( $id, '_scp_aktivni' );
                continue;
            }
            echo do_shortcode( '[nabidka id="' . intval( $id ) . '"]' );
            $i++;
            if ( $reklama_shortcode && $i % 4 === 0 ) {
                echo do_shortcode( $reklama_shortcode );
            }
        }
    } else {
        echo '<p>' . esc_html__( 'No active offers.', 'kupon' ) . '</p>';
    }

    echo '</div>';

    wp_reset_postdata();

    return ob_get_clean();
} );

// Add meta boxes
add_action( 'add_meta_boxes', function() {
    add_meta_box( 'scp_offer_details', __( 'Offer Details', 'kupon' ), 'scp_metabox_callback', 'scp_offer', 'normal', 'high' );
    add_meta_box( 'scp_aktivni_box', __( 'Active', 'kupon' ), 'scp_aktivni_metabox_callback', 'scp_offer', 'side' );
} );

function scp_metabox_callback( $post ) {
    wp_nonce_field( 'scp_save_nonce', 'scp_nonce' );

    $type     = get_post_meta( $post->ID, '_scp_offer_type', true ) ?: 'kupon';
    $kod      = get_post_meta( $post->ID, '_scp_kod', true );
    $url      = get_post_meta( $post->ID, '_scp_url', true );
    $logo     = get_post_meta( $post->ID, '_scp_logo', true );
    $platnost = get_post_meta( $post->ID, '_scp_platnost', true );
    $obchod   = get_post_meta( $post->ID, '_scp_obchod', true );
    ?>
    <p>
        <label for="scp_offer_type"><strong><?php esc_html_e( 'Offer type:', 'kupon' ); ?></strong></label><br />
        <select name="scp_offer_type" id="scp_offer_type" onchange="document.getElementById('scp_kod').disabled = (this.value === 'akce');">
            <option value="kupon" <?php selected( $type, 'kupon' ); ?>><?php esc_html_e( 'Coupon', 'kupon' ); ?></option>
            <option value="akce" <?php selected( $type, 'akce' ); ?>><?php esc_html_e( 'Deal', 'kupon' ); ?></option>
        </select>
    </p>
    <p>
        <label for="scp_kod"><strong><?php esc_html_e( 'Coupon code:', 'kupon' ); ?></strong></label><br />
        <input type="text" id="scp_kod" name="scp_kod" value="<?php echo esc_attr( $kod ); ?>" style="width:100%;" <?php echo ( $type === 'akce' ) ? 'disabled' : ''; ?> />
        <small><?php esc_html_e( 'Only valid for coupons.', 'kupon' ); ?></small>
    </p>
    <p>
        <label for="scp_url"><strong><?php esc_html_e( 'Store/deal URL:', 'kupon' ); ?></strong></label><br />
        <input type="url" id="scp_url" name="scp_url" value="<?php echo esc_attr( $url ); ?>" style="width:100%;" placeholder="https://example.com" />
    </p>
    <p>
        <label for="scp_logo"><strong><?php esc_html_e( 'Store/deal logo URL:', 'kupon' ); ?></strong></label><br />
        <input type="url" id="scp_logo" name="scp_logo" value="<?php echo esc_attr( $logo ); ?>" style="width:100%;" placeholder="https://..." />
    </p>
    <p>
        <label for="scp_platnost"><strong><?php esc_html_e( 'Valid until:', 'kupon' ); ?></strong></label><br />
        <input type="date" id="scp_platnost" name="scp_platnost" value="<?php echo esc_attr( $platnost ); ?>" style="width:100%;" />
        <small><?php esc_html_e( 'The offer will be automatically deactivated after this date.', 'kupon' ); ?></small>
    </p>
    <p>
        <label for="scp_obchod"><strong><?php esc_html_e( 'Store:', 'kupon' ); ?></strong></label><br />
        <input type="text" id="scp_obchod" name="scp_obchod" value="<?php echo esc_attr( $obchod ); ?>" style="width:100%;" placeholder="<?php esc_attr_e( 'Store name', 'kupon' ); ?>" />
        <small><?php esc_html_e( 'E.g. Amazon, eBay, Best Buy\xe2\x80\xa6', 'kupon' ); ?></small>
    </p>
    <p>
        <label><strong><?php esc_html_e( 'Shortcode for this offer:', 'kupon' ); ?></strong></label><br />
        <input type="text" id="scp_shortcode_<?php echo esc_attr( $post->ID ); ?>"
               value='[nabidka id="<?php echo esc_attr( $post->ID ); ?>"]'
               readonly style="width:70%; font-family:monospace; font-size:1em;" />
        <button type="button"
                onclick="navigator.clipboard.writeText(document.getElementById('scp_shortcode_<?php echo esc_attr( $post->ID ); ?>').value); this.textContent='<?php esc_attr_e( 'Copied!', 'kupon' ); ?>'; setTimeout(()=>this.textContent='<?php esc_attr_e( 'Copy', 'kupon' ); ?>',1500);"
                style="margin-left:10px;"><?php esc_html_e( 'Copy', 'kupon' ); ?></button>
    </p>
    <?php
}

function scp_aktivni_metabox_callback( $post ) {
    $aktivni = get_post_meta( $post->ID, '_scp_aktivni', true );
    ?>
    <label>
        <input type="checkbox" name="scp_aktivni" value="yes" <?php checked( $aktivni, 'yes' ); ?> />
        <?php esc_html_e( 'Active offer (show in listing)', 'kupon' ); ?>
    </label>
    <?php
}

// Save meta data
add_action( 'save_post', function( $post_id ) {
    if ( ! isset( $_POST['scp_nonce'] ) ||
         ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['scp_nonce'] ) ), 'scp_save_nonce' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['scp_offer_type'] ) ) {
        $type = sanitize_text_field( wp_unslash( $_POST['scp_offer_type'] ) );
        update_post_meta( $post_id, '_scp_offer_type', in_array( $type, [ 'kupon', 'akce' ], true ) ? $type : 'kupon' );
    }

    if ( isset( $_POST['scp_kod'] ) && get_post_meta( $post_id, '_scp_offer_type', true ) === 'kupon' ) {
        update_post_meta( $post_id, '_scp_kod', sanitize_text_field( wp_unslash( $_POST['scp_kod'] ) ) );
    } else {
        delete_post_meta( $post_id, '_scp_kod' );
    }

    if ( isset( $_POST['scp_url'] ) ) {
        update_post_meta( $post_id, '_scp_url', esc_url_raw( wp_unslash( $_POST['scp_url'] ) ) );
    }

    if ( isset( $_POST['scp_logo'] ) ) {
        update_post_meta( $post_id, '_scp_logo', esc_url_raw( wp_unslash( $_POST['scp_logo'] ) ) );
    }

    if ( isset( $_POST['scp_platnost'] ) ) {
        update_post_meta( $post_id, '_scp_platnost', sanitize_text_field( wp_unslash( $_POST['scp_platnost'] ) ) );
    }

    if ( isset( $_POST['scp_obchod'] ) ) {
        update_post_meta( $post_id, '_scp_obchod', sanitize_text_field( wp_unslash( $_POST['scp_obchod'] ) ) );
    }

    if ( isset( $_POST['scp_aktivni'] ) && 'yes' === $_POST['scp_aktivni'] ) {
        update_post_meta( $post_id, '_scp_aktivni', 'yes' );
    } else {
        delete_post_meta( $post_id, '_scp_aktivni' );
    }
} );

// [nabidka id="X"] shortcode — single offer display
add_shortcode( 'nabidka', function( $atts ) {
    $atts = shortcode_atts( [ 'id' => 0 ], $atts, 'nabidka' );
    $id   = intval( $atts['id'] );
    if ( ! $id ) return esc_html__( 'Offer not found', 'kupon' );

    $post = get_post( $id );
    if ( ! $post || $post->post_type !== 'scp_offer' ) return esc_html__( 'Offer not found', 'kupon' );

    $type     = get_post_meta( $id, '_scp_offer_type', true );
    $kod      = get_post_meta( $id, '_scp_kod', true );
    $url      = get_post_meta( $id, '_scp_url', true );
    $logo     = get_post_meta( $id, '_scp_logo', true );
    $platnost = get_post_meta( $id, '_scp_platnost', true );
    $obchod   = get_post_meta( $id, '_scp_obchod', true );

    $content      = do_shortcode( $post->post_content );
    $allowed_html = [
        'a'      => [ 'href' => [], 'title' => [], 'target' => [], 'rel' => [] ],
        'strong' => [],
        'em'     => [],
        'p'      => [],
        'br'     => [],
        'ul'     => [],
        'ol'     => [],
        'li'     => [],
        'span'   => [ 'class' => [] ],
        'b'      => [],
        'i'      => [],
    ];
    $content = wp_kses( $content, $allowed_html );

    ob_start();
    ?>
    <div class="scp-offer">
        <div class="scp-offer-inner">
            <?php if ( $logo ) : ?>
                <div style="margin-bottom:10px; text-align:center;">
                    <img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $post->post_title ); ?>" style="max-height:50px; max-width:100%;" />
                </div>
            <?php endif; ?>
            <h3 style="margin:0 0 10px; text-align:center;"><?php echo esc_html( $post->post_title ); ?></h3>
            <?php if ( $obchod ) : ?>
                <div style="text-align:center; color:#0073aa; font-weight:bold; margin-bottom:8px;">
                    <?php echo esc_html( $obchod ); ?>
                </div>
            <?php endif; ?>
            <div style="flex-grow:1;"><?php echo $content; ?></div>
            <?php if ( $type === 'kupon' && $kod ) : ?>
                <div class="scp-code-row" style="margin:15px 0; font-weight:bold; font-size:1.2em;">
                    <span id="scp-code-<?php echo esc_attr( $id ); ?>" style="background:#eee; padding:5px 10px; border-radius:4px; user-select:all; font-family:monospace;"><?php echo esc_html( $kod ); ?></span>
                    <button class="scp-copy-btn" data-target="scp-code-<?php echo esc_attr( $id ); ?>" style="cursor:pointer; padding:5px 10px;"><?php esc_html_e( 'Copy code', 'kupon' ); ?></button>
                </div>
            <?php endif; ?>
            <?php if ( $url ) : ?>
                <div style="text-align:center; margin-bottom:10px;">
                    <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="nofollow noopener"
                       style="background:#0073aa; color:#fff; padding:8px 15px; border-radius:4px; text-decoration:none; display:inline-block;"><?php esc_html_e( 'Go to store', 'kupon' ); ?></a>
                </div>
            <?php endif; ?>
            <?php if ( $platnost ) : ?>
                <p style="text-align:center; color:#888; font-size:0.95em;">
                    <?php
                    printf(
                        /* translators: %s: expiry date formatted as d.m.Y */
                        esc_html__( 'Valid until: %s', 'kupon' ),
                        esc_html( date_i18n( 'd.m.Y', strtotime( $platnost ) ) )
                    );
                    ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
} );

// Enqueue scripts and styles
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'scp-offers-style',
        plugin_dir_url( __FILE__ ) . 'scp-offers.css',
        [],
        '1.2'
    );
    wp_enqueue_script(
        'scp-copy-script',
        plugin_dir_url( __FILE__ ) . 'scp-copy.js',
        [],
        '1.2',
        true
    );
} );

// Add "Shortcode" column to the offers list in admin
add_filter( 'manage_scp_offer_posts_columns', function( $columns ) {
    $columns['scp_shortcode'] = __( 'Shortcode', 'kupon' );
    return $columns;
} );

add_action( 'manage_scp_offer_posts_custom_column', function( $column, $post_id ) {
    if ( $column === 'scp_shortcode' ) {
        echo '<input type="text" value="[nabidka id=&quot;' . esc_attr( $post_id ) . '&quot;]" readonly style="width:140px; font-family:monospace; font-size:1em;" onclick="this.select();" />';
    }
}, 10, 2 );
