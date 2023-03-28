<?php
/**
 * Plugin Name: GPT Guard
 * Description: Protect your Content from Web Chat-GPT 
 * Version: 1.0.0
 * Author: Walid Gabteni
 * Author URI: https://www.lightonseo.com
 * License: GPL2
 */

function is_chat_gpt() {
    if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && strpos( $_SERVER['HTTP_USER_AGENT'], 'ChatGPT-User' ) !== false ) {
        return true;
    }
    return false;
}


function gpt_guard_add_options_page() {
    add_options_page( 'GPT Guard', 'GPT Guard', 'manage_options', 'gpt-guard', 'gpt_guard_render_options_page' );
}
add_action( 'admin_menu', 'gpt_guard_add_options_page' );


function gpt_guard_render_options_page() {
    ?>
    <div class="wrap">
        <h1>GPT Guard</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'gpt_guard_options' );
            do_settings_sections( 'gpt_guard_options' );
            ?>
            <h2>Choose WordPress Behaviour on traffic comming from Web Chat-GPT</h2>
            <p>
                <label>
                    <input type="radio" name="gpt_guard_behavior" value="show_different_content" <?php checked( get_option( 'gpt_guard_behavior' ), 'show_different_content' ); ?>>
                    Show a different Content
                </label>
            </p>
            <div id="gpt-guard-show-different-content" <?php if ( get_option( 'gpt_guard_behavior' ) != 'show_different_content' ) { echo 'style="display:none"'; } ?>>
                <label for="gpt-guard-different-content">Content to show:</label>
                <textarea id="gpt-guard-different-content" name="gpt_guard_different_content"><?php echo esc_html( get_option( 'gpt_guard_different_content' ) ); ?></textarea>
            </div>
            <p>
                <label>
                    <input type="radio" name="gpt_guard_behavior" value="redirection" <?php checked( get_option( 'gpt_guard_behavior' ), 'redirection' ); ?>>
                    Redirection
                </label>
            </p>
            <div id="gpt-guard-redirection" <?php if ( get_option( 'gpt_guard_behavior' ) != 'redirection' ) { echo 'style="display:none"'; } ?>>
                <label for="gpt-guard-redirection-url">Target URL:</label>
                <input type="text" id="gpt-guard-redirection-url" name="gpt_guard_redirection_url" value="<?php echo esc_attr( get_option( 'gpt_guard_redirection_url' ) ); ?>">
            </div>
            <p>
                <label>
                    <input type="radio" name="gpt_guard_behavior" value="404" <?php checked( get_option( 'gpt_guard_behavior' ), '404' ); ?>>
                    Show a 404 error (Not Found)
                </label>
            </p>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Enregistre les options
function gpt_guard_register_settings() {
    register_setting( 'gpt_guard_options', 'gpt_guard_behavior' );
    register_setting( 'gpt_guard_options', 'gpt_guard_different_content' );
    register_setting( 'gpt_guard_options', 'gpt_guard_redirection_url' );
}
add_action( 'admin_init', 'gpt_guard_register_settings' );

// Définit le comportement de WordPress en fonction de l'option choisie
function gpt_guard_modify_wp_behavior() {
    if ( is_chat_gpt() ) {
        $behavior = get_option( 'gpt_guard_behavior' );
        if ( $behavior == 'show_different_content' ) {
            $content = get_option( 'gpt_guard_different_content' );
            if ( $content ) {
                echo $content;
                exit;
            }
        } elseif ( $behavior == 'redirection' ) {
            $url = get_option( 'gpt_guard_redirection_url' );
            if ( $url ) {
                wp_redirect( $url );
                exit;
            }
        } elseif ( $behavior == '404' ) {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            get_template_part( 404 );
            exit;
        }
    }
}
add_action( 'template_redirect', 'gpt_guard_modify_wp_behavior' );

