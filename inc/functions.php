<?php
/**
 * Toggl Sync
 * Extra functions that contribute to the operation of TogglSync.
 *
 * @author Andrew Natoli
 * @site http://AndrewNatoli.com
 */

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