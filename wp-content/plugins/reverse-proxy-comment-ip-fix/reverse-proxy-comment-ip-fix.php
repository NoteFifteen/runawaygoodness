<?php
/*
Plugin Name: Reverse-Proxy Comment IP Fix
Plugin URI: http://www.codetrax.org/projects/reverse-proxy-comment-ip-fix
Description: Sets the comment IP to the client IP provided by the X-Forwarded-For header before the comment is saved to the database.
Version: 0.1.1
Author: George Notaras
Author URI: http://www.g-loaded.eu
License: Apache License V2
*/

/*
Copyright 2012 George Notaras <gnot@g-loaded.eu>, CodeTRAX.org

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

function rpcif__set_client_ip()
{
    /**
     * Security Note
     * -------------
     * We ultimately trust the IPs provided in the X-Forwarded-For header.
     * The reliability of the X-Forwarded-For header should be checked
     * at the reverse proxy level.
     */

    // Use IP set in the REMOTE_ADDR server variable by default
    $CLIENT_IP = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['X_FORWARDED_FOR'])) {
        $X_FORWARDED_FOR = explode(',', $_SERVER['X_FORWARDED_FOR']);
    }

    // Extra check taken from The WordPress Codex at:
    // http://codex.wordpress.org/Plugin_API/Filter_Reference/pre_comment_user_ip
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $X_FORWARDED_FOR = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    // If we got a
    if (!empty($X_FORWARDED_FOR)) {
        $CLIENT_IP = trim($X_FORWARDED_FOR[0]);
        //$CLIENT_IP = preg_replace('/[^0-9a-f:\., ]/si', '', $CLIENT_IP);
    }

    return $CLIENT_IP;
}

add_filter ('pre_comment_user_ip', 'rpcif__set_client_ip');
?>