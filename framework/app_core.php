<?php

/*
	* RottenEggs App Framework.
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
	* @description		Application/API Toolset
	* @copyright  		Copyright (c) 2010, Monir Boktor. (http://www.rotteneggs.com)
	* @license    		http://www.opensource.org/licenses/bsd-license.php - BSD
	* @version    		Ver: 0.1.4 2011-01-01 14:48
	* 
	*/
			
	
	// These can either be defined by or in your program
	// ------------------------------------------------
	
//	define('PRIVATE_KEY','xxxx');													// My private app key provided by RottenEggs.com
//	define('BASE_URL','http://www.rotteneggs.com/obj/apps/SiteAchievements/');		// URL path to your main app page	
	
	// MySQL variables
//	define('SQL_HOST','localhost');										// Address of the MySQL Server
//	define('SQL_USER','sqluser');										// MySQL Username 
//	define('SQL_PASSWORD','mypassword');								// MySQL Password
//	define('SQL_DATABASE','rotteneggs_apps_Badges');					// MySQL Database to use
	
	// ------------------------------------------------	
	
	// Static constants, no modification necessary
	define('MEMBER_ID',$_GET['member_id']);								// Member viewing the app
	define('SESSION_ID',$_GET['sess_id']);								// Should match md5(MEMBER_ID.PRIVATE_KEY)
	define('APP_ID',$_GET['app_id']);									// App ID 				
	
	// Static setup for RottenEggs API
	define('ENC_METHOD','json');										// Encoding method for API
	define('API_URL','http://www.rotteneggs.com/api/');
	define('APP_URL','http://www.rotteneggs.com/obj/process/app_pass/?id='.APP_ID);	// URL members see for this app
	
	
	abstract class App_Core {
	
		public function __construct() {
			
			
		}
		
		
		/*
		* Terminates the script with error message						
		*/
		public function _Die($message) {			
			die('<h1 style="color:#FFF">Failure. '.$message.'</h1>');
		}
		
		/*
		* Simple sanitizer
		* Not to be considered for full text sanitizing.
		* @param string s Any text string, in this case it's designed pretty much for member_ids
		* @param int forceLength Clip the string to no more than this many chars.
		*/
		
		public function _Clean($s,$forceLength=255) {
						
			$s	=	str_replace ("'","&rsquo;",$s);
			$s	=	str_replace ('"',"&quot;",$s);
			$s	=	preg_replace('/[^a-zA-Z0-9\-\,.\+\/!\?\_\*\#\@\%\:\&\;\s]/', '', $s);
		
			$s = 	mysql_real_escape_string($s);
			return substr($s,0,$forceLength);
		}
		
		
		
		
		/*
		* To ensure we are still communicating with a valid member, we can not navigate away from
		* the /obj/process/app_pass/ page.  To allow us to simulate navigation we simply supply more _GET parameters
		* in any app links.
		
			ie. to simulate a link to page.php, we would simply supply this function with load=page.php and
			this app will load the approriate page when /obj/process/app_pass/ calls it.
		
		* @param string Parameters that need to pass back to this app
		* @return string A link that will navigate back to this app and carries the URL Parameters encode to ensure safe delivery
		*/
		public function _Link($URL_parameters) {
		
			return (APP_URL.'&st='.base64_encode($URL_parameters));
			
		}
		
		/*
		* Formats a link and sends a request to the API server for the information
		
			ie. _Request('/func/members/list') would return a JSON encoded list of all the members that use this app
			see the API help for detailed listing of all commands available.
		
		* @param string action Main action being requested, ie. /{member_id}/stat
		* @param string parameters Optional - Any additional parameters that need to be passed to the API server
		* @param string POST_form Optional - If supplied, Curl will send POST form data to the url instead of a GET
		* @return string The requested data
		*/
		public function _Request($action,$parameters=false,$POST_Form=false) {
		
			$action = preg_replace('/^\//','',$action);						
			
			return $this->Curl_Fetch(API_URL.$action.'?key='.PRIVATE_KEY.'&enc_method='.ENC_METHOD.'&'.$parameters,$POST_Form);
			
		}
		
		
		/*
		* Tests the private key and session id to ensure the RottenEggs
		* server dispatched this app.  Additionally it tests to ensure the required
		* extensions are loaded for PHP for this framework to function
		
		* @throws exception on any failed resource
		* @return bool True when valid
		*/
		protected function StartUp_Validation() {

			// Start-up Error checking
			try {
								
				// Ensure Private Key validates
				if ($_GET['privateKey'] != PRIVATE_KEY) 		throw new Exception('Invalid private access key');				
				
				// App can run standalone if the Private Key is supplied as the offline variable
				// Useful when running as cron job
				if ($_GET['offlineKey'] == PRIVATE_KEY) { define(OFFLINE_MODE,true);}
				else if (SESSION_ID != md5(MEMBER_ID.PRIVATE_KEY)) 	throw new Exception('Invalid session ID');
				else { define(OFFLINE_MODE,false); }																								
												
				// Ensure required extensions exist 
				if (!function_exists('curl_init'))   			throw new Exception('App_Core needs the CURL PHP extension.');
				if (!function_exists('json_decode')) 			throw new Exception('App_Core needs the JSON PHP extension.');
				
				// Ensure MySQL connects
				$this->SQL_Link = $this->SQL_Connect();
				
				// Populate the _GET array if we've passed through the rotteneggs API call
				if ($_GET['st'])	{
						$stDec	=	base64_decode($_GET['st']);						
						parse_str($stDec,$st_array);						
						$_GET = array_merge($_GET,$st_array);
				}
				
			} 
			catch (Exception $e) {
				$this->_Die('App could not start: '.$e->getMessage());
			}
			return true;
		}						
		
		/*
		* Connects to the MySQL server defined by constants
		
		* @return Returns a MySQL link identifier on success or throws exception. 
		*/
		private function SQL_Connect() {
				$sql_status		=	@mysql_connect(SQL_HOST, SQL_USER, SQL_PASSWORD);							
				@mysql_select_db (SQL_DATABASE);
				
				if (!$sql_status) throw new Exception('Could not connect to MySQL server.');
				return $sql_status;
		}
		
		
		/*
		* Executes a SQL query
		
		* @param string query Supply an valid MySQL Select/Update/Insert statement
		* @return array On selects: returns the result of the query, if select it will return an ARRAY of fields/values 
		*/
		public function SQL_query($query) {
		
			$result = mysql_query($query,$this->SQL_Link);
			
			if (preg_match('/^select/i',$query)) {			
					$nm = 0;
						if ($result) while ($r = mysql_fetch_array($result)) {
							reset ($r);
								while (list ($data_key, $data_val) = each ($r)) {
									if (is_numeric($data_key)) continue;
									$array[$nm]["$data_key"]= $data_val;							
								}
							$nm++;
						}
			} else return;
			
			return ($array);
					
		}
		
					
		/*
		* Fetches a document using the CURL extension
		
		* @param string url The address of the document you are requesting		
		* @param string POST_Form Optional - If supplied Curl will send POST Form data instead of a get
		* @return string Returns the document 
		*/
		protected function Curl_Fetch($url,$POST_Form=false) {
				
			$user_agent = 'Mozilla/4.0 (compatible; App-Curl ID:'.APP_ID.'  )';
		    
			$ch = curl_init();    // initialize curl handle
			
			curl_setopt($ch, CURLOPT_URL, $url); 			// set url to post to		
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    // allow redirects
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 	// return into a variable
			curl_setopt($ch, CURLOPT_MAXCONNECTS,  1000);	// max connections					
			curl_setopt($ch, CURLOPT_PORT, 80);            	//Set the port number
			curl_setopt($ch, CURLOPT_TIMEOUT, 45);		    // times out after 15s
			
			curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
			
			if ($POST_Form) {
				curl_setopt ($ch, CURLOPT_POST, 1);
				curl_setopt ($ch, CURLOPT_POSTFIELDS, $POST_Form);
			}
			
			$document = curl_exec($ch);
			
			$info = curl_getinfo($ch);
			if (curl_errno($ch)) {
				$lastCurlError	=	curl_errno($ch);
			}
			
			curl_close($ch);
			
			return $document;
			
		}
		
		
		
	}
	
	
?>