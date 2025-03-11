<?php

require_once('settings_menu.php');
require_once('settings_sections.php');
require_once('settings_fields.php');

function initialize_rdstation_settings_page() {
  register_setting('rdsm_general_settings', 'rdsm_general_settings');
  register_setting('rdsm_woocommerce_settings', 'rdsm_woocommerce_settings');

  $sections = new RDSettingsSection;
  $sections->register_sections();

  $fields = new RDSettingsFields;
  $fields->register_fields();
}

function rdstation_settings_page_callback() {
  $active_tab = rdsm_active_tab();
  ?>

  <h1>
    <?php esc_html_e('RD Station Integration Settings', 'integracao-rd-station') ?>
  </h1>

  <div class="rd-oauth-connect-section">
    <p>
      <?php esc_html_e('It automatically activates the RD Station Marketing tracking code in your Wordpress pages, enabling features such as Lead Tracking and Pop ups. It also integrates the contact forms that capture Leads that convert in your website forms directly to RD Station Marketing.', 'integracao-rd-station') ?>
    </p>

    <div class="rdsm-connected hidden">
      <p>
        <?php esc_html_e('The plugin is connected and allows the use of Tracking Code, pop-ups and form integrations. If you want to disconnect, just click the button below.', 'integracao-rd-station') ?>
      </p>

      <button type="button" class="button rd-oauth-disconnect" id="rdsm-oauth-disconnect">
        <?php esc_html_e('Disconnect from RD Station', 'integracao-rd-station') ?>
      </button>
    </div>

    <div class="rdsm-disconnected hidden">
      <p>
        <?php esc_html_e('To continue to install the Tracking Code, we need first to confirm your access permissions.', 'integracao-rd-station') ?>
      </p>
      <button type="button" class="button rd-oauth-integration" id="rdsm-oauth-connect">
        <?php esc_html_e('Connect to RD Station', 'integracao-rd-station') ?>
      </button>
    </div>
  </div>

  <p class="nav-tab-wrapper">
    <a href="?page=rdstation-settings-page&tab=general" class="nav-tab <?php echo esc_attr(rdsm_tab_class('general')) ?>">
      <?php esc_html_e('General Settings', 'integracao-rd-station') ?>
    </a>

    <a href="?page=rdstation-settings-page&tab=woocommerce" class="nav-tab <?php echo esc_attr(rdsm_tab_class('woocommerce')) ?>">
      <?php esc_html_e('WooCommerce', 'integracao-rd-station') ?>
    </a>

    <a href="?page=rdstation-settings-page&tab=integrations_log" class="nav-tab <?php echo esc_attr(rdsm_tab_class('integrations_log')) ?>">
      <?php esc_html_e('Log', 'integracao-rd-station') ?>
    </a>
  </p>

  <form action='options.php' method='post'>
  <input id="rd_form_nonce" name="rd_form_nonce" type="hidden" value="<?php echo esc_attr(wp_create_nonce('rd-form-nonce'))?>" />
    <?php
      switch ($active_tab) {
        case 'general':
          settings_fields('rdsm_general_settings');
          do_settings_sections('rdsm_general_settings');
          rdsm_tracking_code_warning_html();
          submit_button();
          break;
        case 'woocommerce':
          settings_fields('rdsm_woocommerce_settings');
          do_settings_sections('rdsm_woocommerce_settings');
          submit_button();
          break;
        case 'integrations_log':
          do_settings_sections('rdsm_integrations_log_settings');
          break;
      }
    ?>
  </form>

  <?php
}

function rdsm_integrations_log_html() {
  $options = get_option( 'rdsm_integrations_log_settings' ); 
  if (RDSMLogFileHelper::has_error()) { ?>
    <h3 class="alert-box"><?php esc_html_e('There are conversions that returned an error, check the log for more information', 'integracao-rd-station') ?></h3>
  <?php } ?>
  <a class="button" href="#" onclick="copyLogToClipboard()"><?php esc_html_e("Encrypt and Copy", 'integracao-rd-station')?></a>
  <a class="button" href="#" onclick="if(confirm('After this action, the data could not be recovered. Are you sure you want to clear the log?')) clearLog();"><?php esc_html_e("Clear Log", 'integracao-rd-station')?></a>
  <textarea readonly id="rdsm_log_screen" rows="50"></textarea>
  <?php
}

function rdsm_woocommerce_conversion_identifier_html() {
  $options = get_option( 'rdsm_woocommerce_settings' ); ?>
  <input type='text' name='rdsm_woocommerce_settings[conversion_identifier]' size="32" value='<?php echo esc_attr($options['conversion_identifier']); ?>'>
  <?php
}

function rdsm_woocommerce_field_mapping_html() {
  $options = get_option( 'rdsm_woocommerce_settings' );
  $field_mapping = $options['field_mapping'];
  $hidden = (empty($field_mapping)) ? "" : "hidden";
  
  ?>  
  <div class="rdsm-disconnected-box hidden">
    <h3 id="info_check_login">
      <?php esc_html_e('You need to connect to RD Station to map the fields, click in \'Connect to RD Station\' to login', 'integracao-rd-station') ?>
    </h3>
  </div>

  <div class="rdsm-connected-box hidden">
    <?php if (RDSMLogFileHelper::has_error()) { ?>
      <h3 class="alert-box"><?php esc_html_e('There are conversions that returned an error, check the log for more information', 'integracao-rd-station') ?></h3>
    <?php } ?>
    <h4 id="map_fields_title">
      <?php esc_html_e('Map the fields below according to their names in RD Station.', 'integracao-rd-station') ?>
    </h4>
    <?php echo "<h3 id=\"info_mapped_fields\" class=\"".esc_attr($hidden)."\">" ?>
      <?php esc_html_e('The fields on this form have not yet been mapped, you can configure them below or ', 'integracao-rd-station') ?>
      <a href="https://ajuda.rdstation.com.br/hc/pt-br/articles/360054981272" target="_blank" style="color: white;">
        <?php esc_html_e('click here for more information', 'integracao-rd-station') ?>
      </a>
    </h3>
    <a class="button" style="margin-left: 314px;" onclick="showInfoCreateFieldRDSM()" href="https://app.rdstation.com.br/campos-personalizados/novo" target="_blank"><?php esc_html_e("Create field in RDSM", 'integracao-rd-station')?></a>
    <h3 id="info_create_fields" class="hidden"><?php esc_html_e('To see the fields created in RDSM reload page.', 'integracao-rd-station') ?></h3>  
    <?php echo "<div id=\"rdsm_fields\" 
                  data-nome=\""       .esc_attr($field_mapping["nome"])."\"
                  data-sobrenome=\""  .esc_attr($field_mapping["sobrenome"])."\"
                  data-email=\""      .esc_attr($field_mapping["email"])."\"
                  data-telefone=\""   .esc_attr($field_mapping["telefone"])."\"
                  data-empresa=\""    .esc_attr($field_mapping["empresa"])."\"
                  data-país=\""       .esc_attr($field_mapping["país"])."\"
                  data-endereço=\""   .esc_attr($field_mapping["endereço"])."\"
                  data-endereço2=\""  .esc_attr($field_mapping["endereço2"])."\"
                  data-cidade=\""     .esc_attr($field_mapping["cidade"])."\"
                  data-estado=\""     .esc_attr($field_mapping["estado"])."\"
                  data-cep=\""        .esc_attr($field_mapping["cep"])."\"
                  data-produtos=\""   .esc_attr($field_mapping["produtos"])."\"
                ></div>" 
    ?>    
  </div>
  <?php
}

function rdsm_enable_tracking_code_html() {
  $options = get_option( 'rdsm_general_settings' );
  $current_value = isset($options['enable_tracking_code']) ? $options['enable_tracking_code'] : ''; ?>

  <label class="checkbox-switch">
    <input type="checkbox" id="rdsm-enable-tracking" name="rdsm_general_settings[enable_tracking_code]" value="1" <?php checked($current_value, 1); ?> >
    <span class="checkbox-slider checkbox-slider-round">
      <span class="checkbox-slider-off hidden"><?php esc_html_e('Off', 'integracao-rd-station') ?></span>
      <span class="checkbox-slider-on hidden"><?php esc_html_e('On', 'integracao-rd-station') ?></span>
    </span>
  </label>
  <small id="rdsm-tracking-warning" class="hidden"> <?php esc_html_e('You need to connect to RD Station before enable/disable this feature.', 'integracao-rd-station') ?> </small>
  <?php
}

function rdsm_tracking_code_warning_html() { ?>
  <div class="rdsm-tracking-code-validation-warning">
    <div class="notice notice-warning">
      <p>
        <?php esc_html_e('Warning: validate it on RD Station Marketing:', 'integracao-rd-station') ?>
        <a href="https://app.rdstation.com.br/configuracoes/analise-e-monitoramento" target="_blank">
          <?php esc_html_e('Validate', 'integracao-rd-station') ?>
        </a>
      </p>
    </div>
    <div class="notice notice-warning">
      <p>
        <?php esc_html_e("Some custom themes don't include the WordPress global footer, causing the tracking code to not work.", 'integracao-rd-station'); ?>
      </p>
    </div>
  </div>
<?php }


// HELPER METHODS
function rdsm_active_tab() {
  return $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'general';
}

function rdsm_tab_class($tab) {
  return $tab == rdsm_active_tab() ? 'nav-tab-active' : '';
}
