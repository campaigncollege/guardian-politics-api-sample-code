<?php
require("includes/guardian_politics_api.class.php");

$searchterm = $_GET['mp'];

$limit = 10; // This is the limit for the number of tweets, plus the number of guardian news stores from the Content API

// THESE ARE YOUR SETTINGS THAT WILL NEED TO BE CHANGED ACCORDINGLY

// These are the secret keys for API's
$guardianContentApiSecretkey = 'guardianContentApiSecretKey';
$theyworkforyouSecretkey = 'TheyWorkForYouSecretKey';

// This is the path of the Zend Library
set_include_path('/var/www/vhosts/whatcouldicook.com/httpdocs/library');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Politics API</title>

<?php // We are using the YUI Grids Css file for our layout ?>
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.8.0r4/build/reset-fonts-grids/reset-fonts-grids.css">

<?php 

// Also included is the Javascript file tablecloth which will add functionality to our data
// This file can be found at - http://cssglobe.com/lab/tablecloth/
?>
<link href="includes/tablecloth.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="includes/tablecloth.js"></script>

<style type="text/css">
#doc, #doc2, #doc3, #doc4, .yui-t1, .yui-t2, .yui-t3, .yui-t4, .yui-t5, .yui-t6, .yui-t7 {
	margin:auto;
	text-align:left;
	width:98%;
}
h1, h2, h3, h4, h5, h6 {
	color:#314F7B;
	font-family:"Lucida Sans Unicode","Lucida Grande",sans-serif;
	font-weight:normal;
}
#searchtips {
	width:280px;
}
.profilebox {
	margin-left:7px;
	float:left;
	width:100%;
}
.profilepicture {
	float:left;
	clear:both;
	padding-right:1em;
	padding-bottom:0.5em;
	width:auto;
}
.tweet {
	font-family: Georgia, serif;
	font-size: 100%; 
}
.tweet .tweet_list {
	-webkit-border-radius: .5em;
	list-style-type: none;
	margin: 0;
	padding: 0; 
}
.tweet .tweet_list li {
	overflow: auto;
	padding: .5em;
	border-bottom:1px dotted #999999;
}
.tweet .tweet_list li a {
	color: #0C717A; 
}
.tweet .tweet_list .tweet_even {
	background-color: #91E5E7; 
}
.tweet .tweet_list .tweet_avatar {
	padding-right: .5em;
	float: left; 
}
.tweet .tweet_list .tweet_avatar img {
	vertical-align: middle; 
}
.flickrthumb {
	float:left;
	border:1px solid #999999;
	padding:4px;
	margin-bottom:7px;
}
.mpgo{
	margin-bottom:5px;
	font:13px "Lucida Sans Unicode","Lucida Grande",sans-serif;
	border:2px solid #ffd324;
	width:260px;
	padding:4px
}
.mpsearch { 
	font-size:36px; 
	width:440px; 
	border:10px solid #ffd324;
}
.mpgo { 
	font-size:35px; 
	width:auto; 
	background-color:#FFF; 
	color:#090; 
	border:10px solid #ffd324; 
	cursor:pointer;
}
</style>

</head>

<body>
<div id="doc4">
    
<div id="bd">

<pre></pre>
<div class="yui-t6">
		<div id="yui-main">
			<div class="yui-b">
            <?php
			
			// Error checking if the user has not entered a search term
			if (empty($searchterm)) {
				echo '
				<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="get">
					<input autocomplete="off" class="mpsearch" name="mp" type="text" value="';
					if (empty($_GET['mp'])) {
						echo 'Search here..." />';
					} else {
						echo $searchterm.'" />';
					}
					echo '
					<input value="Go" class="mpgo" type="submit" />
				</form>
				<br class="clear" />';
				if (empty($_GET['mp'])) {
						echo '<h1>Use the box above to search for an MP</h1><h3>Created by Daniel Levitt</h3>';
					} else {
						echo '<h1>No results found for "'.$searchterm.'", please try again!</h1>
						<h1>p.s Use full names</h1>';
					}
					
				echo '
				</div>';
				die;
			}
			
			// Get the Politics API PHP Class - Simply shortcuts for parsing the data.
			$politicsapi = new politicsapi;
			$guardianurl = $politicsapi->get_info_on($searchterm); // This get_info_on is the my method of searching the API
			
			$mpinfo = file_get_contents($guardianurl);
			
			// Now when we have some JSON, we can call 'decode_for_php' which will turn it into an associative array
			$mpinfo = $politicsapi->decode_for_php($mpinfo);
			
			
			// Add a bit of styling for the search form (e.g. so if the MP is labour we turn the search box red)
			echo "<style type=\"text/css\">";
			$partyinfo = $mpinfo['person']['party'];
			if ($partyinfo['name'] == 'Labour') {
				echo ".mpsearch,.mpgo { border:10px solid #DC291E }";
			} elseif ($partyinfo['name'] == 'Conservative') {
				echo ".mpsearch,.mpgo { border:10px solid #0093D0 } ";
			} elseif ($partyinfo['name'] == 'Liberal Democrats') {
				echo ".mpsearch,.mpgo { border:10px solid #fbd44b } ";
			} else {
				echo ".mpsearch,.mpgo { border:10px solid #FFF } ";
			}
			echo "</style>";
					
			$politicsname = urlencode($searchterm);
			
			// This is the theyworkforyou component, here we find the ID of the MP using their search
			// and then store this into $twfuid for use later on in this file. 
			
			// (this is NOT a fool proof method and included here as an example only)
			
			$twfu_json = file_get_contents('http://www.theyworkforyou.com/api/getMPs?key='.$theyworkforyouSecretkey.'&num&search='.$politicsname.'&output=js');
			$twfu = $politicsapi->decode_for_php($twfu_json);
			$twfuid = $twfu[0]['person_id'];
			
			?>
            
            <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="get">
            	<input autocomplete="off" class="mpsearch" name="mp" type="text" value="<?php echo $searchterm ?>" />
                <input value="Go" class="mpgo" type="submit" />
            </form>
            <br class="clear" />
			
                <div class="yui-g">
                  <div class="yui-u first">
                  <div class="profilebox">
				  <?php 
				  echo "<img class='profilepicture' src='".$mpinfo['person']['image']."'>";
				  echo "<h3>".$partyinfo['name']."</h3>";
				  $contact = $mpinfo['person']['contact-details'];
				  $i=0;
				  foreach ($contact['email-addresses'] as $mail) {
				  	  echo "Email ~ <a href='mailto:".$contact['email-addresses'][$i]['email']."'>".$contact['email-addresses'][$i]['type']."</a><br/>";
					  $i++;
				  }
				  ?>
				  </div>
                  <?php // This is the table showing past contests, with HTML formatting for tablecloth.js ?>
				  
				  <?php
                  $contests = $mpinfo['person']['candidacies'];
				  if (!empty($contests)) {
					  echo "<h3>Results</h3>";
					  echo '<table cellspacing="0" cellpadding="0">';
					  foreach ($contests as $contest) {
						  echo "<tr>";
						  echo "<th><a href='".$contest['election']['web-url']."'>".ucwords(strtolower($contest['election']['type']))." ".$contest['election']['year']."</a></th>";
						  echo "<td><a href='".$contest['constituency']['web-url']."'>".$contest['constituency']['name']."</a></td>";
						  echo "<td><a href='".$contest['party']['web-url']."'>".$contest['party']['name']."</a></td>";
						  echo "<td>".number_format($contest['votes-as-quantity'])." <strong>(".$contest['votes-as-percentage']."%)</strong></td>";
						  echo "<td>".$contest['position']."</td>";
						  echo "</tr>";
					  }
					  echo "</table>";
				  }
				  ?>
                  
                  <?php // This is the theyworkforyou component, using the $twfuid we set from before ?>
                  <?php
					echo '<h3>Recent Video Debates</h3>';
					$twfuurl = 'http://www.theyworkforyou.com/api/getHansard?key='.$theyworkforyouSecretkey.'&num=4&person='.$twfuid.'&output=js';
					$twfu = file_get_contents($twfuurl);
					$twfu = $politicsapi->decode_for_php($twfu);					
					foreach ($twfu['rows'] as $debate) {
						echo "<a href='http://www.theyworkforyou.com".$debate['listurl']."'>".$debate['body']."</a><br/><br/>";
					}
				   ?>
                  
                   <h3>Powered by..</h3>
                   <img src="http://static.guim.co.uk/sys-images/Guardian/Pix/pictures/2009/7/9/1247130419382/openPlatformLogo_orange_web.jpg"/>
                   <br class="clear" />
                   <img src="http://www.theyworkforyou.com/images/logo.png"/>
                  
                    </div>
                    <div class="yui-u">
                    <?php // This is the Guardian Content API component ?>
                    
                    <h2>Latest News Stories (Guardian)</h2>
                    <?php
                    $mpnews = array();
                    $guardianlimit = 15;
                    $i=1;
                    $xmlReader = new XMLReader();
                    $xmlReader->open('http://api.guardianapis.com/content/search?q='.$politicsname.'&count='.$guardianlimit.'&api_key='.$guardianContentApiSecretkey.'');
                    while($xmlReader->read()) {
                        if($xmlReader->nodeType == XMLReader::ELEMENT) {
                            if($xmlReader->localName == 'content') {
                                $mpnews[$i]['apino'] = $xmlReader->getAttribute('id');
                            }
                            if($xmlReader->localName == 'content') {
                                $mpnews[$i]['weburl'] = $xmlReader->getAttribute('web-url');
                            }
                            if($xmlReader->localName == 'headline') {
                                // move to its textnode / child
                                $xmlReader->read(); 
                                $mpnews[$i]['title'] = $xmlReader->value;
                            }
                            if($xmlReader->localName == 'trail-text') {
                                // move to its textnode / child
                                $xmlReader->read(); 
                                $mpnews[$i]['SmallDesc'] = $xmlReader->value;
                                $i++;
                            }
                        }
                    }
                    
                    foreach ($mpnews as $mpnews[$i]) {
                        echo "<h3><a href='".$mpnews[$i]['weburl']."' >".$mpnews[$i]['title']."</a></h3>";
                        echo "<p>".$mpnews[$i]['SmallDesc']."</p>";
                    }
                    echo "<a href='http://browse.guardian.co.uk/search?search=".$politicsname."&sitesearch-radio=guardian&go-guardian=Search'>More...</a>";
                    ?>
                        
                    </div>
                </div>
        	</div>
		</div>
		<div class="yui-b">
        
		<div id="searchtips"><div class="tweet"><ul class="tweet_list">
		<?php // This is the Twitter component (requires Zend) ?>
		
		<?php		
        require_once("Zend/Loader.php");
        require_once("Zend/Service/Twitter.php");
        require_once("Zend/Service/Twitter/Search.php");
        
        $twitter_search = new Zend_Service_Twitter_Search('json');
        $limit = 7;
        
        $twit_results = $twitter_search->search($politicsname, array('lang' => 'en', 'rpp' => $limit));
        
        echo "<h3>Musings on ".$politicsname." (Twitter)</h3>";
        $i=0;
        while($i <= ($limit-1)) {
            echo "
            <li class='tweet_first tweet_odd'>
            <a href='http://www.twitter.com/".$twit_results["results"][$i]["from_user"]."' class='tweet_avatar' ><img src='".$twit_results["results"][$i]["profile_image_url"]."' width='48' height='48' border='0' /></a>
            <a href='http://twitter.com/".$twit_results["results"][$i]["from_user"]."/'>".$twit_results["results"][$i]["from_user"] . "</a>";
            
            $twit_results["results"][$i]["text"] = preg_replace( '/(http|ftp)+(s)?:(\/\/)((\w|\.)+)(\/)?(\S+)?/i', '<a href="\0">\4</a>', strip_tags($twit_results["results"][$i]["text"]) );
            
            echo " <span class='tweet_text'>".($twit_results["results"][$i]["text"]) . "</span>
            </li>";
            $i=$i+1;
        }
        echo "<a href='http://twitter.com/#search?q=".$politicsname."'>More...</a>";
        ?>
        </ul></div></div>
        </div>
	</div>

</div>


</body>
</html>
