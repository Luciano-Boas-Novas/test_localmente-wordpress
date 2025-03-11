<?php

class RD_Metabox {
	public $plugin_prefix;

	public function __construct($plugin_prefix){
		$this->plugin_prefix = $plugin_prefix;

		add_action( 'add_meta_boxes', array($this, 'rd_create_meta_boxes' ) );
		add_action( 'save_post', array($this, 'rd_save_meta_boxes' ) );
	}

	public function rd_create_meta_boxes(){
		add_meta_box(
      'form_identifier_box',
      __('Conversion identifier', 'integracao-rd-station'),
      array($this, 'form_identifier_box_content'),
      $this->plugin_prefix.'_integrations',
      'normal'
	  );

	  add_meta_box(
      'form_id_box',
      __('Select a form to integrate with RD Station', 'integracao-rd-station'),
      array($this, 'form_id_box_content'),
      $this->plugin_prefix.'_integrations',
      'normal'
	  );
	}

	public function form_identifier_box_content() {
	    $identifier = get_post_meta(get_the_ID(), 'form_identifier', true);
	    $use_post_title = get_post_meta(get_the_ID(), 'use_post_title', true); ?>
		<input id="rd_form_nonce" name="rd_form_nonce" type="hidden" value="<?php echo esc_attr(wp_create_nonce('rd-form-nonce'))?>" />
	    <input type="text" name="form_identifier" value="<?php echo esc_attr($identifier); ?>">
	    <span class="rd-integration-tips">
				<?php esc_html_e('This identifier will help you to identify the Lead source.', 'integracao-rd-station') ?>
			</span>
	    <?php
	}

	public function rd_save_meta_boxes($post_id) {
		if (!isset($_POST['rd_form_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rd_form_nonce'])), 'rd-form-nonce')) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return $post_id;
		}

		if (isset($_POST['form_identifier'])) {
			$form_identifier = sanitize_text_field(wp_unslash($_POST['form_identifier']));
			update_post_meta($post_id, 'form_identifier', $form_identifier);
		}

		if (isset($_POST['use_post_title'])) {
			$use_post_title = sanitize_text_field(wp_unslash($_POST['use_post_title']));
			update_post_meta($post_id, 'use_post_title', $use_post_title);
		}

		if (isset($_POST['form_id'])) {
			$form_id = sanitize_text_field(wp_unslash($_POST['form_id']));
			update_post_meta($post_id, 'form_id', $form_id);
		}

		if (isset($_POST['gf_mapped_fields'])) {
			$gf_mapped_fields = array_map('sanitize_text_field', wp_unslash($_POST['gf_mapped_fields']));
			update_post_meta($post_id, 'gf_mapped_fields_' . $form_id, $gf_mapped_fields);
		}

		if (isset($_POST['cf7_mapped_fields'])) {
			$cf7_mapped_fields = array_map('sanitize_text_field', wp_unslash($_POST['cf7_mapped_fields']));
			update_post_meta($post_id, 'cf7_mapped_fields_' . $form_id, $cf7_mapped_fields);
		}
	}
}

?>
