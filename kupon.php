<?php
/*
Plugin Name: Kupony a Akce
Description: Správa kuponů a akcí s možností kopírování kódu, logem a odkazem.
Version: 1.1
Author: Adam Hornof
*/

if (!defined('ABSPATH')) exit;

// Registrace CPT
add_action('init', function() {
    register_post_type('scp_offer', [
        'labels' => [
            'name' => 'Nabídky',
            'singular_name' => 'Nabídka',
            'add_new' => 'Přidat novou nabídku',
            'add_new_item' => 'Přidat novou nabídku',
            'edit_item' => 'Upravit nabídku',
            'new_item' => 'Nová nabídka',
            'view_item' => 'Zobrazit nabídku',
            'search_items' => 'Hledat nabídky',
            'not_found' => 'Nabídky nenalezeny',
            'not_found_in_trash' => 'Žádné nabídky v koši',
            'menu_name' => 'Kupony & Akce',
        ],
        'public' => true,
        'has_archive' => false,
        'rewrite' => ['slug' => 'scp_offer', 'with_front' => false],
        'show_in_rest' => true,
        'supports' => ['title', 'editor'],
        'menu_position' => 20,
        'menu_icon' => 'dashicons-tickets-alt',
    ]);
});

add_shortcode('nabidky', function($atts) {
    $atts = shortcode_atts([
        'type' => '',
        'count' => -1,
        'obchod' => '',
    ], $atts, 'nabidky');

    // Získání všech unikátních obchodů
    global $wpdb;
    $obchody = $wpdb->get_col("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_scp_obchod' AND meta_value != '' ORDER BY meta_value ASC");

    // Zpracování filtru z GET parametru
    $selected_obchod = isset($_GET['scp_obchod']) ? sanitize_text_field($_GET['scp_obchod']) : $atts['obchod'];

    $meta_query = [
        [
            'key' => '_scp_aktivni',
            'value' => 'yes',
        ],
    ];

    if ($atts['type'] === 'kupon' || $atts['type'] === 'akce') {
        $meta_query[] = [
            'key' => '_scp_offer_type',
            'value' => $atts['type'],
        ];
    }

    if ($selected_obchod) {
        $meta_query[] = [
            'key' => '_scp_obchod',
            'value' => $selected_obchod,
            'compare' => 'LIKE',
        ];
    }

    $query = new WP_Query([
        'post_type' => 'scp_offer',
        'posts_per_page' => intval($atts['count']),
        'meta_query' => $meta_query,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);

    $reklama_shortcode = get_option('scp_reklama_shortcode');

    ob_start();

    // Dropdown filtr obchodů
    if ($obchody && count($obchody) > 1) {
        echo '<form method="get" style="margin-bottom:20px;">';
        echo '<select name="scp_obchod" onchange="this.form.submit()" style="padding:6px 12px; min-width:180px;">';
        echo '<option value="">Všechny obchody</option>';
        foreach ($obchody as $obchod) {
            echo '<option value="' . esc_attr($obchod) . '" ' . selected($selected_obchod, $obchod, false) . '>' . esc_html($obchod) . '</option>';
        }
        echo '</select>';
        // Zachování ostatních GET parametrů
        foreach ($_GET as $key => $val) {
            if ($key !== 'scp_obchod') {
                echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '" />';
            }
        }
        echo '</form>';
    }

    echo '<div class="scp-offers-list" style="display:flex; flex-wrap:wrap; gap:20px;">';

    $i = 0;
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $id = get_the_ID();
            $platnost = get_post_meta($id, '_scp_platnost', true);
            if ($platnost && strtotime($platnost) < strtotime('today')) {
                delete_post_meta($id, '_scp_aktivni');
                continue;
            }
            echo do_shortcode('[nabidka id="' . $id . '"]');
            $i++;
            if ($reklama_shortcode && $i % 4 === 0) {
                echo do_shortcode($reklama_shortcode);
            }
        }
    } else {
        echo '<p>Žádné aktivní nabídky.</p>';
    }

    echo '</div>';

    wp_reset_postdata();

    return ob_get_clean();
});

// Přidání metaboxů
add_action('add_meta_boxes', function() {
    add_meta_box('scp_offer_details', 'Detaily nabídky', 'scp_metabox_callback', 'scp_offer', 'normal', 'high');
    add_meta_box('scp_aktivni_box', 'Aktivní', 'scp_aktivni_metabox_callback', 'scp_offer', 'side');
});

// Callback metaboxu pro detaily
function scp_metabox_callback($post) {
    wp_nonce_field('scp_save_nonce', 'scp_nonce');

    $type = get_post_meta($post->ID, '_scp_offer_type', true) ?: 'kupon';
    $kod = get_post_meta($post->ID, '_scp_kod', true);
    $url = get_post_meta($post->ID, '_scp_url', true);
    $logo = get_post_meta($post->ID, '_scp_logo', true);
    $platnost = get_post_meta($post->ID, '_scp_platnost', true);
    $obchod = get_post_meta($post->ID, '_scp_obchod', true);
    ?>
    <p>
        <label for="scp_offer_type"><strong>Typ nabídky:</strong></label><br />
        <select name="scp_offer_type" id="scp_offer_type" onchange="document.getElementById('scp_kod').disabled = (this.value === 'akce');">
            <option value="kupon" <?php selected($type, 'kupon'); ?>>Kupon</option>
            <option value="akce" <?php selected($type, 'akce'); ?>>Akce</option>
        </select>
    </p>
    <p>
        <label for="scp_kod"><strong>Kód kuponu:</strong></label><br />
        <input type="text" id="scp_kod" name="scp_kod" value="<?php echo esc_attr($kod); ?>" style="width:100%;" <?php echo ($type === 'akce') ? 'disabled' : ''; ?> />
        <small>Platné pouze pro kupony.</small>
    </p>
    <p>
        <label for="scp_url"><strong>URL eshopu/akce:</strong></label><br />
        <input type="url" id="scp_url" name="scp_url" value="<?php echo esc_attr($url); ?>" style="width:100%;" placeholder="https://example.cz" />
    </p>
    <p>
        <label for="scp_logo"><strong>URL loga eshopu/akce:</strong></label><br />
        <input type="url" id="scp_logo" name="scp_logo" value="<?php echo esc_attr($logo); ?>" style="width:100%;" placeholder="https://..." />
    </p>
    <p>
        <label for="scp_platnost"><strong>Platnost do:</strong></label><br />
        <input type="date" id="scp_platnost" name="scp_platnost" value="<?php echo esc_attr($platnost); ?>" style="width:100%;" />
        <small>Kupon bude po tomto datu automaticky deaktivován.</small>
    </p>
    <p>
        <label for="scp_obchod"><strong>Obchod:</strong></label><br />
        <input type="text" id="scp_obchod" name="scp_obchod" value="<?php echo esc_attr($obchod); ?>" style="width:100%;" placeholder="Název obchodu" />
        <small>Např. Alza, Mall, CZC...</small>
    </p>
    <p>
        <label><strong>Shortcode pro tento kupon:</strong></label><br />
        <input type="text" id="scp_shortcode_<?php echo esc_attr($post->ID); ?>" value='[nabidka id="<?php echo esc_attr($post->ID); ?>"]' readonly style="width:70%; font-family:monospace; font-size:1em;" />
        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('scp_shortcode_<?php echo esc_attr($post->ID); ?>').value); this.textContent='Zkopírováno!'; setTimeout(()=>this.textContent='Kopírovat',1500);" style="margin-left:10px;">Kopírovat</button>
    </p>
    <?php
}

// Callback metaboxu pro aktivní checkbox
function scp_aktivni_metabox_callback($post) {
    wp_nonce_field('scp_save_nonce', 'scp_nonce');
    $aktivni = get_post_meta($post->ID, '_scp_aktivni', true);
    ?>
    <label>
        <input type="checkbox" name="scp_aktivni" value="yes" <?php checked($aktivni, 'yes'); ?> />
        Aktivní nabídka (zobrazit ve výpisu)
    </label>
    <?php
}

// Ukládání metadat
add_action('save_post', function($post_id) {
    if (!isset($_POST['scp_nonce']) || !wp_verify_nonce($_POST['scp_nonce'], 'scp_save_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['scp_offer_type'])) {
        $type = sanitize_text_field($_POST['scp_offer_type']);
        update_post_meta($post_id, '_scp_offer_type', in_array($type, ['kupon','akce']) ? $type : 'kupon');
    }

    if (isset($_POST['scp_kod']) && get_post_meta($post_id, '_scp_offer_type', true) === 'kupon') {
        update_post_meta($post_id, '_scp_kod', sanitize_text_field($_POST['scp_kod']));
    } else {
        delete_post_meta($post_id, '_scp_kod');
    }

    if (isset($_POST['scp_url'])) {
        update_post_meta($post_id, '_scp_url', esc_url_raw($_POST['scp_url']));
    }

    if (isset($_POST['scp_logo'])) {
        update_post_meta($post_id, '_scp_logo', esc_url_raw($_POST['scp_logo']));
    }

    if (isset($_POST['scp_platnost'])) {
        update_post_meta($post_id, '_scp_platnost', sanitize_text_field($_POST['scp_platnost']));
    }

    if (isset($_POST['scp_obchod'])) {
        update_post_meta($post_id, '_scp_obchod', sanitize_text_field($_POST['scp_obchod']));
    }

    // Uložení aktivního stavu
    if (isset($_POST['scp_aktivni']) && $_POST['scp_aktivni'] === 'yes') {
        update_post_meta($post_id, '_scp_aktivni', 'yes');
    } else {
        delete_post_meta($post_id, '_scp_aktivni');
    }
});

// Shortcode pro zobrazení jedné nabídky podle ID
// Registrovat shortcode
add_shortcode('nabidka', function($atts) {
    $atts = shortcode_atts(['id' => 0], $atts, 'nabidka');
    $id = intval($atts['id']);
    if (!$id) return 'Nabídka nenalezena';

    $post = get_post($id);
    if (!$post || $post->post_type !== 'scp_offer') return 'Nabídka nenalezena';

    $type = get_post_meta($id, '_scp_offer_type', true);
    $kod = get_post_meta($id, '_scp_kod', true);
    $url = get_post_meta($id, '_scp_url', true);
    $logo = get_post_meta($id, '_scp_logo', true);
    $platnost = get_post_meta($id, '_scp_platnost', true);
    $obchod = get_post_meta($id, '_scp_obchod', true);

    $content = $post->post_content;
    $content = do_shortcode($content);
    $allowed_html = [
        'a' => ['href' => [], 'title' => [], 'target' => [], 'rel' => []],
        'strong' => [],
        'em' => [],
        'p' => [],
        'br' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'span' => ['class' => []],
        'b' => [],
        'i' => [],
    ];
    $content = wp_kses($content, $allowed_html);

    ob_start();
    ?>
    <div class="scp-offer">
        <div class="scp-offer-inner">
            <?php if ($logo): ?>
                <div style="margin-bottom:10px; text-align:center;">
                    <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($post->post_title); ?>" style="max-height:50px; max-width:100%;" />
                </div>
            <?php endif; ?>
            <h3 style="margin:0 0 10px; text-align:center;"><?php echo esc_html($post->post_title); ?></h3>
            <?php if ($obchod): ?>
                <div style="text-align:center; color:#0073aa; font-weight:bold; margin-bottom:8px;">
                    <?php echo esc_html($obchod); ?>
                </div>
            <?php endif; ?>
            <div style="flex-grow:1;"><?php echo $content; ?></div>
            <?php if ($type === 'kupon' && $kod): ?>
                <div class="scp-code-row" style="margin:15px 0; font-weight:bold; font-size:1.2em;">
                    <span id="scp-code-<?php echo esc_attr($id); ?>" style="background:#eee; padding:5px 10px; border-radius:4px; user-select:all; font-family:monospace;"><?php echo esc_html($kod); ?></span>
                    <button class="scp-copy-btn" data-target="scp-code-<?php echo esc_attr($id); ?>" style="cursor:pointer; padding:5px 10px;">Kopírovat kód</button>
                </div>
            <?php endif; ?>
            <?php if ($url): ?>
                <div style="text-align:center; margin-bottom:10px;">
                    <a href="<?php echo esc_url($url); ?>" target="_blank" rel="nofollow noopener" style="background:#0073aa; color:#fff; padding:8px 15px; border-radius:4px; text-decoration:none; display:inline-block;">Přejít na eshop</a>
                </div>
            <?php endif; ?>
            <?php if ($platnost): ?>
                <p style="text-align:center; color:#888; font-size:0.95em;">
                    Platnost do: <?php echo esc_html(date('d.m.Y', strtotime($platnost))); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
});

// Přidat JS pro kopírování
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'scp-offers-style',
        plugin_dir_url(__FILE__) . 'scp-offers.css'
    );
    wp_enqueue_script(
        'scp-copy-script',
        plugin_dir_url(__FILE__) . 'scp-copy.js',
        [],
        false,
        true
    );
});

// Přidat sloupec "Shortcode" do seznamu kuponů v adminu
add_filter('manage_scp_offer_posts_columns', function($columns) {
    $columns['scp_shortcode'] = 'Shortcode';
    return $columns;
});

add_action('manage_scp_offer_posts_custom_column', function($column, $post_id) {
    if ($column === 'scp_shortcode') {
        echo '<input type="text" value="[nabidka id=&quot;' . esc_attr($post_id) . '&quot;]" readonly style="width:140px; font-family:monospace; font-size:1em;" onclick="this.select();" />';
    }
}, 10, 2);

