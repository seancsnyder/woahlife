<?php
    /**
     * handle the callback from mailgun when someone tries to signup,.
     *
     * @author Sean Snyder <sean@snyderitis.com>
     */
    require_once("../bootstrap.php");
    
    use Woahlife\User;
    use Woahlife\Logging;
    
    try {
        $user = new User();
        $saved = $user->signupUser($_POST);  

        // always respond with 200, unless an exception was thrown
        http_response_code(200);
    } catch (Exception $e) {
        Logging::getLogger()->addError("Unable to save journal post. " . $e->getMessage());
        http_response_code(500);
    }   

?>