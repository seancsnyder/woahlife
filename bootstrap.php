<?php
    /**
     * since we didn't use a framework, like symfony or cake, we need to bootstrap some
     * stuff 
     */ 

    $baseDirectory = "";

    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $baseDirectory = $_SERVER['DOCUMENT_ROOT'] . "/../";
    }

    define("APP_DIRECTORY", $baseDirectory);

    require_once "vendor/autoload.php";

?>