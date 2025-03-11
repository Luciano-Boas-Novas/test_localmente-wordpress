<?php

/**
 * Enqueue frontend script for content toggle block
 *
 * @return void
 */

function ub_render_tab_block($attributes, $contents){
    extract($attributes);
    return '<div role="tabpanel" class="wp-block-ub-tabbed-content-tab-content-wrap '.
        ($isActive ? 'active' : 'ub-hide') . (isset($className) ? ' ' . esc_attr($className) : '') . '"
        id="ub-tabbed-content-' . esc_attr($parentID) . '-panel-' . esc_attr($index) . '" aria-labelledby="ub-tabbed-content-' . esc_attr($parentID) . '-tab-' . esc_attr($index) . '" tabindex="0">'
        . $contents . '</div>';
}

if ( !class_exists( 'ub_simple_html_dom_node' ) ) {
    require dirname( dirname( __DIR__ ) ) . '/simple_html_dom.php';
}

function ub_register_tab_block(){
    if(function_exists('register_block_type_from_metadata')){
        require dirname(dirname(__DIR__)) . '/defaults.php';
        register_block_type_from_metadata( dirname(dirname(dirname(__DIR__))) . '/dist/blocks/tabbed-content/components/block.json', array(
            'attributes' => $defaultValues['ub/tab-block']['attributes'],
            'render_callback' =>  'ub_render_tab_block'));
    }
}

function ub_render_tabbed_content_block($attributes, $contents, $block){
    extract($attributes);
    $blockName = 'wp-block-ub-tabbed-content';

    $tabs = '';

    $contents = ub_str_get_html('<div id="tabarray">' . $contents . '</div>', $lowercase=true, $forceTagsClosed=true, $target_charset = UB_DEFAULT_TARGET_CHARSET, $stripRN=false)
                    ->find('#tabarray > .wp-block-ub-tabbed-content-tab-content-wrap');

    $tabContents = [];
	$block_attrs = $block->parsed_block['attrs'];
	$tab_contents_styles = array(
		'border-top-left-radius' => !empty( $block_attrs['tabContentsBorderRadius']['topLeft'] ) ? esc_attr($block_attrs['tabContentsBorderRadius']['topLeft']) . ';': "",
		'border-top-right-radius' => !empty( $block_attrs['tabContentsBorderRadius']['topRight'] ) ?  esc_attr($block_attrs['tabContentsBorderRadius']['topRight']) . ';': "",
		'border-bottom-left-radius' => !empty( $block_attrs['tabContentsBorderRadius']['bottomLeft'] ) ?  esc_attr($block_attrs['tabContentsBorderRadius']['bottomLeft']) . ';': "",
		'border-bottom-right-radius' => !empty( $block_attrs['tabContentsBorderRadius']['bottomRight'] ) ?  esc_attr($block_attrs['tabContentsBorderRadius']['bottomRight']) . ';': "",
	);

	foreach ($contents as $key => $content) {
        if($useAnchors){
            if(isset($tabsAnchor[$key]) && $tabsAnchor[$key] !== ''){
                $content->{'data-tab-anchor'} = esc_attr($tabsAnchor[$key]);
            }
        }
        $tabContent = $content->outertext;
        if(preg_match('/^<div role="tabpanel" class="wp-block-ub-tabbed-content-tab-content-wrap active"/', $tabContent)){
            $accordionIsActive = true;
        }
        else{
            $accordionIsActive = false;
        }

        if($tabletTabDisplay === 'accordion' || $mobileTabDisplay === 'accordion'){
            $content = '<div class="' . $blockName . '-accordion-toggle'.
            ($accordionIsActive ? ' active' : '') .
            ($tabletTabDisplay === 'accordion' ? ' ub-tablet-display' : '') .
            ($mobileTabDisplay === 'accordion' ? ' ub-mobile-display' : '') .
            '">' . wp_kses_post($tabsTitle[$key]) . '</div>' . $tabContent;
            array_push($tabContents, $content);
        }
        else{
            array_push($tabContents, $content->outertext);
        }
    }

	foreach($tabsTitle as $key=>$title){

		$tab_buttons_styles = array(
			'border-top-left-radius' => !empty( $block_attrs['tabButtonsBorderRadius']['topLeft'] ) ? esc_attr($block_attrs['tabButtonsBorderRadius']['topLeft']) . ';': "",
			'border-top-right-radius' => !empty( $block_attrs['tabButtonsBorderRadius']['topRight'] ) ?  esc_attr($block_attrs['tabButtonsBorderRadius']['topRight']) . ';': "",
			'border-bottom-left-radius' => !empty( $block_attrs['tabButtonsBorderRadius']['bottomLeft'] ) ?  esc_attr($block_attrs['tabButtonsBorderRadius']['bottomLeft']) . ';': "",
			'border-bottom-right-radius' => !empty( $block_attrs['tabButtonsBorderRadius']['bottomRight'] ) ?  esc_attr($block_attrs['tabButtonsBorderRadius']['bottomRight']) . ';': "",
		);

		$tabs .= sprintf(
			'<div role="tab" id="ub-tabbed-content-%1$s-tab-%2$s" aria-controls="ub-tabbed-content-%1$s-panel-%2$s" aria-selected="%3$s" class="%4$s-tab-title-%5$swrap%6$s%7$s%8$s" style="%9$s" tabindex="-1">
				<div class="%4$s-tab-title">%10$s</div>
			</div>',
			esc_attr($blockID), // 1
			esc_attr($key), // 2
			json_encode($activeTab === $key), // 3
			$blockName, // 4
			($tabVertical ? 'vertical-' : ''), // 5
			($mobileTabDisplay === 'verticaltab' ? ' ' . $blockName . '-tab-title-mobile-vertical-wrap' : ''), // 6
			($tabletTabDisplay === 'verticaltab' ? ' ' . $blockName . '-tab-title-tablet-vertical-wrap' : ''), // 7
			($activeTab === $key ? ' active' : ''), // 8
			Ultimate_Blocks\includes\generate_css_string($tab_buttons_styles), //9
			wp_kses_post($title) // 10
		);
	}

    $mobileTabStyle = substr($mobileTabDisplay, 0, strlen($mobileTabDisplay) - 3);
    $tabletTabStyle = substr($tabletTabDisplay, 0, strlen($tabletTabDisplay) - 3);

	return sprintf(
		'<div class="wp-block-ub-tabbed-content-block %1$s%2$s %3$s-holder%4$s%5$s%6$s%7$s%8$s"%9$s%10$s>
			<div class="%3$s-tab-holder%11$s%12$s%13$s">
				<div role="tablist" class="%3$s-tabs-title%14$s%15$s%16$s">%17$s</div>
			</div>
			<div class="%3$s-tabs-content%18$s%19$s%20$s" style="%22$s">%21$s</div>
		</div>',
		$blockName, // 1
		($tabStyle !== 'tabs' ? '-' . esc_attr($tabStyle) : ''), // 2
		$blockName, // 3
		($tabVertical ? ' vertical-holder' : ''), // 4
		(isset($className) ? ' ' . esc_attr($className) : ''), // 5
		(isset($align) ? ' align' . $align : ''), // 6
		($mobileTabDisplay !== 'accordion' ? ' ' . $blockName . '-' . $mobileTabStyle . '-holder-mobile' : ''), // 7
		($tabletTabDisplay !== 'accordion' ? ' ' . $blockName . '-' . $tabletTabStyle . '-holder-tablet' : ''), // 8
		($blockID === '' ? '' : ' id="ub-tabbed-content-' . esc_attr($blockID) . '"'), // 9
		($mobileTabDisplay === 'accordion' || $tabletTabDisplay === 'accordion' ? ' data-active-tabs="[' . esc_attr($activeTab) . ']"' : ''), // 10
		($tabVertical ? ' vertical-tab-width' : ''), // 11
		($mobileTabDisplay !== 'accordion' ? ' ' . $mobileTabStyle . '-tab-width-mobile' : ''), // 12
		($tabletTabDisplay !== 'accordion' ? ' ' . $tabletTabStyle . '-tab-width-tablet' : ''), // 13
		($tabVertical ? '-vertical-tab' : ''), // 14
		($mobileTabDisplay === 'accordion' ? ' ub-mobile-hide' : ' ' . $blockName . '-tabs-title-mobile-' . $mobileTabStyle . '-tab'), // 15
		($tabletTabDisplay === 'accordion' ? ' ub-tablet-hide' : ' ' . $blockName . '-tabs-title-tablet-' . $tabletTabStyle . '-tab'), // 16
		$tabs, // 17
		($tabVertical ? ' vertical-content-width ' : ''), // 18
		($mobileTabDisplay === 'verticaltab' ? ' vertical-content-width-mobile' : ($mobileTabDisplay === 'accordion' ? ' ub-tabbed-content-mobile-accordion' : '')), // 19
		($tabletTabDisplay === 'verticaltab' ? ' vertical-content-width-tablet' : ($tabletTabDisplay === 'accordion' ? ' ub-tabbed-content-tablet-accordion' : '')), // 20
		implode($tabContents), // 21
		Ultimate_Blocks\includes\generate_css_string($tab_contents_styles) // 22
	);
}

function ub_register_tabbed_content_block(){
    if(function_exists('register_block_type_from_metadata')){
        require dirname(dirname(__DIR__)) . '/defaults.php';
        register_block_type_from_metadata( dirname(dirname(dirname(__DIR__))) . '/dist/blocks/tabbed-content/block.json', array(
            'attributes' => $defaultValues['ub/tabbed-content-block']['attributes'],
            'render_callback' =>  'ub_render_tabbed_content_block'));
    }
}

function ub_tabbed_content_add_frontend_assets() {
    require_once dirname(dirname(__DIR__)) . '/common.php';

    $presentBlocks = ub_getPresentBlocks();

    foreach( $presentBlocks as $block ){
        if($block['blockName'] === 'ub/tabbed-content' || $block['blockName'] === 'ub/tabbed-content-block'){
            wp_enqueue_script(
                'ultimate_blocks-tabbed-content-front-script',
                plugins_url( 'tabbed-content/front.build.js', dirname( __FILE__ ) ),
                array(),
                Ultimate_Blocks_Constants::plugin_version(),
                true
            );
            break;
        }
    }
}

add_action( 'wp_enqueue_scripts', 'ub_tabbed_content_add_frontend_assets' );
add_action('init', 'ub_register_tabbed_content_block');
add_action('init', 'ub_register_tab_block');
