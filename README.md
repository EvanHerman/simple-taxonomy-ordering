YIKES Inc. Site Monitoring Plugin
=============

The YIKES Inc. site monitoring plugin is a fairly basic plugin which runs in the background and alerts a specified user (or users) of plugin and WordPress core updates.

When an update is needed an API request is also sent to the YIKES Inc. Trello board to create a new checklist item inside of the 'YIKES Monitoring Plugin Notifications' card. (This is hardcoded but in future could be dynamic to allow us to select a given board and card from the dashboard)

You can currently select from a standard WP cron or use the provided cron URL to setup a true cron job. (WP cron jobs fire when a user is on the site. True crons fire at set intervals regardless of traffic)

##### Setup
1. Install the plugin
2. Head into 'Settings > Site Monitoring'
3. Enter both your API and Secret keys
4. Authorize the site with Trello
5. Setup the email addresses to notify of updates
6. Click 'Save settings with test email' to confirm that emails are successfully being sent
7. Sit back and wait for update notifications to roll in 

#### Issues?
If you find that the updates aren't being sent you may want to check on the error log inside of `yikes-site-monitoring/lib/trello/error-log.txt`. This may give you a better indication of what's going wrong.

<br />
**Built by Evan Herman for use on all YIKES monitoring client sites.**
<br />
<br />

To Do
=============


Changes
=============

### 7-3-2015
* Added slack integration
* Updated function to check if Trello checklist item already exists before adding (prevent duplicates for a given site)

### 7-2-2015
Initial release of the awesomeness to come