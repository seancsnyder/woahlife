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

        <blockquote>
        Filter Expression = match_recipient("signup@yourdomain.com") <br/>
        Action = store(notify="http://www.yourdomain.com/receiveSignup.php")
        </blockquote>

        and<br/>
        <blockquote>
        Filter Expression = match_recipient("post@yourdomain.com")    
        Action = store(notify="http://www.yourdomain.com/receivePost.php")
        </blockquote>

        When someone emails post@yourdomain.com, mailgun will accept the email, store it, and will HTTP POST the entire email 
        message to that url.</li>

    <li>You'll need a lamp/lemp stack.  If you're completely lost, this is a pretty good walkthrough, <br/>
        <a href="https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-12-04">Lamp Tutorial</a></li>
    <li>Once you have it up and running, with at least one virtual host configured, set the A record for your domain to your server</li>
    <li>Git clone this repo into the site directory you setup.<br/>
        For example, if you configured your document root to be /var/www/mysite.com/<br/>
        Go ahead and clone this repo into <b>/var/ww/mysite.com</b> <br/>
        We will refer to this directory as your APP_PATH.  Unfortunately, this will expose the entire codebase, which is bad, so update
        your nginx configuration to set the document root to <b>/var/ww/mysite.com/webroot/</b> and then restart nginx.</li>
    <li>From the APP_PATH directory (ex: /var/www/mysite.com/), run <b>composer install</b> to get all of the dependcies up to date.  <br/>
        If you don't have composer, go here, https://getcomposer.org/</li>
    <li>Create a mysql database. Run some sql.  <br/>
        The sql to create the required tables are in the <b>config/bootstrap.sql</b> file.
        <blockquote>mysql -h YOUR_HOST -u YOUR_USERNAME -p -D YOUR_DATABASE_NAME < config/bootstrap.sql</blockquote>
        </li>
    <li>Update the <b>config/mailgun-sample.ini</b> file with your actual mailgun information and the domain you plan on using.<br/>
        Then, rename the file to <b>mailgun.ini</b> in the same directory.
        <blockquote>mv config/mailgun-sample.ini config/mailgun.ini</blockquote>
        </li>
    <li>Update the <b>config/mysql-sample.ini</b> file with your actual mysql database information and the domain you plan on using.<br/>
        Then, rename the file to mysql.ini</b> in the same directory
        <blockquote>mv config/mysql-sample.ini config/mysql.ini</blockquote>
        </li>
    <li>Give the <b>logs</b> directory the correct group ownership and/or file permissions to be writable by your webserver.<br/>
        I used nginx, so i set the group ownership to www-data and gave the group write permissions to that directory.
        <blockquote>
            chown :www-data logs;<br/>
            chmod g+w logs;
        </blockquote>
        </li>
    <li>Configure a cron to run every day, or as often as you'd like, to run the <b>app/sendDailyEmail.php</b> script.  <br/>
        That script will fire off the email to all active registered users in your database.  Upon replying to that email
        Mailgun will post the email to the route you setup in step 4.  That script will save the message to your database.<br/>
        For example: I wanted the email to be sent to me every day at 5pm so i added this to my crontab:
        <blockquote>0 17 * * * /usr/bin/php /var/www/yourdomain.com/app/sendDailyEmail.php</blockquote>
        </li>
    <li>Send an email to <b>signup@yourdomain.com</b><br/>
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

<h2>Cost</h2>
    Assuming 1 signup email per person and 30 daily post emails, Mailgun's free tier of 10,000 emails per month would allow you to
    comfortably run this service for ~300 people. <br/>
    However, this does not take into account the server requirements and data storage.<br/>
    For a single person, as I intend to do, it's well within the limits of a very cheap cloud server or two.

