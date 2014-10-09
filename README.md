I was a big fan of ohlife.com, but it'll be shutdown soon...

For those that don't know, ohlife.com was a really great way to maintain an journal.  Everyday, at a user configured time, ohlife would
send you an email asking about your day. All you had to do was respond to that email, and ohlife would save the response as a journal entry.
Ohlife provided a site, with a way to search and download your entries.  They also had a premium version that let you customize the 
email message that was sent to you and a way to auto backup your entries to dropbox.

I chose to dumb down the functionality a bit since I only plan on using this for myself.  I don't need a web interface or an easy export button,
since I have full access.

Feel free to clone it and use your own mailgun account.  Here is how to set it up for yourself....sorry it's so many steps, 
but we have a lot of moving pieces...

<h2>Setting It Up</h2>
<ol>
    <li>Buy a domain or use an existing domain that doesn't currently have mx records setup.</li>
    <li>Signup for a Mailgun account.  The first 10k emails per month are free.</li>
    <li>Add the domain to Mailgun.  They will require you to add some dns records to verify the domain.</li>
    <li>Setup the email routes in Mailgun.  These routes will let Mailgun handle the emails that people will send to your domain.<br/>
        At a minimum, you need two routes setup.  example:<br/>

        ```
        Filter Expression = match_recipient("signup@yourdomain.com")  
        Action = store(notify="http://www.yourdomain.com/receiveSignup.php")
        ```
        and
        ```
        Filter Expression = match_recipient("post@yourdomain.com")    
        Action = store(notify="http://www.yourdomain.com/receivePost.php")
        ```

        When someone emails post@yourdomain.com, mailgun will accept the email, store it, and will HTTP POST the entire email 
        message to that url.</li>

    <li>You'll need a lamp/lemp stack.  If you're completely lost, this is a pretty good walkthrough, <br/>
        https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-12-04</li>
    <li>Once you have it up and running, with at least one virtual host configured, set the A record for your domain to your server</li>
    <li>Git clone this repo into the site directory you setup.<br/>
        For example, if you configured your document root to be /var/www/mysite.com/<br/>
        Go ahead and clone this repo into /var/ww/mysite.com  This will expose the entire codebase, which is bad, so update
        your nginx configuration to set the document root to /var/ww/mysite.com/webroot/ and then restart nginx.</li>
    <li>From the /var/www/mysite.com/ directory, run 'composer install' to get all of the dependcies up to date.  <br/>
        If you don't have composer, go here, https://getcomposer.org/</li>
    <li>Create a mysql database. Run some sql.  <br/>
        The sql to create the required tables are in the config/bootstrap.sql file.</li>
    <li>Update the config/mailgun-sample.ini file with your actual mailgun information and the domain you plan on using.<br/>
        Then, rename the file to "mailgun.ini" in the same directory</li>
    <li>Update the config/mysql-sample.ini" file with your actual mysql database information and the domain you plan on using.<br/>
        Then, rename the file to "mysql.ini" in the same directory</li>
    <li>Give the 'logs' directory the correct group ownership and/or file permissions to be writable by your webserver.<br/>
        I used nginx, so i set the group ownership to www-data and gave the group write permissions to that directory.</li>
    <li>Configure a cron to run every day, or as often as you'd like, to run the app/sendDailyEmail.php script.  <Br/>
        That script will fire off the email to all active registered users in your database.  Upon replying to that email
        Mailgun will post the email to the route you setup in step 4.  That step will save the message to your mysql database.</li>
    <li>Send an email to signup@yourdomain.com<br/>
        Within a few seconds, you should see it get posted to your site and create a new user.</li>
</ol>

<h2>Troubleshooting</h2>
<ul>
    <li>If you aren't getting daily emails, make sure the cron is running.</li>
    <li>If your emails aren't getting added to the database, check the app logs, as well as the Mailgun logs. <br/>
        The Mailgun logs are hosted at mailgun.com</li>
    <li>If you don't want to use mysql, choose whatever datastore you like.  Just create a new config file<br/>
        and update the Db.class.php to pull in the correct config file. </li>
    <li>composer is freaking out...please contact me and I can help you out.</li>
