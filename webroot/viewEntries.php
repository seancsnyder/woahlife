<?php
    /**
     * Front end view pages for browsing a user's journal entries.
     * 
     * @author Sean Snyder <sean@snyderitis.com>
     */
    require_once("../bootstrap.php");
    
    use Woahlife\BrowsingSession;
    use Woahlife\Logging;
    use Woahlife\User;
    
    $browseSession = new BrowsingSession();
    
    try {
        $browseSession->validateBrowsingSession($_GET['token']);
        
        $allEntries = $woahlifeEntry->getAllEntriesForUser($browseSession->user->email);
    
        if (count($allEntries) > 0) {
            foreach($allEntries as $entry) {
                echo "<p>";
                echo "{$entry['entry_date']}<br/>\n";
                echo "{$entry['entry_text']}<br\>\n";
                echo "</p>\n";
            }
        } else { 
            echo "no entries";
        }
    } catch (Exception $e) {
       echo $e->getMessage();   
    }
?>