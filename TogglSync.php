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

/**
 * $startTime
 * Time program started execution so we can track the time spent.
 */
$startTime = microtime(true);

displayStatus("========== TogglSync ==========");
displayStatus("Running at " . Date('Y-m-d H:i:s'));
displayStatus("===============================");

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
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
displayStatus("Getting workspace projects from Toggl");
$projects = TogglWorkspace::getWorkspaceProjects($TOGGL_WORKSPACE);

/*
 * Here's where the fun begins
 * Go through all of our projects
 */
foreach ($projects as $Project) {
    try {
        //See if we've already added this project...
        $stmt = $db->prepare("SELECT * FROM ".$TBL_PROJECTS." WHERE pid=?");
        $stmt->execute(array($Project['id']));

        //If we haven't added the project, add it!
        if($stmt->rowCount() == 0) {
            try {
                $stmt = $db->prepare("INSERT INTO ".$TBL_PROJECTS." (pid,wid,cid,name,actual_hours) VALUES(:pid,:wid,:cid,:projectname,:actual_hours)");
                $stmt->execute(array(   ':pid'          => $Project['id'],
                                        ':wid'          => $Project['wid'],
                                        ':cid'          => $Project['cid'],
                                        ':projectname'  => $Project['name'],
                                        ':actual_hours' => $Project['actual_hours']
                ));
                displayStatus("Created project " . $Project['id'] . " - " . $Project['name'] . " - " . $Project['cid']);
            }
            catch (PDOException $e2) {
                displayStatus("Error creating project record. Project ID: " . $Project['id']);
                die($e2);
            }
        }
        //Update the project's actual_hours count
        else {
            try {
                $stmt = $db->prepare("UPDATE ".$TBL_PROJECTS." SET actual_hours=:actual_hours WHERE pid=:pid");
                $stmt->execute(array (  'actual_hours'  => $Project['actual_hours'],
                                        ':pid'          => $Project['id']
                ));
                displayStatus("Updated project " . $Project['id']);
            }
            catch (PDOException $e) {
                displayStatus("Could not update project " . $Project['id']);
                die($e);
            }
        }
        /*
         * Get the time logs for this project
         * Toggl only allows us to fetch a year's worth of records at a time (which is part of why I wrote this program)
         */
        displayStatus("Getting time records for project " . $Project['id']);
        $untilDate = date("Y-m-d");
        $sinceDate = date("Y-m-d",strtotime("-1 year",time()));
        $timeLogs= TogglReport::detailed(array("user_agent"=>$TOGGL_USER,"workspace_id"=>$TOGGL_WORKSPACE,"project_ids"=>$Project['id'],"since"=>$sinceDate,"until"=>$untilDate));
        foreach ($timeLogs['data'] as $TimeLog) {
            //See if the record exists in the database
            //TODO: Get the most recent TimeLog first, check if that's in the database. More efficient than checking EVERY log.
            try {
                $stmt = $db->prepare("SELECT * FROM ".$TBL_TIMELOGS." WHERE id=?");
                $stmt->execute(array($TimeLog['id']));
                //If the record isn't in the database, add it!
                if($stmt->rowCount() == 0) {
                    try {
                        $stmt = $db->prepare("INSERT INTO ".$TBL_TIMELOGS." (id,pid,description,start,end,dur,client,project) VALUES(:id,:pid,:description,:start,:end,:dur,:client,:project)");
                        $stmt->execute(array(   ':id'           => $TimeLog['id'],
                                                ':pid'          => $TimeLog['pid'],
                                                ':description'  => $TimeLog['description'],
                                                ':start'        => $TimeLog['start'],
                                                ':end'          => $TimeLog['end'],
                                                ':dur'          => $TimeLog['dur'],
                                                ':client'       => $TimeLog['client'],
                                                ':project'      => $TimeLog['project']
                        ));
                        displayStatus("Added time record " . $TimeLog['id']);
                    }
                    catch(PDOException $e) {
                        displayStatus("Couldn't insert time record " . $TimeLog['id']);
                        die($e);
                    }
                }
            }
            catch(PDOException $e) {
                displayStatus("Couldn't check for existing time record (" + $TimeLog['id'] + ")");
                die($e);
            }
        }

    }
    catch(PDOException $e) {
        displayStatus("Error looking for project " . $Project['id'] . " in database.");
        die($e);
    }
}


/**
 * $endTime
 * Mark when the program finished so we can show the time elapsed
 */
$endTime = microtime(true);

/**
 * $elapsedTime
 * Total time spent executing the program
 */
$elapsedTime = ($endTime - $startTime);

//Show the elapsed time.
displayStatus("Program finished in " . $elapsedTime . " seconds.");
displayStatus("===========================================");


/*
function getWorkspaceProjects() {
    return TogglWorkspace::getWorkspaceProjects($this->workspace_id);
}
*/

//TogglReport::detailed(array("user_agent"=>$TOGGL_USER,"workspace_id"=>$TOGGL_WORKSPACE));
//TogglProject::getProjectData($project_id);

/*
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
 * getCid
 * Becuase some projects might not be bound to a client, we need to give some kind of value.
 * @param $Project The project we cant to get the cid of
 * @return int
 */
function getCid($Project) {
    if(empty($Project['cid']) || $Project['cid'] == "")
        return -1;
    else
        return $Project['cid'];
}

/**
 * displayStatus
 * Prints the status of the program as it's executing.
 * @param $message String
 */
function displayStatus($message) {
    echo $message . "\n";
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