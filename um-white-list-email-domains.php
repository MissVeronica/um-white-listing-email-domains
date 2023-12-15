<?php
/**
 * Plugin Name:     Ultimate Member - White Listing Email Domains/Addresses
 * Description:     Extension to Ultimate Member for white listing email domains and email addresses. Settings at UM Settings -> Access -> Other
 * Version:         3.1.0
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


Class UM_White_Listing_Email_Domains {

    function __construct() {

        add_action( 'um_submit_form_errors_hook__blockedemails', array( $this, 'white_listed_email_domains' ), 20, 1 );
        add_filter( 'um_settings_structure',                     array( $this, 'um_settings_structure_white_listed_email_domains' ), 10, 2 );
    }

    public function white_listed_email_domains( $args ) {

        if ( isset( $args['form_id'] ) && is_numeric( $args['form_id'] )) {

            $white_list_forms = UM()->options()->get( 'white_listed_email_domains_forms' );
            if ( ! empty( $white_list_forms ) && is_array( $white_list_forms )) {

                $white_list_forms = array_map( 'sanitize_text_field', $white_list_forms );

                if ( is_array( $white_list_forms ) && in_array( $args['form_id'], $white_list_forms )) {

                    $white_list = UM()->options()->get( 'white_listed_email_domains' );
                    if ( ! empty( $white_list )) {

                        $valid_email_domains = array_map( 'strtolower', array_map( 'trim', explode( "\n", $white_list )));

                        $this->validate_white_listed_emails( $args['user_email'], $valid_email_domains );
                        $this->validate_white_listed_emails( $args['username'],   $valid_email_domains );
                    }
                }
            }
        }
    }

    public function validate_white_listed_emails( $user_email, $valid_email_domains ) {

        if ( isset( $user_email ) && is_email( $user_email )) {

            $email_domain = array_map( 'strtolower', explode( '@', $user_email ));

            if ( ! in_array( $email_domain[1], $valid_email_domains ) &&
                 ! in_array( strtolower( $user_email ), $valid_email_domains )) {

                exit( wp_redirect( esc_url( add_query_arg( 'err', 'blocked_email' ))));
            }
        }
    }

    public function get_form_ids_register() {

        $um_form_ids = array( '' );
        $um_forms = get_posts( array( 'post_type' => 'um_form', 'numberposts' => -1, 'post_status' => array( 'publish' )));

        if ( ! empty( $um_forms )) {
            foreach ( $um_forms as $um_form ) {

                $um_form_meta = get_post_meta( $um_form->ID );
                if ( isset( $um_form_meta['_um_mode'][0] ) && $um_form_meta['_um_mode'][0] == 'register' ) {

                    $um_form_ids[$um_form->ID] = $um_form->post_title;
                }
            }
        }

        return $um_form_ids;
    }

    public function um_settings_structure_white_listed_email_domains( $settings ) {

        $settings['access']['sections']['other']['fields'][] = array(
                    'id'      => 'white_listed_email_domains',
                    'type'    => 'textarea',
                    'label'   => __( 'White_Listing_Email_Domains - One entry per line', 'ultimate-member' ),
                    'tooltip' => __( 'Enter one per line either domain names and/or user email addresses. This will block other e-mail addresses from being able to sign up to your site.', 'ultimate-member' ),
                    'size'    => 'medium',
                );

        $settings['access']['sections']['other']['fields'][] = array(
                    'id'      => 'white_listed_email_domains_forms',
                    'type'    => 'select',
                    'multi'   => true,
                    'label'   => __( 'White_Listing_Email_Domains - Include these UM Forms in testing', 'ultimate-member' ),
                    'options' => $this->get_form_ids_register(),
                    'tooltip' => __( 'The UM Forms where these White listed e-mail addresses are tested.', 'ultimate-member' ),
                );

        return $settings;
    }

}

new UM_White_Listing_Email_Domains();
