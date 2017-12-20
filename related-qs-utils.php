<?php

require_once QA_INCLUDE_DIR.'db/selects.php';

class related_qs_utils {
    
    const CACHE_EXPIRES = 60 * 10;      // キャッシュの保存期間
    const MIN_ACOUNT_IMG = 2;           // 最小の回答数(画像あり)
    const MIN_ACOUNT = 3;               // 最小の回答数
    const LIST_COUNT_IMG = 5;           // 表示件数(画像あり)
    const LIST_COUNT = 15;              // 表示件数

    /*
     * 関連する質問
     */
    public static function get_related_questions($userid, $questionid)
    {
        global $qa_cache;
        $key = 'q-related-'.$questionid;
        if($qa_cache->has($key)) {
            $questions = $qa_cache->get($key);
        } else {
            $orgselspec = qa_db_related_qs_selectspec($userid, $questionid);
            $minscore = qa_match_to_min_score(qa_opt('match_related_qs'));
            $orgselspec['columns']['content'] = '^posts.content ';
            $orgselspec['columns']['format'] = '^posts.format ';
            $imgselspec = $orgselspec;
            $where = " WHERE  (^posts.content like '%[image=%'";
            $where.= " OR ^posts.content like '%<img%'";
            $where.= " OR ^posts.content like '%[uploaded-video=%'";
            $where.= " OR ^posts.content like '%plain_url%')";
            $where.= ' AND ^posts.acount >= # AND y.score >= # LIMIT #';
            $imgselspec['source'] .= $where;
            $imgselspec['arguments'][] = self::MIN_ACOUNT_IMG;
            $imgselspec['arguments'][] = $minscore;
            $imgselspec['arguments'][] = self::LIST_COUNT_IMG;

            $otherselspec = $orgselspec;
            $otherselspec['source'] .= ' WHERE ^posts.acount >= # AND y.score >= # LIMIT #';
            $otherselspec['arguments'][] = self::MIN_ACOUNT;
            $otherselspec['arguments'][] = $minscore;
            $otherselspec['arguments'][] = self::LIST_COUNT;

            list($imgquestions, $otherquestions) = qa_db_select_with_pending(
                $imgselspec, $otherselspec
            );
            $questions = array_slice(array_replace($imgquestions, $otherquestions), 0, self::LIST_COUNT);
            $qa_cache->set($key, $questions, self::CACHE_EXPIRES);
        }
        return $questions;
        // return array_slice($questions, 0, 5);
    }

    /*
     * 季節の質問
     */
    public static function get_seasonal_questions($userid = null)
    {
        $month = date("m");
        $day= date("j");
        $day = floor($day/10);
        if($day == 3) {
            $day  = 2;
        }
        $date = '%-' . $month . '-' . $day . '%';

        // $userid = '1';
        $selectspec=qa_db_posts_basic_selectspec($userid);
        $selectspec['columns']['content'] = '^posts.content ';
        $selectspec['columns']['format'] = '^posts.format ';
        $selectspec['source'] .=" WHERE type='Q'";
        $selectspec['source'] .= " AND ^posts.created like $ ORDER BY RAND() LIMIT #";
        $selectspec['arguments'][] = $date;
        $selectspec['arguments'][] = self::LIST_COUNT;
        $questions=qa_db_single_select($selectspec);
        return $questions;
    }

    /*
     * 最近の質問
     */
    public static function get_recent_questions($userid = null)
    {
        $selectsort='created';
        $start=qa_get_start();

        $selectspec = qa_db_qs_selectspec($userid, $selectsort, $start, null, null, false, false, 5);
        $selectspec['columns']['content'] = '^posts.content ';
        $selectspec['columns']['format'] = '^posts.format ';

        $questions = qa_db_single_select($selectspec);
        return $questions;
    }

    /*
     * q_list を返す
     */
    public static function get_q_list($questions, $userid, $sendEvent = false) {

        $q_list = array(
            'form' => array(
                'tags' => 'method="post" action="' . qa_self_html() . '"',
                'hidden' => array(
                    'code' => qa_get_form_security_code('vote'),
                ),
            ),
            'qs' => array(),
        );

        $cookieid = qa_cookie_get();
        $defaults = qa_post_html_defaults('Q');
        $defaults['contentview'] = true;
        $usershtml = qa_userids_handles_html($questions);
        $idx = 1;
        foreach ($questions as $question) {
            if ($sendEvent) {
                $onclick = '" onclick="optSendEvent('.$idx.');';
            } else {
                $onclick = '';
            }
            $fields = qa_post_html_fields($question, $userid, $cookieid, $usershtml, null, qa_post_html_options($question, $defaults));
            $fields['url'] .= $onclick;
            if (function_exists('qme_remove_anchor')) {
                $fields['content'] = qme_remove_anchor($fields['content']);
            }
            $q_list['qs'][] = $fields;
            $idx++;
        }
        return $q_list;
    }

    /*
     * 関連する質問のHTMLを返す
     */
    public static function get_related_qs_html($userid, $questionid, $themeobject)
    {
        $questions = self::get_related_questions($userid, $questionid);
        if (count($questions) > 0) {
            $titlehtml = qa_lang('main/related_qs_title');
            $html = '<h2 style="margin-top:0; padding-top:0;">'.$titlehtml.'</h2>';
        } else {
            $titlehtml = qa_lang('main/no_related_qs_title');
            return '<h2 style="margin-top:0; padding-top:0;">'.$titlehtml.'</h2>';
        }

        $q_list = self::get_q_list($questions, $userid);
        
        ob_start();
        $themeobject->q_list_and_form($q_list);
        $html .= ob_get_clean();

        return $html;
    }

    /*
     * 季節の質問のHTMLを返す
     */
    public static function get_seasonal_qs_html($userid, $themeobject)
    {
        $questions = self::get_seasonal_questions($userid);
        $titlehtml = qa_lang_html('custom_related_qs/title_seasons');
        $html = '<h2 style="margin-top:0; padding-top:0;">'.$titlehtml.'</h2>';

        $q_list = self::get_q_list($questions, $userid);

        ob_start();
        $themeobject->q_list_and_form($q_list);
        $html .= ob_get_clean();

        return $html;
    }
}