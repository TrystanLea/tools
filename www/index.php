<?php

// The most basic front controller

include 'core.php';
include 'menu.php';

$q = $_GET['q'] ?? 'home';

// change to lower case
$q = strtolower($q);
// remove .php, .html
$q = str_replace('.php', '', $q);
$q = str_replace('.html', '', $q);

// Check if page exists in menu
if (isset($menu[$q])) {
    $title = $menu[$q]['title'];
    $description = $menu[$q]['description'];
    $path = "tools/$q/";
    $path_lib = "lib/";
    $content = view("tools/$q/$q.php", array(
        'path' => $path,
        'path_lib' => $path_lib
    ));
} else if ($q == 'home') {
    $title = 'Tools';
    $description = 'Open source tools to help with heat pump design and understanding.';
    $content = view('home.php', array(
        'menu' => $menu
    ));
} else {
    $title = '404 - Page not found';
    $description = 'The page you are looking for does not exist.';
    $content = view('404.php');
}

// Load the theme
echo view("theme.php", array(
    'menu' => $menu,
    'title' => $title,
    'description' => $description,
    'content' => $content,
    'github' => 'https://github.com/trystanLea/tools'
));
