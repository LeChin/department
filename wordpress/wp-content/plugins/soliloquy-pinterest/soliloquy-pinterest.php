<?php
/**
 * Plugin Name: Soliloquy - Pinterest Addon
 * Plugin URI:  http://soliloquywp.com
 * Description: Enables Pinterest "Pin It" buttons for Soliloquy sliders.
 * Author:      Thomas Griffin
 * Author URI:  http://thomasgriffinmedia.com
 * Version:     2.1.1
 * Text Domain: soliloquy-pinterest
 * Domain Path: languages
 *
 * Soliloquy is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Soliloquy is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Soliloquy. If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define necessary addon constants.
define( 'SOLILOQUY_PINTEREST_PLUGIN_NAME', 'Soliloquy - Pinterest Addon' );
define( 'SOLILOQUY_PINTEREST_PLUGIN_VERSION', '2.1.1' );
define( 'SOLILOQUY_PINTEREST_PLUGIN_SLUG', 'soliloquy-pinterest' );

add_action( 'plugins_loaded', 'soliloquy_pinterest_plugins_loaded' );
/**
 * Ensures the full Soliloquy plugin is active before proceeding.
 *
 * @since 1.0.0
 *
 * @return null Return early if Soliloquy is not active.
 */
function soliloquy_pinterest_plugins_loaded() {

    // Bail if the main class does not exist.
    if ( ! class_exists( 'Soliloquy' ) ) {
        return;
    }

    // Fire up the addon.
    add_action( 'soliloquy_init', 'soliloquy_pinterest_plugin_init' );

}

/**
 * Loads all of the addon hooks and filters.
 *
 * @since 1.0.0
 */
function soliloquy_pinterest_plugin_init() {

    add_action( 'soliloquy_updater', 'soliloquy_pinterest_updater' );
    add_filter( 'soliloquy_defaults', 'soliloquy_pinterest_defaults', 10, 2 );
    add_filter( 'soliloquy_tab_nav', 'soliloquy_pinterest_tab_nav' );
    add_action( 'soliloquy_tab_pinterest', 'soliloquy_pinterest_settings' );
    add_action( 'soliloquy_lightbox_box', 'soliloquy_pinterest_lightbox_settings' );
    add_filter( 'soliloquy_save_settings', 'soliloquy_pinterest_save', 10, 2 );
    add_filter( 'soliloquy_output_start', 'soliloquy_pinterest_style', 10, 2 );
    add_filter( 'soliloquy_output_start', 'soliloquy_pinterest_lightbox_style', 10, 2 );
    add_filter( 'soliloquy_output_link_attr', 'soliloquy_pinterest_lightbox_attr', 10, 5 );
    add_filter( 'soliloquy_output_after_link', 'soliloquy_pinterest_output', 10, 4 );
    add_action( 'soliloquy_api_end', 'soliloquy_pinterest_event' );
    add_action( 'soliloquy_lightbox_api_before_show', 'soliloquy_pinterest_lightbox' );

}

/**
 * Initializes the addon updater.
 *
 * @since 1.0.0
 *
 * @param string $key The user license key.
 */
function soliloquy_pinterest_updater( $key ) {

    $args = array(
        'plugin_name' => SOLILOQUY_PINTEREST_PLUGIN_NAME,
        'plugin_slug' => SOLILOQUY_PINTEREST_PLUGIN_SLUG,
        'plugin_path' => plugin_basename( __FILE__ ),
        'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . SOLILOQUY_PINTEREST_PLUGIN_SLUG,
        'remote_url'  => 'http://soliloquywp.com/',
        'version'     => SOLILOQUY_PINTEREST_PLUGIN_VERSION,
        'key'         => $key
    );
    $soliloquy_pinterest_updater = new Soliloquy_Updater( $args );

}

/**
 * Applies a default to the addon setting.
 *
 * @since 1.0.0
 *
 * @param array $defaults  Array of default config values.
 * @param int $post_id     The current post ID.
 * @return array $defaults Amended array of default config values.
 */
function soliloquy_pinterest_defaults( $defaults, $post_id ) {

    // Pinterest addon defaults.
    $defaults['pinterest']                   = 0;
    $defaults['pinterest_position']          = 'top_left';
    $defaults['pinterest_color']             = 'gray';
    $defaults['lightbox_pinterest']          = 0;
    $defaults['lightbox_pinterest_position'] = 'top_left';
    $defaults['lightbox_pinterest_color']    = 'gray';
    return $defaults;

}

/**
 * Filters in a new tab for the addon.
 *
 * @since 1.0.0
 *
 * @param array $tabs  Array of default tab values.
 * @return array $tabs Amended array of default tab values.
 */
function soliloquy_pinterest_tab_nav( $tabs ) {

    $tabs['pinterest'] = __( 'Pinterest', 'soliloquy-pinterest' );
    return $tabs;

}

/**
 * Adds addon setting to the Pinterest tab.
 *
 * @since 1.0.0
 *
 * @param object $post The current post object.
 */
function soliloquy_pinterest_settings( $post ) {

    $instance = Soliloquy_Metaboxes::get_instance();
    ?>
    <div id="soliloquy-pinterest">
        <p class="soliloquy-intro"><?php _e( 'The settings below adjust the Pinterest settings for the slider.', 'soliloquy-pinterest' ); ?></p>
        <table class="form-table">
            <tbody>
                <tr id="soliloquy-config-pinterest-box">
                    <th scope="row">
                        <label for="soliloquy-config-pinterest"><?php _e( 'Enable Pin It Button?', 'soliloquy-pinterest' ); ?></label>
                    </th>
                    <td>
                        <input id="soliloquy-config-pinterest" type="checkbox" name="_soliloquy[pinterest]" value="<?php echo $instance->get_config( 'pinterest', $instance->get_config_default( 'pinterest' ) ); ?>" <?php checked( $instance->get_config( 'pinterest', $instance->get_config_default( 'pinterest' ) ), 1 ); ?> />
                        <span class="description"><?php _e( 'Enables or disables the Pinterest "Pin It" button for slider images.', 'soliloquy-pinterest' ); ?></span>
                    </td>
                </tr>
                <tr id="soliloquy-config-pinterest-position-box">
                    <th scope="row">
                        <label for="soliloquy-config-pinterest-position"><?php _e( 'Pinterest Position', 'soliloquy-pinterest' ); ?></label>
                    </th>
                    <td>
                        <select id="soliloquy-config-pinterest-position" name="_soliloquy[pinterest_position]">
                            <?php foreach ( (array) soliloquy_pinterest_positions() as $i => $data ) : ?>
                                <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $instance->get_config( 'pinterest_position', $instance->get_config_default( 'pinterest_position' ) ) ); ?>><?php echo $data['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e( 'Sets the position of the Pinterest button on the slider images.', 'soliloquy-pinterest' ); ?></p>
                    </td>
                </tr>
                <tr id="soliloquy-config-pinterest-color-box">
                    <th scope="row">
                        <label for="soliloquy-config-pinterest-color"><?php _e( 'Pinterest Button Color', 'soliloquy-pinterest' ); ?></label>
                    </th>
                    <td>
                        <select id="soliloquy-config-pinterest-color" name="_soliloquy[pinterest_color]">
                            <?php foreach ( (array) soliloquy_pinterest_colors() as $i => $data ) : ?>
                                <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $instance->get_config( 'pinterest_color', $instance->get_config_default( 'pinterest_color' ) ) ); ?>><?php echo $data['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e( 'Sets the color of the Pin It button.', 'soliloquy-pinterest' ); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php

}

/**
 * Adds addon setting to the Lightbox tab.
 *
 * @since 1.0.0
 *
 * @param object $post The current post object.
 */
function soliloquy_pinterest_lightbox_settings( $post ) {

    $instance = Soliloquy_Metaboxes::get_instance();
    ?>
    <tr id="soliloquy-config-lightbox-pinterest-box">
        <th scope="row">
            <label for="soliloquy-config-lightbox-pinterest"><?php _e( 'Enable Pin It Button in Lightbox?', 'soliloquy-pinterest' ); ?></label>
        </th>
        <td>
            <input id="soliloquy-config-lightbox-pinterest" type="checkbox" name="_soliloquy[lightbox_pinterest]" value="<?php echo $instance->get_config( 'lightbox_pinterest', $instance->get_config_default( 'lightbox_pinterest' ) ); ?>" <?php checked( $instance->get_config( 'lightbox_pinterest', $instance->get_config_default( 'lightbox_pinterest' ) ), 1 ); ?> />
            <span class="description"><?php _e( 'Enables or disables the Pinterest "Pin It" button for lightbox images.', 'soliloquy-pinterest' ); ?></span>
        </td>
    </tr>
    <tr id="soliloquy-config-lightbox-pinterest-position-box">
        <th scope="row">
            <label for="soliloquy-config-lightbox-pinterest-position"><?php _e( 'Pinterest Position', 'soliloquy-pinterest' ); ?></label>
        </th>
        <td>
            <select id="soliloquy-config-lightbox-pinterest-position" name="_soliloquy[lightbox_pinterest_position]">
                <?php foreach ( (array) soliloquy_pinterest_positions() as $i => $data ) : ?>
                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $instance->get_config( 'lightbox_pinterest_position', $instance->get_config_default( 'lightbox_pinterest_position' ) ) ); ?>><?php echo $data['name']; ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php _e( 'Sets the position of the Pinterest button on the lightbox images.', 'soliloquy-pinterest' ); ?></p>
        </td>
    </tr>
    <tr id="soliloquy-config-lightbox-pinterest-color-box">
        <th scope="row">
            <label for="soliloquy-config-lightbox-pinterest-color"><?php _e( 'Pinterest Button Color', 'soliloquy-pinterest' ); ?></label>
        </th>
        <td>
            <select id="soliloquy-config-lightbox-pinterest-color" name="_soliloquy[lightbox_pinterest_color]">
                <?php foreach ( (array) soliloquy_pinterest_colors() as $i => $data ) : ?>
                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $instance->get_config( 'lightbox_pinterest_color', $instance->get_config_default( 'lightbox_pinterest_color' ) ) ); ?>><?php echo $data['name']; ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php _e( 'Sets the color of the Pin It button in the lightbox.', 'soliloquy-pinterest' ); ?></p>
        </td>
    </tr>
    <?php

}

/**
 * Saves the addon setting.
 *
 * @since 1.0.0
 *
 * @param array $settings  Array of settings to be saved.
 * @param int $pos_tid     The current post ID.
 * @return array $settings Amended array of settings to be saved.
 */
function soliloquy_pinterest_save( $settings, $post_id ) {

    $settings['config']['pinterest']                   = isset( $_POST['_soliloquy']['pinterest'] ) ? 1 : 0;
    $settings['config']['pinterest_position']          = esc_attr( $_POST['_soliloquy']['pinterest_position'] );
    $settings['config']['pinterest_color']             = esc_attr( $_POST['_soliloquy']['pinterest_color'] );
    $settings['config']['lightbox_pinterest']          = isset( $_POST['_soliloquy']['lightbox_pinterest'] ) ? 1 : 0;
    $settings['config']['lightbox_pinterest_position'] = esc_attr( $_POST['_soliloquy']['lightbox_pinterest_position'] );
    $settings['config']['lightbox_pinterest_color']    = esc_attr( $_POST['_soliloquy']['lightbox_pinterest_color'] );
    return $settings;

}

/**
 * Outputs the Pinterest button styles.
 *
 * @since 1.0.0
 *
 * @param string $output  The slider HTML output.
 * @param array $data     Array of slider data.
 * @return string $output Amended slider HTML output.
 */
function soliloquy_pinterest_style( $output, $data ) {

    $instance = Soliloquy_Shortcode::get_instance();
    if ( ! $instance->get_config( 'pinterest', $data ) ) {
        return $output;
    }

    // Since this CSS only needs to be defined once on a page, use static flag to help keep track.
    static $soliloquy_pinterest_css_flag = false;

    // If the tag has been set to true, return the default output.
    if ( $soliloquy_pinterest_css_flag ) {
        return $output;
    }

    // Build out our custom CSS.
    $css  = '<style type="text/css">';
        // Apply a base reset for all items in the filter list to avoid as many conflicts as possible.
        $css .= '.soliloquy-container .soliloquy-pinterest-share { background-color: transparent; transition: none; -moz-transition: none; -webkit-transition: none; }';
        $css .= '.soliloquy-container .soliloquy-pinterest-share:hover { background-position: 0 -28px; }';
        $css .= '.soliloquy-container .soliloquy-pinterest-share:active { background-position: 0 -56px; }';
        $css .= '.soliloquy-container .soliloquy-pinterest-share.soliloquy-pinterest-gray { background-image: url(' . plugins_url( 'images/pinterest-gray.png', __FILE__ ) . '); }';
        $css .= '.soliloquy-container .soliloquy-pinterest-share.soliloquy-pinterest-red { background-image: url(' . plugins_url( 'images/pinterest-red.png', __FILE__ ) . '); }';
        $css .= '.soliloquy-container .soliloquy-pinterest-share.soliloquy-pinterest-white { background-image: url(' . plugins_url( 'images/pinterest-white.png', __FILE__ ) . '); }';
        $css .= '@media only screen and (-webkit-min-device-pixel-ratio: 2),only screen and (min--moz-device-pixel-ratio: 2),only screen and (-o-min-device-pixel-ratio: 2/1), only screen and (min-device-pixel-ratio: 2),only screen and (min-resolution: 192dpi),only screen and (min-resolution: 2dppx) {';
            $css .= '.soliloquy-container .soliloquy-pinterest-share { background-size: 56px 84px; }';
            $css .= '.soliloquy-container .soliloquy-pinterest-share.soliloquy-pinterest-gray { background-image: url(' . plugins_url( 'images/pinterest-gray@2x.png', __FILE__ ) . '); }';
            $css .= '.soliloquy-container .soliloquy-pinterest-share.soliloquy-pinterest-red { background-image: url(' . plugins_url( 'images/pinterest-red@2x.png', __FILE__ ) . '); }';
            $css .= '.soliloquy-container .soliloquy-pinterest-share.soliloquy-pinterest-white { background-image: url(' . plugins_url( 'images/pinterest-white@2x.png', __FILE__ ) . '); }';
        $css .= '}';
    $css .= '</style>';

    // Set our flag to true.
    $soliloquy_pinterest_css_flag = true;

    // Return the minified CSS.
    $minify = $instance->minify( $css );
    return $css . $output;

}

/**
 * Outputs the Pinterest lightbox button styles.
 *
 * @since 1.0.0
 *
 * @param string $output  The slider HTML output.
 * @param array $data     Array of slider data.
 * @return string $output Amended slider HTML output.
 */
function soliloquy_pinterest_lightbox_style( $output, $data ) {

    $instance = Soliloquy_Shortcode::get_instance();
    if ( ! $instance->get_config( 'lightbox_pinterest', $data ) ) {
        return $output;
    }

    // Since this CSS only needs to be defined once on a page, use static flag to help keep track.
    static $soliloquy_pinterest_lightbox_css_flag = false;

    // If the tag has been set to true, return the default output.
    if ( $soliloquy_pinterest_lightbox_css_flag ) {
        return $output;
    }

    // Build out our custom CSS.
    $css  = '<style type="text/css">';
        // Apply a base reset for all items in the filter list to avoid as many conflicts as possible.
        $css .= '.soliloquybox-wrap .soliloquy-pinterest-share { background-color: transparent; transition: none; -moz-transition: none; -webkit-transition: none; }';
        $css .= '.soliloquybox-wrap .soliloquy-pinterest-share:hover { background-position: 0 -28px; }';
        $css .= '.soliloquybox-wrap .soliloquy-pinterest-share:active { background-position: 0 -56px; }';
        $css .= '.soliloquybox-wrap .soliloquy-pinterest-share.soliloquy-pinterest-gray { background-image: url(' . plugins_url( 'images/pinterest-gray.png', __FILE__ ) . '); }';
        $css .= '.soliloquybox-wrap .soliloquy-pinterest-share.soliloquy-pinterest-red { background-image: url(' . plugins_url( 'images/pinterest-red.png', __FILE__ ) . '); }';
        $css .= '.soliloquybox-wrap .soliloquy-pinterest-share.soliloquy-pinterest-white { background-image: url(' . plugins_url( 'images/pinterest-white.png', __FILE__ ) . '); }';
        $css .= '@media only screen and (-webkit-min-device-pixel-ratio: 2),only screen and (min--moz-device-pixel-ratio: 2),only screen and (-o-min-device-pixel-ratio: 2/1), only screen and (min-device-pixel-ratio: 2),only screen and (min-resolution: 192dpi),only screen and (min-resolution: 2dppx) {';
            $css .= '.soliloquybox-wrap .soliloquy-pinterest-share { background-size: 56px 84px; }';
            $css .= '.soliloquybox-wrap .soliloquy-pinterest-share.soliloquy-pinterest-gray { background-image: url(' . plugins_url( 'images/pinterest-gray@2x.png', __FILE__ ) . '); }';
            $css .= '.soliloquybox-wrap .soliloquy-pinterest-share.soliloquy-pinterest-red { background-image: url(' . plugins_url( 'images/pinterest-red@2x.png', __FILE__ ) . '); }';
            $css .= '.soliloquybox-wrap .soliloquy-pinterest-share.soliloquy-pinterest-white { background-image: url(' . plugins_url( 'images/pinterest-white@2x.png', __FILE__ ) . '); }';
        $css .= '}';
    $css .= '</style>';

    // Set our flag to true.
    $soliloquy_pinterest_lightbox_css_flag = true;

    // Return the minified CSS.
    $minify = $instance->minify( $css );
    return $css . $output;

}

/**
 * Adds the proper attributes to images for Pinterest output in the lightbox.
 *
 * @since 1.0.0
 *
 * @param string $attr  String of link attributes.
 * @param int $id       The current slider ID.
 * @param array $item   Array of slide data.
 * @param array $data   Array of slider data.
 * @param int $i        The current position in the slider.
 * @return string $attr Amended string of link attributes.
 */
function soliloquy_pinterest_lightbox_attr( $attr, $id, $item, $data, $i ) {

    // If there is no lightbox, don't output anything.
    $instance = Soliloquy_Shortcode::get_instance();
    if ( ! $instance->get_config( 'lightbox_pinterest', $data ) ) {
        return $attr;
    }

    // If the $post variable is not set, set the URL to the home page of the site.
    global $post;
    if ( isset( $post ) ) {
        $url = get_permalink( $post->ID );
    } else {
        $url = trailingslashit( get_home_url() );
    }
    $url = apply_filters( 'soliloquy_pinterest_url', $url, $id, $item, $data );

    // Set the style for the Pin It button.
    $style = '';
    switch ( $instance->get_config( 'lightbox_pinterest_position', $data ) ) {
        case 'top_left' :
        default :
            $style = 'top:10px;left:10px;';
            break;
        case 'top_right' :
            $style = 'top:10px;right:10px;';
            break;
        case 'bottom_right' :
            $style = 'bottom:10px;right:10px;';
            break;
        case 'bottom_left' :
            $style = 'bottom:10px;left:10px;';
            break;
    }

    // Set the description for the image.
    $title       = ! empty( $item['caption'] ) ? $item['caption'] : $item['title'];
    $description = apply_filters( 'soliloquy_pinterest_description', $title, $id, $item, $data );

    // Append the button to the image with styles.
    $output = apply_filters( 'soliloquy_pinterest_output', '<a class="soliloquy-pinterest-share soliloquy-pinterest-' . $instance->get_config( 'lightbox_pinterest_color', $data ) . '" href="http://pinterest.com/pin/create/button/?url=' . esc_url( $url ) . '&description=' . urlencode( strip_tags( $description ) ) . '&media=' . esc_url( $item['src'] ) . '" rel="nofollow" style="width:56px;height:28px;display:block;outline:none;position:absolute;z-index:9999999;' . $style . '"></a>', $id, $item, $data );
    $attr .= ' data-soliloquy-pinterest="' . esc_attr( $output ) . '"';

    return apply_filters( 'soliloquy_pinterest_lightbox_attr', $attr, $id, $item, $data, $i );

}

/**
 * Outputs the custom Pinterest to the specific slider.
 *
 * @since 1.0.0
 *
 * @global object $post   The current post object.
 * @param string $output  The HTML output for the slider.
 * @param int $id         The slider ID.
 * @param array $item     Array of data about the image.
 * @param array $data     Data for the slider.
 * @return string $output Amended slider HTML.
 */
function soliloquy_pinterest_output( $output, $id, $item, $data ) {

    // If there is no Pinterest button, return the default slider HTML.
    $instance = Soliloquy_Shortcode::get_instance();
    if ( ! $instance->get_config( 'pinterest', $data ) ) {
        return $output;
    }

    // If the $post variable is not set, set the URL to the home page of the site.
    global $post;
    if ( isset( $post ) ) {
        $url = get_permalink( $post->ID );
    } else {
        $url = trailingslashit( get_home_url() );
    }
    $url = apply_filters( 'soliloquy_pinterest_url', $url, $id, $item, $data );

    // Set the style for the Pin It button.
    $style = '';
    switch ( $instance->get_config( 'pinterest_position', $data ) ) {
        case 'top_left' :
        default :
            $style = 'top:10px;left:10px;';
            break;
        case 'top_right' :
            $style = 'top:10px;right:10px;';
            break;
        case 'bottom_right' :
            $style = 'bottom:10px;right:10px;';
            break;
        case 'bottom_left' :
            $style = 'bottom:10px;left:10px;';
            break;
    }

    // Set the description for the image.
    $title       = ! empty( $item['caption'] ) ? $item['caption'] : $item['title'];
    $description = apply_filters( 'soliloquy_pinterest_description', $title, $id, $item, $data );

    // Append the button to the image with styles.
    $output .= apply_filters( 'soliloquy_pinterest_output', '<a class="soliloquy-pinterest-share soliloquy-pinterest-' . $instance->get_config( 'pinterest_color', $data ) . '" href="http://pinterest.com/pin/create/button/?url=' . esc_url( $url ) . '&description=' . urlencode( strip_tags( $description ) ) . '&media=' . esc_url( $item['src'] ) . '" rel="nofollow" style="width:56px;height:28px;display:block;outline:none;position:absolute;z-index:99999;' . $style . '"></a>', $id, $item, $data );

    // Return the amended HTML.
    return $output;

}

/**
 * Output the JS to have the button click open in a new window.
 *
 * @since 1.0.0
 *
 * @param array $data Data for the slider.
 */
function soliloquy_pinterest_event( $data ) {

    // If there is no Pinterest button, do nothing.
    $instance = Soliloquy_Shortcode::get_instance();
    if ( ! $instance->get_config( 'pinterest', $data ) ) {
        return;
    }

    // Output JS to open button click in a new window.
    ob_start();
    ?>
    $(document).on('click', '.soliloquy-pinterest-share', function(e){
        e.preventDefault();
        window.open($(this).attr('href'), 'soliloquy-pinterest', 'menubar=1,resizable=1,width=760,height=360');
    });
    <?php
    echo ob_get_clean();

}

/**
 * Enables Pinterest inside lightboxes if it is enabled.
 *
 * @since 1.0.0
 *
 * @param array $data Data for the slider.
 */
function soliloquy_pinterest_lightbox( $data ) {

    // If there is no Pinterest button for the lightbox, do nothing.
    $instance = Soliloquy_Shortcode::get_instance();
    if ( ! $instance->get_config( 'lightbox_pinterest', $data ) ) {
        return;
    }

    ob_start();
    ?>
    if ( $(this.element).data('soliloquy-pinterest') ) {
        $(this.inner).append($(this.element).data('soliloquy-pinterest'));
    }
    <?php
    echo ob_get_clean();

}

/**
 * Returns the available Pinterest positions on the slider.
 *
 * @since 1.0.0
 *
 * @return array Array of Pinterest positions.
 */
function soliloquy_pinterest_positions() {

    $positions = array(
        array(
            'value' => 'top_left',
            'name'  => __( 'Top Left', 'soliloquy-pinterest' )
        ),
        array(
            'value' => 'top_right',
            'name'  => __( 'Top Right', 'soliloquy-pinterest' )
        ),
        array(
            'value' => 'bottom_left',
            'name'  => __( 'Bottom Left', 'soliloquy-pinterest' )
        ),
        array(
            'value' => 'bottom_right',
            'name'  => __( 'Bottom Right', 'soliloquy-pinterest' )
        )
    );

    return apply_filters( 'soliloquy_pinterest_positions', $positions );

}

/**
 * Returns the available Pinterest colors.
 *
 * @since 1.0.0
 *
 * @return array Array of Pinterest colors.
 */
function soliloquy_pinterest_colors() {

    $colors = array(
        array(
            'value' => 'gray',
            'name'  => __( 'Gray', 'soliloquy-pinterest' )
        ),
        array(
            'value' => 'red',
            'name'  => __( 'Red', 'soliloquy-pinterest' )
        ),
        array(
            'value' => 'white',
            'name'  => __( 'White', 'soliloquy-pinterest' )
        )
    );

    return apply_filters( 'soliloquy_pinterest_colors', $colors );

}