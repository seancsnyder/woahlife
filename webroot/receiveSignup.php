<?php
    /**
     * handle the callback from mailgun when someone tries to signup
     */
    require_once("../bootstrap.php");
    
    use Woahlife\Db;
    use Woahlife\Logging;    
    use Woahlife\MailgunClient;

    if (empty($_POST['sender']) || empty($_POST['Message-Id'])) {
        Logging::getLogger()->addDebug("Invalid post data for signup. missing sender and Message-Id");
        http_response_code(500);
        exit();
    }

    Logging::getLogger()->addDebug("processing signup {$_POST['Message-Id']}");

    $db = new Db();
    $connection = $db->getConnection();

    Logging::getLogger()->addDebug("attempting to find user id for {$_POST['sender']}");
    $existingUserId = $connection->fetchColumn("SELECT id FROM users WHERE email = ?", array($_POST['sender']));
    Logging::getLogger()->addDebug("found user id for {$_POST['sender']}: {$existingUserId}");

    if (empty($existingUserId)) {
        Logging::getLogger()->addDebug("determining name by manipulating the FROM header {$_POST['From']}");
        $name = preg_replace("/\s*<?" . $_POST['sender'] . ">?/", '', $_POST['From']);
        Logging::getLogger()->addDebug("determined name to be {$name}");

        $dbRecord = [
            "name" => $name,
            "email" => $_POST['sender'],
            "active" => 1,
            "message_id" => $_POST['Message-Id'],
            "message_url" => $_POST['message-url'],
            "create_date" => date("Y-m-d H:i:s")
        ];

        Logging::getLogger()->addDebug("saving signup for {$_POST['sender']}");

        $saved = $connection->insert('users', $dbRecord);

        if ($saved === 1) {
            http_response_code(200);

            //TODO add the insert to the user class and send a thank you email.
            //TODO don't rely on the mailgun posted fields
        } else {
            Logging::getLogger()->addError("Unable to save signup");
            http_response_code(500);
        }
    }
?>