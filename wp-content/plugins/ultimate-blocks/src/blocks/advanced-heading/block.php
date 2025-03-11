<?php

function ub_render_advanced_heading_block( $attributes, $_, $block ) {
	$spacingStyles = ub_get_spacing_styles($attributes);
	$block_attrs = $block->parsed_block['attrs'];

	$styles = ( $attributes['alignment'] === 'none' ? '' : 'text-align: ' . $attributes['alignment'] . ';' ) .
		( $attributes['textColor'] ? 'color: ' . $attributes['textColor'] . ';' : '' ) .
		( $attributes['backgroundColor'] ? 'background-color: ' . $attributes['backgroundColor'] . ';' : '' ) .
		( $attributes['fontSize'] ? 'font-size: ' . $attributes['fontSize'] . 'px;' : '' ) .
		'letter-spacing: ' . $attributes['letterSpacing'] . 'px;' .
		'text-transform: ' . $attributes['textTransform'] . ';' .
		'font-family: ' . $attributes['fontFamily'] . ';' . $spacingStyles .
		'font-weight: ' . $attributes['fontWeight'] . ';' .
		( $attributes['lineHeight'] ? 'line-height: ' . $attributes['lineHeight'] . 'px;' : '' );
	$padding = Ultimate_Blocks\includes\get_spacing_css( isset($block_attrs['padding']) ? $block_attrs['padding'] : array() );
	$margin  = Ultimate_Blocks\includes\get_spacing_css( isset($block_attrs['margin']) ? $block_attrs['margin'] : array() );

	$wrapper_padding = array(
		'padding-top'        => isset($padding['top']) ? $padding['top'] : "",
		'padding-left'       => isset($padding['left']) ? $padding['left'] : "",
		'padding-right'      => isset($padding['right']) ? $padding['right'] : "",
		'padding-bottom'     => isset($padding['bottom']) ? $padding['bottom'] : "",
		'margin-top'         => !empty($margin['top']) ? $margin['top']  : "",
		'margin-left'        => !empty($margin['left']) ? $margin['left']  : "",
		'margin-right'       => !empty($margin['right']) ? $margin['right']  : "",
		'margin-bottom'      => !empty($margin['bottom']) ? $margin['bottom']  : "",
	);
	$styles .= Ultimate_Blocks\includes\generate_css_string($wrapper_padding);
	extract( $attributes );
	$classes                  = array( 'ub_advanced_heading' );
	$ids                      = array();
	$ids[]                    = 'ub-advanced-heading-' . esc_attr($blockID);
	$block_wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => implode( ' ', $classes ),
			'id'    => implode( ' ', $ids ),
			'style' => $styles,
		)
	);

	// Don't allow img and script tags for heading content.
	$cleaned_content = preg_replace( '/<img[^>]+>/i', '', $content );
	$cleaned_content = preg_replace( '/<script[^>]*?>.*?<\/script>/is', '', $cleaned_content );

	if (!in_array($level, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
		$level = 'h1';
	}

	$final_content = '<' . esc_attr($level) . ' ' . $block_wrapper_attributes . ' data-blockid="' . esc_attr($blockID) . '">' . $cleaned_content . '</' . esc_attr($level) . '>';

	return wp_kses_post( $final_content );
}

function ub_register_advanced_heading_block() {
	if ( function_exists( 'register_block_type_from_metadata' ) ) {
		require dirname( dirname( __DIR__ ) ) . '/defaults.php';
		register_block_type_from_metadata(
			dirname( dirname( dirname( __DIR__ ) ) ) . '/dist/blocks/advanced-heading',
			array(
				'attributes'      => $defaultValues['ub/advanced-heading']['attributes'],
				'render_callback' => 'ub_render_advanced_heading_block',
			)
		);
	}
}

add_action( 'init', 'ub_register_advanced_heading_block' );
