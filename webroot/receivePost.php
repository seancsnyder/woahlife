<?php
    /**
     * handle the callback from mailgun when someone posts a daily journal entry.
     * If Mailgun receives an HTTP 200, it considers that a success
     * If Mailgun receives an HTTP 406, it considers it a failure, but won't retry
     * If Mailgun receives any other response code, including a 500, it will attempt to notify
     *
     * @author Sean Snyder <sean@snyderitis.com>
     */
    require_once("../bootstrap.php");
    
    use Woahlife\Entry;
    use Woahlife\Logging;

    try {
        $entry = new Entry();
        $saved = $entry->saveEntry($_POST);  

        if ($saved === true) {
            Logging::getLogger()->addError("Saved journal post");
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