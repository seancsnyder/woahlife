<?php
    /**
     * This script will export the posts as plaintext.  You must provide the username on the command line. 
     *
     * @author Sean Snyder <sean@snyderitis.com>
     */    
    require_once(__DIR__ . "/../bootstrap.php");

    use Woahlife\Entry;

    $email = $argv[1];

    if (empty($email)) {
        echo "USAGE: php app/exportPosts.php <EMAIL ADDRESS>\n";
        exit();
    }

    $woahlifeEntry = new Entry();
    $allEntries = $woahlifeEntry->getAllEntriesForUser($email);

    if (count($allEntries) > 0) {
        foreach($allEntries as $entry) {
            echo "{$entry['entry_date']}\n";
            echo "{$entry['entry_text']}\n";
            echo "\n";
        }
    } else { 
        echo "sorry, no entries exist for {$email}\n";
    }
?>