<?php

require_once(RDSM_SRC_DIR . '/entities/rdsm_user_credentials.php');
require_once(RDSM_SRC_DIR . '/events/rdsm_events_interface.php');

class RDSMOauthConnected implements RDSMEventsInterface {
  public function register_hooks() {
    add_action('wp_ajax_rd-persist-tokens', array($this, 'persist_tokens'), 1);
    add_action('wp_ajax_rd-persist-legacy-tokens',  array($this, 'persist_legacy_tokens'), 1);
  }

  public static function persist_tokens() {
    if (!isset($_POST['rd_form_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rd_form_nonce'])), 'rd-form-nonce')) {
      wp_die( '0', 400 );
    }

    if (!isset($_POST['accessToken']) || !isset($_POST['refreshToken'])) {
      wp_die('0', 400);
    }

    $access_token_value = sanitize_text_field(wp_unslash($_POST['accessToken']));
    $refresh_token_value = sanitize_text_field(wp_unslash($_POST['refreshToken']));

    $user_credentials = new RDSMUserCredentials($access_token_value, $refresh_token_value);

    $user_credentials->save_access_token($access_token_value);
    $user_credentials->save_refresh_token($refresh_token_value);

    wp_die(wp_kses_post($user_credentials));
  }

  public function persist_legacy_tokens() {
    if (!isset($_POST['rd_form_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rd_form_nonce'])), 'rd-form-nonce')) {
      wp_die( '0', 400 );
    }

    if (!isset($_POST['tokens'][0])) {
      wp_die('0', 400);
    }

    $tokens = sanitize_text_field(wp_unslash($_POST['tokens'][0]));
    $user_credentials = new RDSMLegacyUserCredentials();
    $user_credentials->save_public_token($tokens['public']);
    $user_credentials->save_private_token($tokens['private']);

    die();
  }
}
