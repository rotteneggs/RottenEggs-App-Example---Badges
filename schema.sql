SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `rotteneggs_Badges`
--

-- --------------------------------------------------------

--
-- Table structure for table `badges_defined`
--

CREATE TABLE IF NOT EXISTS `badges_defined` (
  `badge_id` int(10) unsigned NOT NULL auto_increment,
  `badge_name` varchar(255) NOT NULL,
  `badge_desc` varchar(255) NOT NULL,
  `badge_group` varchar(255) NOT NULL,
  `badge_icon` varchar(255) NOT NULL,
  `stat_func` varchar(20) NOT NULL,
  `stat_value` varchar(255) NOT NULL,
  `goal` varchar(255) NOT NULL,
  `secret` tinyint(1) NOT NULL,
  `sorting` float(10,3) NOT NULL,
  `pointValue` int(11) NOT NULL,
  PRIMARY KEY  (`badge_id`),
  KEY `sorting` (`sorting`),
  KEY `badge_group` (`badge_group`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

--
-- Dumping data for table `badges_defined`
--

INSERT INTO `badges_defined` (`badge_id`, `badge_name`, `badge_desc`, `badge_group`, `badge_icon`, `stat_func`, `stat_value`, `goal`, `secret`, `sorting`, `pointValue`) VALUES(1, 'Loyal Solider', 'Reach 7 consecutive visits.', 'consec_visits', 'img/b/1.png', 'statCheck', 'totalConsecDays', '7', 0, 1000.000, 10);

-- --------------------------------------------------------

--
-- Table structure for table `members_cache`
--

CREATE TABLE IF NOT EXISTS `members_cache` (
  `member_id` varchar(36) NOT NULL default '0',
  `action` varchar(255) NOT NULL,
  `cache` text NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`member_id`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



-- --------------------------------------------------------

--
-- Table structure for table `members_goals`
--

CREATE TABLE IF NOT EXISTS `members_goals` (
  `member_id` varchar(36) NOT NULL default '0',
  `badge_id` int(11) unsigned NOT NULL,
  `goal_tracking` tinytext NOT NULL,
  `success` tinyint(1) NOT NULL default '0',
  `ts` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`member_id`,`badge_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;




-- --------------------------------------------------------

--
-- Table structure for table `members_ranking`
--

CREATE TABLE IF NOT EXISTS `members_ranking` (
  `member_id` varchar(36) NOT NULL default '0',
  `score` int(11) unsigned NOT NULL,
  `totalBadges` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`member_id`),
  KEY `stat` (`score`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `members_ranking`
--

INSERT INTO `members_ranking` (`member_id`, `score`, `totalBadges`) VALUES('16e690a0-0294-11e0-b360-357008b325fb', 2232, 10);


-- --------------------------------------------------------

--
-- Table structure for table `members_tracking`
--

CREATE TABLE IF NOT EXISTS `members_tracking` (
  `member_id` varchar(36) NOT NULL default '0',
  `badge_id` int(11) unsigned NOT NULL,
  `ts` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`member_id`,`badge_id`),
  KEY `users_id` (`member_id`),
  KEY `stat` (`badge_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `members_tracking`
--

INSERT INTO `members_tracking` (`member_id`, `badge_id`, `ts`) VALUES('a4b54c20-0be3-11e0-8fed-0f985d0afb92', 11, '2010-12-20 00:29:06');

