<?php
/*
Plugin Name: Sticky page
Plugin URI: http://spais.jp/
Description: Sticky page
Author: HAYASHI Ryo
Version: 0.0.1
Author URI: http://spais.jp/
*/
class sticky_page{

    private $_targetScreen = 'page';

    private $_targetMetaBox = 'submitdiv';

    /**
     * Hook add_meta_boxes_page
     */
    function action_add_meta_boxes_page(){
        global $wp_meta_boxes;
        $screen = convert_to_screen($this->_targetScreen);
        if(!isset($wp_meta_boxes[$screen->id])) return;
        $metaboxes =& $wp_meta_boxes[$screen->id];
        foreach($metaboxes as $context => &$_metaboxes){
            foreach($_metaboxes as $priority => &$__metaboxes){
                foreach($__metaboxes as $id => &$metabox){
                    if($id !== $this->_targetMetaBox) continue;
                    $metabox['callback'] = array(&$this, 'replace_' . $this->_targetMetaBox);
                    return;
                }
            }
        }
    }

    /**
     * Replace submitdiv(metabox) HTML
     * @param object $post
     */
    function replace_submitdiv($post){
        ob_start();
        post_submit_meta_box($post);
        $buff = ob_get_clean();
        if(preg_match('!(^.*)(<label for="visibility-radio-public")(.*)(<input type="radio" name="visibility" id="visibility-radio-password")(.*$)!is', $buff, $matches)){
            $buff = sprintf('%s%s%s<span id="sticky-span"><input id="sticky" name="sticky" type="checkbox" value="sticky" %s tabindex="4" /> <label for="sticky" class="selectit">%s</label><br /></span>%s%s',
                $matches[1], $matches[2], $matches[3], checked(is_sticky($post->ID), true, false), __('Stick this post to the front page'), $matches[4], $matches[5]);
        }
        if(is_sticky($post->ID)){
            $buff = preg_replace('!(<span id="post-visibility-display">)[^<]*(</span>)!is', sprintf('$1%s$2', __('Public, Sticky')), $buff);
        }
        echo $buff;
    }

    function __construct(){
        add_action('add_meta_boxes_' . $this->_targetScreen, array(&$this, 'action_add_meta_boxes_' . $this->_targetScreen));
    }
}
new sticky_page;