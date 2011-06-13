<?
	/*
	* RottenEggs App Framework + Sample
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
	* @description		Application/API Toolset + Sample
	* @copyright  		Copyright (c) 2010, Monir Boktor. (http://www.rotteneggs.com)
	* @license    		http://www.opensource.org/licenses/bsd-license.php - BSD
	* @version    		Ver: 0.1.1 2011-01-01 14:48
	* 
	*/
	
	include('framework/app_core.php');
	include('program/badges.php');
	
	$app = new Badges();
	
	// Generate the text for this page
	$Body = $app->AppBody($_GET);

	// Was an ajax call made?  If so, simply display the body without formatting.
	if ($_GET['aj_call']) {echo $Body;die();}
	
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<base href="<?=BASE_URL;?>" />
<link href="css/s.css" rel="stylesheet" type="text/css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script src="js/s.js"></script>
<script src="js/scrollable.min.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>RottenEggs Badges</title>
</head>
<body>
<div id="header"></div>
<? if ($app->ShowNavigation) { ?>
<div class="navigation">
	<ul>
        <li class="nameHead"><?=$app->Format_NameHeadline($app->Show_member_id);?></li>
    	<a href='<?=$app->_Link('page=myBadges');?>'><li class="<? if (!$_GET['page'] || $_GET['page'] == 'myBadges') echo 'sel';?>">My Badges</li></a>
        <a href='<?=$app->_Link('page=allBadges');?>'><li class="<? if ($_GET['page'] == 'allBadges') echo 'sel';?>">Claim Badges</li></a>
        <a href='<?=$app->_Link('page=leaderboard');?>'><li class="<? if ($_GET['page'] == 'leaderboard') echo 'sel';?>">Badge Leaderboard</li></a>
	</ul>
</div>
<? } ?>
<div class="appBody">

	<? if ($app->badgesVisible>3) { ?>
    <div id="actions">
  		 <div class="prev">&and; Scroll Up</div>
   		 <div class="next">Scroll Down &or;</div>
	</div>
    <? } ?>
	<div class="scrollable vertical">  
	    <div class="items">
		<?=$Body;?>
       	</div>
    </div>
</div>

</body>
</html>
