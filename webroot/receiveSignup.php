<?php
    /**
     * Handle the callback from mailgun when someone tries to signup.
     * If Mailgun receives an HTTP 200, it considers that a success
     * If Mailgun receives an HTTP 406, it considers it a failure, but won't retry
     * If Mailgun receives any other response code, including a 500, it will attempt to notify
     *
     * @author Sean Snyder <sean@snyderitis.com>
     */
    require_once("../bootstrap.php");
    
    use Woahlife\User;
    use Woahlife\Logging;
    
    try {
        $user = new User();
        $saved = $user->signupUser($_POST);  

        /**
         * Always respond with 200, unless an exception was thrown.
         * If the user was already signed up, just silently ignore the request.
         */
        http_response_code(200);
    } catch (Exception $e) {
        Logging::getLogger()->addError("Unable to save signup. " . $e->getMessage());
        http_response_code(500);
    }   
?>