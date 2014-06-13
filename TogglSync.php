<?php
/**
 * Toggl Sync
 * Main Program
 * Downloads records from Toggl to my database so I don't have to mash their server with
 * requests every time I view a report.
 *
 * Designed to be run as a CRON task.
 *
 * @author Andrew Natoli
 * @site http://AndrewNatoli.com
 * @version 1
 * @uses TogglAPI
 */

//Includes
require_once("inc/config.php");
require_once("inc/Toggl/Toggl.php");

//Coming soon!