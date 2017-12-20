<?php

/*
    Plugin Name: Custom Related Questions
    Plugin URI:
    Plugin Description: Customize related questions widget
    Plugin Version: 1.0
    Plugin Date: 2016-10-18
    Plugin Author: 38qa.net
    Plugin Author URI: http://38qa.net/
    Plugin License: GPLv2
    Plugin Minimum Question2Answer Version: 1.7
    Plugin Update Check URI:
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
    header('Location: ../../');
    exit;
}

qa_register_plugin_module('widget','qa-custom-related-qs-widget.php','qa_custom_related_qs','Custom Related Questions');
qa_register_plugin_layer('qa-custom-related-qs-layer.php', 'Custom Related Questions Layer');
// language file
qa_register_plugin_phrases('qa-custom-related-qs-lang-*.php', 'custom_related_qs');
// page
qa_register_plugin_module('page', 'qa-custom-related-qs-response.php', 'qa_crq_page', 'Related Questions Ajax Page');

@define( 'CUSTOME_RELATED_DIR', dirname( __FILE__ ) );

function infinite_scroll_available()
{
    global $qa_layers;
    if (array_key_exists('Infinite Scroll Layer', $qa_layers)) {
        return true;
    } else {
        return false;
    }
}
