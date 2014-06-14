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

displayStatus("========== TogglSync ==========");

//Includes
displayStatus("Loading configuration & Toggl SDK");
require_once("inc/config.php");
require_once("inc/Toggl/Toggl.php");

/*
 * Establish a connection to the database
 */
try {
    displayStatus("Establishing database connection");
    $db = new PDO('mysql:host='.$DB_HOST.';dbname='.$DB_NAME.';charset=utf8', ''.$DB_USER.'', ''.$DB_PASS.'');
}
catch(PDOException $e) {
    displayStatus("Could not establish a connection to the database.");
    die();
}

displayStatus("Connected to database.");

Toggl::setKey($TOGGL_KEY);

/*
 * Get a list of projects in our workspace
 */
$projects = TogglWorkspace::getWorkspaceProjects($TOGGL_WORKSPACE);

/*
 * Here's where the fun begins
 * Go through all of our projects
 */
foreach ($projects as $Project) {
    try {
        //See if we've already added this project...
        $stmt = $db->prepare("SELECT * FROM ? WHERE pid=?");
        $stmt->execute(array($TBL_PROJECTS,$Project['id']));

        //If we haven't added the project, add it!
        if($stmt->rowCount() == 0) {
            try {
                $stmt = $db->prepare("INSERT INTO :thetable (pid,wid,cid,name,actual_hours) VALUES(:pid,:wid,:cid,:projectname,:actual_hours");
                $stmt->execute(array(   ':thetable'     => $TBL_PROJECTS,
                                        ':pid'          => $Project['id'],
                                        ':wid'          => $Project['wid'],
                                        ':cid'          => $Project['cid'],
                                        ':projectname'  => $Project['name'],
                                        ':actual_hours' => $Project['actual_hours']
                ));
                displayStatus("Created project " . $Project['id'] . " - " . $Project['name'] . " - " . $Project['cid']);
            }
            catch (PDOException $e2) {
                displayStatus("Error creating project record. Project ID: " . $Project['id']);
            }
        }
        //Update the project's actual_hours count
        else {
            try {
                $stmt = $db->prepare("UPDATE :table SET actual_hours=:actual_hours WHERE pid=:pid");
                $stmt->execute(array (  ':table'    => $TBL_PROJECTS,
                                        ':pid'      => $Project['id']
                ));
            }
            catch (PDOException $e) {
                displayStatus("Updated project " . $Project['id']);
            }
        }
    }
    catch(PDOException $e) {
        displayStatus("Error looking for project " . $Project['id'] . " in database.");
    }
}

/*
function getWorkspaceProjects() {
    return TogglWorkspace::getWorkspaceProjects($this->workspace_id);
}
*/

//TogglReport::detailed(array("user_agent"=>$TOGGL_USER,"workspace_id"=>$TOGGL_WORKSPACE));
//TogglProject::getProjectData($project_id);

/*
function getProjectTimelog($toggl_projectID,$sinceDate="",$untilDate="") {
    if($untilDate == "") $untilDate = date("Y-m-d");
    if($sinceDate == "") $sinceDate = date("Y-m-d",strtotime("-1 year",time()));

    return TogglReport::detailed(array("user_agent"=>$this->user_agent,"workspace_id"=>$this->workspace_id,"project_ids"=>$toggl_projectID,"since"=>$sinceDate,"until"=>$untilDate));
}

function calculateTime($start,$finish) {
    $duration = $finish-$start;
    return $this->timeToDecimal(date("H:i:s",$duration));
}
*/

/**
 * Convert time into decimal time.
 *
 * @param string $time The time to convert
 *
 * @return integer The time as a decimal value.
 */
function timeToDecimal($time) {
    $timeArr = explode(':', $time);
    $decTime = ($timeArr[0]*60) + ($timeArr[1]) + ($timeArr[2]/60);
    return $decTime;
}

/**
 * displayStatus
 * Prints the status of the program as it's executing.
 * @param $message String
 */
function displayStatus($message) {
    echo $message . "<br/>";
    flush();
}

/**
 * printAndHalt
 * Used for debugging. Print the contents of a variable and kill the program.
 * @param $var
 */
function printAndHalt($var) {
    echo "<pre>";
    die(print_r($var));
}