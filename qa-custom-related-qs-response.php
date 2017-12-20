<?php

require_once CUSTOME_RELATED_DIR.'/related-qs-utils.php';

class qa_crq_page {
    
    function match_request($request) {
        $parts = explode('/', $request);
        
        return $parts[0] == 'relatedqs';
    }
    
    function process_request($request) {
        header ( 'Content-Type: application/json' );
        $ret_val = array ();
        $json_object = array ();

        try {
            $userid = qa_get_logged_in_userid();
            $postid = qa_get('postid');

            $themeclass=qa_load_theme_class(qa_get_site_theme(), 'ajax-rlated-qs', null, null);
            $themeclass->initialize();

            // 関連する質問
            $related_qs_html = related_qs_utils::get_related_qs_html($userid, $postid, $themeclass);
            // 季節の質問
            // $seasonal_qs_html = related_qs_utils::get_seasonal_qs_html($userid, $themeclass);

            $themeclass = null;
                
            http_response_code ( 200 );
            
            $json_object['statuscode'] = '200';
            $json_object['message'] = 'ok';

            $json_object['related_q_list'] = $related_qs_html;
            // $json_object['season_q_list'] = $seasonal_qs_html;
            

        } catch (Exception $e) {
            http_response_code ( 500 );
            
            $json_object['statuscode'] = '500';
            $json_object['message'] = 'Internal Server Error';

            $json_object['detail'] = $e->getMessage();
        }
        
        echo json_encode ( $json_object, JSON_PRETTY_PRINT );

    }
    
}
