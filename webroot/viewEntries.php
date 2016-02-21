<?php
    /**
     * Front end view pages for browsing a user's journal entries.
     * 
     * @author Sean Snyder <sean@snyderitis.com>
     */
    require_once("../bootstrap.php");
    
    use Woahlife\BrowsingSession;
    use Woahlife\Logging;
    use Woahlife\Entry;
    
    $browseSession = new BrowsingSession();
    
    try {
        $activeBrowsingSession = $browseSession->validateBrowsingSession($_GET['token']);
        
        $entry = new Entry();
        $allEntries = $entry->getAllEntriesForUser($activeBrowsingSession->user->email);
    
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