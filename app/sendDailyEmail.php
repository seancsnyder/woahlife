<?php
    /**
     * send the daily journal entry email to all active users
     * @todo setup/handle an unsubscribe.  
     *
     * @author Sean Snyder <sean@snyderitis.com>
     */    
    require_once(__DIR__ . "/../bootstrap.php");

    use Woahlife\User;
    use Woahlife\Logging;    
    use Woahlife\MailgunClient;

    $woahlifeUser = new User();
    $users = $woahlifeUser->getAllActiveUsers();

    // if we have active users email them.
    if (count($users) > 0) {
        Logging::getLogger()->addDebug(count($users) . " users to process");

        $mailgunner = new MailgunClient();

        foreach($users as $user) {
            Logging::getLogger()->addDebug("processing {$user->email}");          
            
            /**
             * if the number of users grows to the thousands, you're better off using the
             * MessageBuilder to handle the email sending in a batch
             */
            $mailgunned = $mailgunner->sendDailyEmail($user);

            if ($mailgunned->http_response_code === 200) {
                Logging::getLogger()->addDebug("successfully emailed {$user->email}");
            } else {
                Logging::getLogger()->addError("unable to send email to {$user->email}");
                Logging::getLogger()->addError("{$mailgunned->http_response_body->message}");
            }
        }
    } else { 
        Logging::getLogger()->addDebug("no users");
    }
?>