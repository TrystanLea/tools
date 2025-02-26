<?php

function view($filepath, array $args = array())
{
    global $path;
    $args['path'] = $path;
    $content = '';
    if (file_exists($filepath)) {
        extract($args);
        ob_start();
        include "$filepath";
        $content = ob_get_clean();
    }
    return $content;
}

function get_views($redis,$menu)
{
    $keys = $redis->keys('tools:*');
    $views = array();
    foreach ($keys as $key) {
        $key = str_replace('tools:', '', $key);
        $title = $key;
        if (isset($menu[$key])) {
            $title = $menu[$key]['title'];
        }
        $views[$title] = (int) $redis->get("tools:$key");
    }
    arsort($views);
    return $views;
}