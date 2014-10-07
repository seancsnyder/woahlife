<?php
    /**
     * send the daily journal entry email to all active users
     */
    require_once "vendor/autoload.php";
    
    use Woahlife\Db;
    use Woahlife\Logging;    
    use Woahlife\MailgunClient;

    $db = new Db();
    $connection = $db->getConnection();

    $users = $connection->fetchAll(
        "SELECT name, email FROM users WHERE active = ?", 
        [1]
    );

    // if we have active users email them.
    if (count($users) > 0) {
        Logging::getLogger()->addDebug(count($users) . " users to process");

        $mailgunner = new MailgunClient();

        foreach($users as $user) {
            Logging::getLogger()->addDebug("processing {$user['email']}");          
            
            $mailgunned = $mailgunner->sendMessage($user);

            if ($mailgunned->http_response_code === 200) {
                Logging::getLogger()->addDebug("successfully emailed {$user['email']}");
            } else {
                Logging::getLogger()->addError("unable to send email to {$user['email']}");
                Logging::getLogger()->addError("{$mailgunned->http_response_body->message}");
            }
        }
    } else { 
        Logging::getLogger()->addDebug("no users");
    }
?>