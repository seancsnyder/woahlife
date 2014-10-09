<?php
    /**
     * handle the callback from mailgun when someone posts a daily journal entry
     */
    require_once("../bootstrap.php");
    
    use Woahlife\Entry;
    use Woahlife\Logging;

    try {
        $entry = new Entry();
        $saved = $entry->saveEntry($_POST);  

        if ($saved === true) {
            http_response_code(200);
        } else {
            Logging::getLogger()->addError("Unable to save journal post");
            http_response_code(500);
        }
    } catch (Exception $e) {
        Logging::getLogger()->addError("Unable to save journal post. " . $e->getMessage());
        http_response_code(500);
    }   
?>