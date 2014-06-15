/*
Toggl Sync Table Structure
Run this on your database server to create tables for great justice!
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `toggl_time`
-- ----------------------------
DROP TABLE IF EXISTS `toggl_time`;
CREATE TABLE `toggl_time` (
  `id` int(11) NOT NULL,
  `pid` int(255) DEFAULT NULL,
  `uid` int(255) NOT NULL,
  `description` text,
  `start` varchar(255) NOT NULL,
  `end` varchar(255) NOT NULL,
  `dur` int(255) NOT NULL,
  `client` varchar(200) DEFAULT NULL,
  `project` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `toggl_projects`
-- ----------------------------
DROP TABLE IF EXISTS `toggl_projects`;
CREATE TABLE `toggl_projects` (
  `pid` int(255) NOT NULL,
  `wid` int(255) NOT NULL,
  `cid` int(255) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `actual_hours` int(255) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

SET FOREIGN_KEY_CHECKS = 1;