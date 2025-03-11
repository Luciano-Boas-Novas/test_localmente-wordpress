<?php

require_once(RDSM_SRC_DIR . '/events/rdsm_events_interface.php');

class RDSMOauthDisconnected implements RDSMEventsInterface {
  public function register_hooks() {
    add_action('wp_ajax_rdsm-disconnect-oauth',  array($this, 'oauth_disconnected_hooks'));
  }

  public function oauth_disconnected_hooks() {
    if (!isset($_POST['rd_form_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rd_form_nonce'])), 'rd-form-nonce')) {
      wp_die( '0', 400 );
    }
    delete_option('rdsm_public_token');
    delete_option('rdsm_private_token');
    delete_option('rdsm_access_token');
    delete_option('rdsm_refresh_token');

    die();
  }
}
