<?php
    /**
     * since we didn't use a framework, like symfony or cake, we need to bootstrap some
     * stuff 
     */ 

    $baseDirectory = __DIR__ . "/";

    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $baseDirectory = $_SERVER['DOCUMENT_ROOT'] . "/../";
    }

    define("APP_DIRECTORY", $baseDirectory);

    // change this to DEV, if you want the logs printed to stdout
    define("APP_MODE", 'PRODUCTION');

    require_once "vendor/autoload.php";

?>