<?php
/**
 * Toggl Sync
 * Database configuration file
 * @author Andrew Natoli
 * @date 2014 June 13
 * @verison 1
 * @since 1
 */

/*
 * MySQL Database Configuration
 */

/**
 * Database host
 * Default: localhost
 */
$DB_HOST            = "localhost";

/**
 * Database port
 * Usually 3306 in standard environments
 */
$DB_PORT            = 3306;

/**
 * MySQL username
 */
$DB_USER            = "";

/**
 * MySQL user's password
 */
$DB_PASS            = "";

/**
 * Which database to use
 */
$DB_NAME            = "";

/**
 * Table that timelogs will be stored in
 * Default specified in the .sql file is toggl_time
 */
$TBL_TIMELOGS       = "toggl_time";

/**
 * Table for projects to be stored in
 * Default specified in the .sql file is toggl_projects
 */
$TBL_PROJECTS       = "toggl_projects";

/*
 * Toggl Configuration Values
 */

/**
 * User's email address registered with Toggl
 */
$TOGGL_USER         = "";

/**
 * Toggl Workspace ID containing the projects we want
 * If you need to use multiple workspaces, run multiple instances of this program ;)
 */
$TOGGL_WORKSPACE    = "";

/**
 * Toggl API Key
 */
$TOGGL_KEY          = "";