<?php
/**
 * Plugin Name:     Ultimate Member - White Listing Email Domains
 * Description:     Extension to Ultimate Member for white listing email domains. Settings at UM Settings -> Access -> Other
 * Version:         1.0.0
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.4.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; 
if ( ! class_exists( 'UM' ) ) return;


add_action( 'um_submit_form_errors_hook__blockedemails', 'white_listed_email_domains', 10, 1 );
add_filter( 'um_settings_structure', 'um_settings_structure_white_listed_email_domains', 10, 2 );

function white_listed_email_domains( $args ) {
    
    $white_list = UM()->options()->get( 'white_listed_email_domains' );
    if( empty( $white_list )) return;
    $valid_email_domains = array_map( 'rtrim', explode( "\n", strtolower( $white_list )));

    if( isset( $args['user_email'] ) && is_email( $args['user_email'] )) {
        $email_domain = explode( '@', $args['user_email'] );        
        if( !in_array( strtolower( $email_domain[1] ), $valid_email_domains )) {
            exit( wp_redirect( esc_url( add_query_arg( 'err', 'blocked_email' ))));
        }
    }

    if( isset( $args['username'] ) && is_email( $args['username'] )) {
        $email_domain = explode( '@', $args['username'] );        
        if( !in_array( strtolower( $email_domain[1] ), $valid_email_domains )) {
            exit( wp_redirect( esc_url( add_query_arg( 'err', 'blocked_email' ))));
        }
    }
}

function um_settings_structure_white_listed_email_domains( $settings ) {

    $settings['access']['sections']['other']['fields'][] = array(
        'id'      => 'white_listed_email_domains',
        'type'    => 'textarea',
        'label'   => __( 'White Listed Email Domains (Enter one email domain per line)', 'ultimate-member' ),
        'tooltip' => __( 'This will block other e-mail addresses from being able to sign up or sign in to your site.', 'ultimate-member' ),
    );

    return $settings;
}
