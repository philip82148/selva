<?php


/* add comment..
* ---------------------------------------- */
function is_bzb_firstpost(){
    global $wp_query;
    return ($wp_query->current_post === 0);
}