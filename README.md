TogglSync
=========

Export your time records and project data from Toggl to your own MySQL database.

I wanted a solution to view my time records through my own business management software that didn't involve bombarding Toggl with API requests
so I wrote this simple PHP program to do a nightly download (scheduled via cron) so I also have backups of the data and can do whatever I want with it.

I also took the opportunity to finally get myself off the deprecated php mysql extension. I'm using PDO here.
