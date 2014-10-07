<?php
    /**
     * handle the callback from mailgun when someone posts a daily journal entry
     */
    require_once "../vendor/autoload.php";
    
    use Woahlife\Db;
    use Woahlife\Logging;    
    use Woahlife\MailgunClient;

    if (empty($_POST['sender']) || empty($_POST['Message-Id'])) {
        Logging::getLogger()->addError("Invalid post data for journal entry. missing sender and Message-Id");
        http_response_code(500);
        exit();
    }

    Logging::getLogger()->addDebug("processing journal post {$_POST['Message-Id']}");

    $db = new Db();
    $connection = $db->getConnection();

    Logging::getLogger()->addDebug("finding user id for {$_POST['sender']}");
    $userId = $connection->fetchColumn("SELECT id FROM users WHERE email = ?", array($_POST['sender']));
    Logging::getLogger()->addDebug("found user id for {$_POST['sender']}: {$user_id}");

    if (empty($user_id)) {
        Logging::getLogger()->addError("unable to find user id for {$_POST['sender']}");
        http_response_code(500);
        exit();
    }

    $dbRecord = [
        "user_id" => $userId,
        "entry_text" => $_POST['stripped-text'],
        "entry_date" => date("Y-m-d", strtotime($_POST['Date'])),
        "message_id" => $_POST['Message-Id'],
        "message_url" => $_POST['message-url'],
        "create_date" => date("Y-m-d H:i:s")
    ];

    Logging::getLogger()->addDebug("saving journal post for {$_POST['sender']} ({$dbRecord['user_id']}) entry date {$dbRecord['entry_date']}");

    $saved = $connection->insert('entries', $dbRecord);

    if ($saved === 1) {
        //TODO put all this in the Entry class
        //TODO don't rely on the mailgun posted fields...
        http_response_code(200);
    } else {
        Logging::getLogger()->addError("Unable to save journal post");
        http_response_code(500);
    }
?>