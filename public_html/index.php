<?php

    /*
     * Hi there, do you want to reconfigure me?
     *  - options in the /system/config.php
     */
    try {
        if (file_exists(__DIR__ . '/../system/config.php')) {
            $config = require(__DIR__ . '/../system/config.php');

            if (DEBUG_MODE === true) {
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
                error_reporting(E_ALL);
            }
        } else {
            throw new Exception('The config file is empty');
        }

        if (file_exists(__DIR__ . '/../system/vendor/bestpdo.php')) {
            require(__DIR__ . '/../system/vendor/bestpdo.php');
        } else {
            throw new Exception('The DB class is empty (you may clone the 
            https://github.com/colshrapnel/thebestpdowrapper/blob/master/bestpdo.php)');
        }

        // module tree start
        spl_autoload_register( function( $class_name ) {
            $file_name = str_replace('\\', '/', $class_name. '.php');

            if (file_exists( APP.$file_name )) {
                require APP . $file_name;
            }
        });
        $class_tree = new system\modules\tree\controllers\Controller;
        print_r($class_tree->run());
        // module tree end

    } catch (Exception $e) {
        echo "Failed! Please contact our system administrator - admin@domain";

        if (DEBUG_MODE === true) {
            if (!empty($e->getMessage())) {
                print_r($e->getMessage());
            }

            if (!empty(\DB::errorInfo())) {
                print_r(\DB::errorInfo());
            }
        }
    }