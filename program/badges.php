<?php

/*
	* RottenEggs App - Badges - Sample Application
	* Copyright (c) 2010, Monir Boktor. (http://www.rotteneggs.com)
	* All rights reserved.
	* 
	* License
	*
	* Redistribution and use in source and binary forms, with or without modification, 
	* are permitted provided that the following conditions are met:
	*
	* - 	Redistributions of source code must retain the above copyright notice, 
	* 		this list of conditions and the following disclaimer.
	* 
	* -	Redistributions in binary form must reproduce the above copyright notice, 
	* 		this list of conditions and the following disclaimer in the documentation and/or other 
	* 		materials provided with the distribution.
	* 
	* -	Neither the name of the RottenEggs nor the names of its contributors may be used to 
	* 		endorse or promote products derived from this software without specific prior written permission.
	* 
	* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR 
	* IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY 
	* AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR 
	* CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL 
	* DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
	* DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER 
	* IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF 
	* THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	* 
	* @framework		RottenEggs App Development
	* @description		Application/API Sample Application
	* @copyright  		Copyright (c) 2010, Monir Boktor. (http://www.rotteneggs.com)
	* @license    		http://www.opensource.org/licenses/bsd-license.php - BSD
	* @version    		Ver: 0.1.2 2011-06-12 14:48
	* 
	*/
	
	// Modify these constants
	// ------------------------------------------------
	
	define('PRIVATE_KEY','cf3fb1b18c-Example-01c3e7fb5d6944f3');				// My private access key provided by RottenEggs.com
	define('BASE_URL','http://www.rotteneggs.com/obj/apps/SiteAchievements/');	// URL path to your main app page	
	
	// MySQL variables
	define('SQL_HOST','192.168.0.81');									// Address of the MySQL Server
	define('SQL_USER','sqluser');										// MySQL Username 
	define('SQL_PASSWORD','sqlpassword');								// MySQL Password
	define('SQL_DATABASE','rotteneggs_Badges');							// MySQL Database to use
	
	
	
	class Badges extends App_Core {
		
		
		
		/**
		* Start the application
		*
		* @param String $url the URL to make the request to
		* @param Array $params the parameters to use for the POST body
		* @param CurlHandler $ch optional initialized curl handle
		* @return String the response text
		*/
		public function __construct() {
		
			// Execute startup items 
			$this->Startup_Validation();	// Startup validation
		
		}
		
		/**
		* Selects from a message to send to the member's board at rotteneggs
		* @param string member_id ID of the member
		* @param string message Send this message to the member's board		
		* @return bool 
		*/
		private function PostBoardMessage($member_id,$message) {
			
			$this->_Request($member_id.'/board/post',false,'message='.urlencode($message));
			return true;
		}
		
		
		/**
		* Select all badges this member has already claimed
		* @param string member_id ID of the member to check for
		* @param fields Which fields to select, defaults to all fields				
		* @return array	All defined badge records
		*/
		private function SelectMyBadges($member_id,$fields='*') {
			
			$r	=	$this->SQL_query('select '.$fields.' from badges_defined,members_tracking 
									 where member_id = "'.$this->_Clean($member_id,36).'"
									 and badges_defined.badge_id = members_tracking.badge_id
									 order by badge_group,ts
									 ');			
			return $r;
		}
		
		/**
		* Select an existing badge if it exists for this member
		* @param string member_id ID of the member to check for
		* @param int badge_id badge to check for
		* @param string fields The columns to select
		* @return array	The badge record
		*/
		private function SelectExistingBadge($member_id,$badge_id,$fields) {
			
			$r	=	$this->SQL_query('select '.$fields.' from badges_defined,members_tracking 
									 where member_id = "'.$this->_Clean($member_id,36).'"
									 and badges_defined.badge_id = members_tracking.badge_id
									 and members_tracking.badge_id = '.$badge_id.'
									 ');			
			return $r;
			
		}
		
		
		/**
		* Select all badges available, grouped by badge_group, lowest sorting first
		* Filters out claimed badges
		*		
		* @return array	All defined badge records
		*/
		private function SelectAllBadges($member_id) {
			
			// Filter out claimed badges from the list
			$claimed	=	$this->SelectMyBadges($member_id,'members_tracking.badge_id');
			if ($claimed) foreach ($claimed as $c) {
				$filterOut .=' and badge_id != '.$c['badge_id'];	
			}
			
			$r	=	$this->SQL_query('select * from (select * from badges_defined where 1 '.$filterOut.' order by sorting) as tmpTable group by badge_group order by pointValue');						
			return $r;
		} 
		
		
		/**
		* Select a single badge by id		
		* @return array	Badge record
		*/
		private function SelectBadge($badge_id) {
			
			$r	=	$this->SQL_query('select * from badges_defined where badge_id = '.$this->_Clean($badge_id+0));	
			
			return $r[0];
		}
		
		/**
		* Slice a section of the leaderboard	
		* @param int cpos The position to slice from
		* @param int limit The max records to return
		* @return array	Badge record
		*/
		private function SelectLeaderboard($cpos,$limit=100) {
			
			// Ranks them as the query is executed
			$r	=	$this->SQL_query('select *,@rank := @rank + 1 as rank from members_ranking,(SELECT @rank := 0) r order by score desc limit '.$cpos.','.$limit);				

			return $r;
		}
		
		
		private function SelectMyRecordLeaderboard($member_id) {
			
			// Ranks them as the query is executed
			$me	=	$this->SQL_query('select * from members_ranking where member_id ="'.$this->_Clean($member_id,36).'"');		
			return $me[0];
		}
		
		private function SelectMyPositionLeaderboard($member_id) {
			
			// Ranks them as the query is executed
			$me	=	$this->SQL_query('select score from members_ranking where member_id ="'.$this->_Clean($member_id,36).'"');	
			
			// Find what that position will be
			$r	=	$this->SQL_query('select count(*) as total from members_ranking where score > '.$me[0]['score']);	

			return $r[0]['total']+1;
		}
				
		
		
		// -------------------------------------------------------------------------------------
		/**
		* Retrieve information from the RottenEggs API, caches it to the table members_cache for faster look ups
		* purges the data after 1 hour
		* @param string member_id The member's ID to pull
		* @param string action API action to execute (ie. {member_id}/stat/all or {member_id}/confirm)
		* @param int cacheDuration Consider anything older than this many seconds as out of date
		* @return json object.  (ie. $stat->name)
		*/
		private function MemberRequest($member_id,$action,$cacheDuration=.3600) {
			
			// Hash for easier storage
			$hash = md5($member_id.$action.'request');
			
			// This script has already called this function, return what we already know
			if ($cacheDuration && $this->MemberRequest_Cached[$hash]) return $this->MemberRequest_Cached[$hash];
			
			// First check the cache to see if it has a recent stat
			if ($cacheDuration) $ts	=	$this->SQL_query('select * from members_cache where member_id = "'.$this->_Clean($member_id,36).'" and action="'.$hash.'"');	
				
			// If not recent, pull the latest data from RottenEggs.  Caches for 1 hour
			if ($cacheDuration && (time() - strtotime($ts[0]['updated'])) < $cacheDuration) $stats = json_decode(base64_decode($ts[0]['cache']));
			else {
				
				// Request from RottenEggs all the stats for this member
				$raw = $this->_Request($action);
				
				// Decode the raw data
				$stats	=	json_decode($raw);
				
				// Save it the the cache table
				$this->SQL_query('insert into members_cache 
								 (member_id,action,cache,updated) 
								 values 
								 ("'.$this->_Clean($member_id,36).'","'.$hash.'","'.base64_encode($raw).'",now())');
				
				
			}
			
			// Save for later calls in this script
			$this->MemberRequest_Cached[$hash] = $stats;
			
			return $stats;
		}
		
		
		
		
		/**
		* Does this member qualify for this badge?
		* @param string member_id ID of the member to check for
		* @param array Define badge record
		* @return bool True if member can claim it
		*/
		private function Claimable($member_id,$badge) {
			if (!$badge) return false;
			
			$stats	=	$this->MemberRequest($member_id,$member_id.'/stat/all',60*5);
			
			// Execute the function detailed in the badge record
			$result = $this->$badge['stat_func']($member_id,$badge,$stats);
			
			return $result;
		}
		
		/**
		* Generates a link that jQuery will use to execute the claim action
		* @param string member_id ID of the member
		* @param int Define badge ID
		* @return string Link for jQuery
		*/
		private function ClaimLink($member_id,$badge_id) {
			
			return '<div id="unc_'.$badge_id.'" class="unclaimed"  url="'.$this->_Link('&aj_call=true&page=claiming&badge_id='.$badge_id).'">Claim It</div>';	
		}
		
		
		/**
		* Actually executes the action of claiming a badge
		* @param string member_id ID of the member
		* @param int Define badge ID
		* @return string Text for jQuery
		*/
		private function ClaimBadge($member_id,$badge_id) {
			
			$badge =	$this->SelectBadge($badge_id);
			// Ensure that this member can claim this badge 		
			if (!$this->Claimable($member_id,$badge))  return 'Not claimable';
			
			// Ensure it hasn't already been claimed
			$existing	=	$this->SelectExistingBadge($member_id,$badge_id,'members_tracking.badge_id');
			if ($existing) return 'Already Claimed';
			
			// Save it
			$this->SQL_query('insert into members_tracking
								 (member_id,badge_id) 
								 values 
								 ("'.$this->_Clean($member_id,36).'","'.$this->_Clean($badge_id+0).'")');
			
			$didSave	=	$this->SelectExistingBadge($member_id,$badge_id,'members_tracking.badge_id');
			
			// Mark the points on the leaderboard ranking if it saved properly
			if ($didSave) $this->SQL_query('insert into members_ranking
								 (member_id,score,totalBadges) 
								 values 
								 ("'.$this->_Clean($member_id,36).'","'.$badge['pointValue'].'",1)
								 
								 on duplicate key update totalBadges=totalBadges+1, score = score + '.$badge['pointValue'].'
								 ');
			else return 'Unknown Error';
			
			// Post board message about it
			// RottenEggs translates any text in brackets, i.e. {name:weasel}, into a link if it matches a members name
			$info	=	$this->MemberRequest($member_id,$member_id.'/stat/all');
			$this->PostBoardMessage($member_id,'{member:'.$info->name.'} has obtained the badge "'.$badge['badge_name'].'"');
			
			return 'Claimed!';
		}
		
		// Badge functions
		// -------------------------------------------------------------------------------------
		
		/**
		* Check a stat set in the badge's record
		* @param string member_id ID of the member
		* @param array badge The badge record
		* @param array stats The stats for this member
		* @return True if successful
		*/
		private function statCheck($member_id,$badge,$stats) {
								
			// Does this member's stat equal or exceed the goal set in the badge record?
			if ($stats->$badge['stat_value'] >= $badge['goal']) return true;
			else return false;
			
		}
		
		/**
		* Check a unique hook function set in the badge's record
		* @param string member_id ID of the member
		* @param array badge The badge record
		* @param array stats The stats for this member
		* @return True if successful
		*/
		private function hookCheck($member_id,$badge,$stats) {
	
			// Execute the function set in the stat_value
			$func	=	'hook_'.$badge['stat_value'];
			return $this->$func($member_id,$badge,$stats);			
		}
		
		// [PRIVATE]
		// Records a viewed hour
		private function hook_setTimes($member_id,$badge,$stats) {
						
			// Check to see if it's ready for claiming
			$g	=	$this->SQL_query('select goal_tracking,success from members_goals 
									 where member_id = "'.$member_id.'" and badge_id = "'.$badge['badge_id'].'"
									 ');	
			
			// Already confirmed as completed
			if ($g[0]['success']) return true;			
			
			// Serialize and store the hour
			$goals	=	unserialize($g[0]['goal_tracking']);
			
			$date	=	date('Y-m-d');
			$goals[$date]		.=	date('H',time()).'|';
			$onlyToday[$date]	=	$goals[$date];
			
			$goal_serialize	=	mysql_real_escape_string(serialize($onlyToday));
			
			//Save it
			$this->SQL_query('insert into members_goals 
							 (member_id,badge_id,goal_tracking) 
							 values 
							 (
							  "'.$member_id.'","'.$badge['badge_id'].'","'.$goal_serialize.'"
							 ) on duplicate key update goal_tracking = "'.$goal_serialize.'"');
			
			
			// Check to see if we have success
			$bits	=	explode('|',$onlyToday[$date]);
			$hours	=	explode('|',$badge['goal']);
			if ($bits) foreach ($bits as $bit) {
				foreach ($hours as $h) {
					if ($bit == $h) $found[$h] = true;
				}
			}
			
			
			if (count($found) == count($hours)) {
				// Success
				$this->SQL_query('update members_goals set success=1 where member_id = "'.$member_id.'" and badge_id = "'.$badge['badge_id'].'"');				
				return true;				
			}
			// No goal reached
			return false;
		}
		
		
		// -------------------------------------------------------------------------------------
		public function Format_NameHeadline($member_id) {
			$info	=	$this->MemberRequest($member_id,$member_id.'/stat/all');
			$s = '<a href="'.$info->url.'" target=_parent>'.substr($info->name,0,20).'</a>';
			return $s;
		}
		
		/**
		* Formats all badges with CSS styles
		*		
		* @param array	A single badge record
		* @return string Body that will be echo'd in the main app
		*/
		private function Format_Badges($member_id,$badge) {	
					
			// Has the badge been claimed? Yes, show the date claimed
			if ($badge['ts']) $claim ='<div class="claimed">'.$badge['pointValue'].' pts</div>';				
			
			// Has not been claimed, show button to 'claim it' if member qualifies for it
			elseif ($this->Claimable($member_id,$badge)) $claim = $this->ClaimLink($member_id,$badge['badge_id']);
			
			// secret badges do not show their description until after member is able to claim it
			if ((!$claim && !$badge['ts']) && $badge['secret'])  $badge['badge_desc'] = '???';
			
			// Format the full badge div
			$d	=	'<div id="'.$badge['badge_id'].'_'.$member_id.'" class="badgeRow">'.
						'<img src="'.$badge['badge_icon'].'">'.
						'<div class="name">'.$badge['badge_name'].'</div>'.
						'<div class="desc">'.$badge['badge_desc'].'</div>'.
						$claim.
					'</div>';
			
			// How many badges will be visible?
			$this->badgesVisible++;
					
			return $d;
		}
		
		
		/**
		* Cycles through all the badge records
		*		
		* @param array An array of all badges we want to display
		* @return string Listing of badges
		*/
		private function ShowBadges($member_id,$allBadges) {					
		
			if ($allBadges) $allChunks	=	array_chunk($allBadges,6);	// Show the badges in chunks of 4
		
			if ($allChunks) foreach ($allChunks as $chunked) {
				
				$d .= '<div class="item">';
				if ($chunked) foreach ($chunked as $badge) {				
					$d .=	 $this->Format_Badges($member_id,$badge);					
				} 
				$d .= '</div>';
			}
			
			if (!$d) $d = '<div class="noBadges">You have no badges to list, <a href="'.$this->_Link('page=allBadges').'">browse badges</a> available.</div>';
			
			return $d;
		}
		
		
		// Leaderboard functions
		// -------------------------------------------------------------------------------------
		
		private function Format_Leaderboard($member,$forceRank=false) {
			
			$stats = $this->MemberRequest($member['member_id'],$member['member_id'].'/stat/all',3600*24*31);
			
			// Should we show something other than what is set in the record?
			if ($forceRank) $member['rank'] = $forceRank;
			
			// Show we highlight the row?
			if ($member['member_id'] == MEMBER_ID) $highlight = ' myPos ';
			
			// Build the row
			
			$d	=	'<div class="badgeRow leaderRow '.$highlight.'">'.		
						'<img src="'.$stats->img_tiny.'">'.
						'<div class="name">'.$stats->name.'</div>'.
						'<div class="desc">'.$member['score'].' badge points, 
						<a href="'.$this->_Link('page=listBadges&showFor='.$member['member_id']).'">'.$member['totalBadges'].' badges</a></div>'.
						'<div class="rank">#'.$member['rank'].'</div>'.
					'</div>';
					
			// How many members will be visible?
			$this->badgesVisible++;
			return $d;
		}
		
		/**
		* Show all members on the leaderboard					
		* @return string Listing for the leaderboard
		*/
		private function Leaderboard() {
			
			if (!$_GET['cpos']) $cpos = '0';
			$listing = $this->SelectLeaderboard($cpos);
			
			if ($listing) $allChunks	=	array_chunk($listing,6);	// Show the badges in chunks of 4
			
			if ($allChunks) foreach ($allChunks as $chunked) {
				
				$d .= '<div class="item">';
				if ($chunked) foreach ($chunked as $member) {				
					$d .=	 $this->Format_Leaderboard($member);					
				} 
				$d .= '</div>';
			} 
			
			if (!$d) $d = '<div class="noBadges">There are no more items to display.</div>';
			
			return $d;
		}
		
		// Always show the viewers position on the leader board first
		private function Leaderboard_MyPosition($member_id) {
			$d = '<div class="item">';
			$d .=	 $this->Format_Leaderboard($this->SelectMyRecordLeaderboard($member_id),$this->SelectMyPositionLeaderboard($member_id));
			$d .= '<hr noshade size=1>';
			$d .= '</div>';
			return $d;
		}
		
		
		
		/**
		* Selects the appropriate view requested
		*
		* @param array params Parameters passed to the app
		* @throws Expection when error encountered
		* @return string the response text
		*/
		public function AppBody($params) {
			
			// Show this as the headline name
			$this->Show_member_id = MEMBER_ID;
			$this->ShowNavigation = true;
			
			try {
				
				// Has this member added this app yet? If no, show a message telling them to add the app
				if (!$_GET['page']) {
					$confirm = $this->MemberRequest(MEMBER_ID,MEMBER_ID.'/confirm',0);
					
					if ($confirm->access !='true') {
						$s = '<div class="noBadges">Apps must first be enabled by you to allow them to access a few of your stats and post messages to your board. <br><br> Click the ADD THIS APP to the left to enable this app now.</div>';
						$this->ShowNavigation = false;
						return $s;
					}
				}
				switch ($params['page']) {										
					
					
					// Showing all unclaimed badges available for this member
					case 'allBadges' :							
						$s = $this->ShowBadges(MEMBER_ID,$this->SelectAllBadges(MEMBER_ID));		
					break;
					
					// Listing leaderboard
					case 'leaderboard' :
						$s = $this->Leaderboard_MyPosition(MEMBER_ID);		
						$s .= $this->Leaderboard();		
					break;
					
					// Claiming a badge
					case 'claiming' :
						$s = $this->ClaimBadge(MEMBER_ID,$params['badge_id']);	
					break;
												
					// List all the badges for this member
					case 'listBadges' :		
						$this->Show_member_id = $_GET['showFor'];
						$s = $this->ShowBadges($_GET['showFor'],$this->SelectMyBadges($_GET['showFor']));
					break;
						
					// Showing all claimed badges by this member
					default:						
						$s = $this->ShowBadges(MEMBER_ID,$this->SelectMyBadges(MEMBER_ID));
						
				}
					
			}
			catch (Exception $e) {
				$this->_Die($e->getMessage());
			}
			
			return $s;
		}
		
	}


?>