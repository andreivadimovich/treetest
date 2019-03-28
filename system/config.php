<?php

    // DB connect option
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'st2');
    define('DB_USER', 'root');
    define('DB_PASS', 'root');
    define('DB_CHAR', 'utf8');

    // display errors?
    define('DEBUG_MODE', true);

    // modules section
    // Tree module
    define('APP_TREE',  str_replace('system', '', __DIR__));