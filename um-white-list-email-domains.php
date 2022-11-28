<?php
/**
 * Plugin Name:     Ultimate Member - White Listing Email Domains/Addresses
 * Description:     Extension to Ultimate Member for white listing email domains and email addresses. Settings at UM Settings -> Access -> Other
 * Version:         2.1.1
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


add_action( 'um_submit_form_errors_hook__blockedemails', 'white_listed_email_domains', 20, 1 );
add_filter( 'um_settings_structure', 'um_settings_structure_white_listed_email_domains', 10, 2 );

function white_listed_email_domains( $args ) {
    
    $white_list_forms = UM()->options()->get( 'white_listed_email_domains_forms' );
    if( !empty( $white_list_forms )) {
        $white_list_forms = explode( ',', str_replace( ' ', '', $white_list_forms ));
        if( !in_array( $args['form_id'], $white_list_forms )) return;
    }

    $white_list = UM()->options()->get( 'white_listed_email_domains' );
    if( empty( $white_list )) return;
    $valid_email_domains = array_map( 'rtrim', explode( "\n", strtolower( $white_list )));

    if( isset( $args['user_email'] ) && is_email( $args['user_email'] )) {
        $email_domain = explode( '@', $args['user_email'] );        
        if( !in_array( strtolower( $email_domain[1] ), $valid_email_domains ) &&
            !in_array( strtolower( $args['user_email'] ), $valid_email_domains )) {
            exit( wp_redirect( esc_url( add_query_arg( 'err', 'blocked_email' ))));
        }
    }

    if( isset( $args['username'] ) && is_email( $args['username'] )) {
        $email_domain = explode( '@', $args['username'] );        
        if( !in_array( strtolower( $email_domain[1] ), $valid_email_domains ) &&
            !in_array( strtolower( $args['username'] ), $valid_email_domains )) {
            exit( wp_redirect( esc_url( add_query_arg( 'err', 'blocked_email' ))));
        }
    }
}

function um_settings_structure_white_listed_email_domains( $settings ) {

    $settings['access']['sections']['other']['fields'][] = array(
        'id'      => 'white_listed_email_domains',
        'type'    => 'textarea',
        'label'   => __( 'White Listed Email Domains/Addresses (One entry per line)', 'ultimate-member' ),
        'tooltip' => __( 'This will block other e-mail addresses from being able to sign up or sign in to your site.', 'ultimate-member' ),
    );

    $settings['access']['sections']['other']['fields'][] = array(
        'id'      => 'white_listed_email_domains_forms',
        'type'    => 'text',
        'label'   => __( 'White Listed Email Forms (Enter form_id comma separated)', 'ultimate-member' ),
        'tooltip' => __( 'The UM Form IDs where these White listed e-mail addresses are tested.', 'ultimate-member' ),
    );

    return $settings;
}
