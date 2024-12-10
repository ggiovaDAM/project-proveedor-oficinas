<?php

    define("ROOT_DIR", __DIR__);

        define("SRC_DIR", ROOT_DIR . "/src");

            define("BACKEND_DIR", SRC_DIR . "/backend");

                define("PDO_DIR", BACKEND_DIR . "/pdo");





    define("ROOT_URL", "http://localhost");

        define("SOURCE_DIR_URL", ROOT_URL . "/src");

            define("PAGES_DIR_URL", SOURCE_DIR_URL . "/pages");

                define("ERROR_DIR_URL", PAGES_DIR_URL . "/error");

                    define("ERROR_URL", ERROR_DIR_URL . "/error.php");