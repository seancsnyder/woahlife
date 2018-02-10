## Woahlife
I was a big fan of ohlife.com, but it'll be shutdown soon (10/11/14)...

For those that don't know, ohlife.com was a really great way to maintain a daily journal.  Everyday, at a user configured time, ohlife would send you an email asking about your day. All you had to do was respond to that email, and ohlife would save the response as a journal entry. Ohlife provided a site, with a way to search past entries and a way to export your entries.  They also had a premium version that let you customize the email message that was sent to you and a way to auto backup your entries to dropbox.

Since I only plan on using this for myself, I chose to dumb down the functionality a bit.  I don't need a web interface or an easy export button, since I have full access to the database.

Feel free to clone it and use your own mailgun account.  Here is how to set it up for yourself....sorry it's so many steps,  but we have a lot of moving pieces...

## Setting It Up
1. Buy a domain or use an existing domain that doesn't currently have mx records setup.
2. Signup for a Mailgun account.  The first 10k emails per month are free.
3. Add the domain to Mailgun.  They will require you to add some dns records to verify the domain.
4. Setup the email routes in Mailgun.  These routes will let Mailgun handle the emails that people will send to your domain. At a minimum, you need two routes setup.  example:<br/>
```
Filter Expression = match_recipient("signup@yourdomain.com") 
Action = store(notify="http://www.yourdomain.com/receiveSignup.php")
```
and
```
Filter Expression = match_recipient("post@yourdomain.com")   
Action = store(notify="http://www.yourdomain.com/receivePost.php")
```
5. Additionally, I wanted to add this route to send out links that would let you read through all of your entries       
```
Filter Expression = match_recipient("browse@yourdomain.com") <br/>    
Action = store(notify="http://www.yourdomain.com/receiveBrowse.php")
```
6. You'll need a lamp/lemp stack.  If you're completely lost, this is a pretty good walkthrough, [Lamp Tutorial](https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-12-04)
7. Once you have PHP, Database and a Webserver up and running, with your site configured, set the A record for your domain to your server
8. Git clone this repo into the site directory you setup.
    For example, if you configured your document root to be /var/www/mysite.com/<br/>
    Go ahead and clone this repo into <b>/var/ww/mysite.com</b>
    We will refer to this directory as your APP_PATH.  Unfortunately, this will expose the entire codebase, which is bad, so update your nginx configuration to set the document root to <b>/var/ww/mysite.com/webroot/</b> and then restart nginx.
9. From the APP_PATH directory (ex: /var/www/mysite.com/), run <b>composer install</b> to get all of the dependcies up to date. If you don't have composer, go here, https://getcomposer.org/</li>
10. Create a mysql database. Run some sql. The sql to create the required tables are in the <b>config/bootstrap.sql</b> file.
```
mysql -h YOUR_HOST -u YOUR_USERNAME -p -D YOUR_DATABASE_NAME < config/bootstrap.sql
```
11. Update the <b>config/mailgun-sample.ini</b> file with your actual mailgun information and the domain you plan on using.  Then, rename the file to <b>mailgun.ini</b> in the same directory.
```
mv config/mailgun-sample.ini config/mailgun.ini
```
12. Update the <b>config/mysql-sample.ini</b> file with your actual mysql database information and the domain you plan on using. Then, rename the file to mysql.ini</b> in the same directory
```
mv config/mysql-sample.ini config/mysql.ini
```
13. Give the <b>logs</b> directory the correct group ownership and/or file permissions to be writable by your webserver. I used nginx, so I set the group ownership to www-data and gave the group write permissions to that directory.
```
chown :www-data logs;
chmod g+w logs;
```
14. Configure a cron to run every day, or as often as you'd like, to run the <b>app/sendDailyEmail.php</b> script. That script will fire off the email to all active registered users in your database.  Upon replying to that email, Mailgun will post the email to the route you setup in step 4.  That script will save the message to your database. For example: I wanted the email to be sent to me every day at 5pm so i added this to my crontab:
```
0 17 * * * /usr/bin/php /var/www/yourdomain.com/app/sendDailyEmail.php
```
15. Now, signup! Send an email to <b>signup@yourdomain.com</b>. Within a few seconds, you should see it get posted to your site and create a new user.

## Troubleshooting
- If you aren't getting daily emails, make sure the cron is running.
- If your emails aren't getting added to the database, check the app logs, as well as the Mailgun logs. The Mailgun logs are hosted at mailgun.com
- If you don't want to use mysql, choose whatever datastore you like.  Just create a new config file and update the Db.class.php to pull in the correct config file.
- composer is freaking out...please contact me and I can help you out.

## Cost
Assuming 1 signup email per person and 30 posts/month, Mailgun's free tier of 10,000 emails per month would allow you to comfortably run this service for ~300 people. However, this does not take into account the server requirements and data storage. For a single person, as I intend to do, it's well within the limits of a very cheap cloud server or two.
