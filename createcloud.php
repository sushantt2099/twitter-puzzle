<?php
//get all the values
$handler = $_GET['handler'];
$sample_size = $_GET['sample_size'];
$retweet = $_GET['retweet'];
$exclude_replies = $_GET['exclude_replies'];
if($retweet == 'true'){
	$retweet_checked = 'checked';
}
if($exclude_replies == 'true'){
	$exclude_replies_checked = 'checked';
}
echo "<!DOCTYPE html>\n";
echo "<head>\n";
echo '<script src="/script/cloud.js" type="text/javascript"></script>';
echo "\n</head>\n";
echo "<body>\n";
echo '<form>
		Try New Twitter handle: @
		<input type="text" value="" id = "handler">
		<input id="submit_button"  type="button" value="Hit it!" onClick="genrateResult(this.form)">
		<br><br>
		<b>OR</b>
		<br><br>
		Customize result for @<input type="text" value="'.$handler.'" id = "oldhandler">
		<br>
		Sample Size:
		<input type="text" value="'.$sample_size.'" id = "sample_size"><br>
		<input type="checkbox" id="retweet" '.$retweet_checked.'>Include retweet<br>
		<input type="checkbox" id="exclude_replies" '.$exclude_replies_checked.'>Exclude replies<br> 
		<input id="submit_button"  type="button" value="apply filter" onClick="customizeResult(this.form)">
</form>';


/*
	*this fuction return the number of word in the given $string(passed as argument)
	*A word is defined as sequence of character that does not contain blank, new line, or tab
*/
function count_word($string){
	$word_count = 0;
	$number_of_char = strlen($string);
	$state = 0;
	for($i = 0; $i < $number_of_char; $i++){
		$char = substr($string, $i, 1);
		$cmp = strcmp($char, "\n");
		if($cmp==0){//if there is new line
			$state = 0;
		}else{
		$cmp = strcmp($char, " ");
			if($cmp==0){// if there is a blank
				$state = 0;
			}else{
			$cmp = strcmp($char, "\t");
			if($cmp==0){// if there is tab
				$state = 0;
			}else if($state == 0){//or else
			$state = 1;
			$word_count = $word_count + 1;
			}
			}
		}
		
	}
	return $word_count;
}

$tweet_word_meter = array();//it stores the frequency of tweets with certain number of word maped as[number of word => number of tweets]
$id = 0;  //it stores the id of the last tweet returned by after querying. 

/*
	*genrate_number($json) it decodes the json object and counts the number of word in each tweet and stores it to $tweet_word_meter
*/
function genrate_numbers($json){
	global $tweet_word_meter;
	$decode = json_decode($json, true);
	$count = count($decode);
	for($i=0;$i<$count;$i++){
		$text = $decode[$i][text];
		$number_of_word = count_word($text);
		if(array_key_exists($number_of_word, $tweet_word_meter)){
			$tweet_word_meter[$number_of_word] = $tweet_word_meter[$number_of_word] + 1;
		}else{
				$tweet_word_meter[$number_of_word] = 1;
			}
	}
}
/*
 * calculate_id_of_last_tweet($json) return the id of last tweet 
*/
function calculate_id_of_last_tweet($json){
		$decode = json_decode($json, true);
		$count = count($decode);
		return  $decode[$count-1][id];
}

/*
	* Fot the given number of $sample_size calulate() recurcively calls itself to calculate the final result it also take care of the addtional parameter(exclude_replies, include_rts)

*/
function calculate(){
	$json;
	global $handler, $retweet, $exclude_replies, $sample_size;
	if($sample_size==0){
		return;
	}
	$sample_size = $sample_size - 200;
	if($sample_size>=0){
		$temp_size = 200;
	}else{
		$temp_size = $sample_size + 200;
		$sample_size = 0;
	}
	
	if($id==0){
		$json = file_get_contents("https://api.twitter.com/1/statuses/user_timeline.json?trim_user=true&screen_name=".$handler."&exclude_replies=".								$exclude_replies."&include_rts=".$retweet."&count=".$temp_size, true); 
	}else{
		$json = file_get_contents("https://api.twitter.com/1/statuses/user_timeline.json?trim_user=true&screen_name=".$handler."&exclude_replies=".								$exclude_replies."&include_rts=".$retweet."&count=".$temp_size."&max_id=".$id, true); 
	}
	$id = calculate_id_of_last_tweet($json);
	if($id==0){
				echo '<p style="color:red">Twitter is returning error, please provide correct handler</p>';
				exit;
	}
	genrate_numbers($json);
	calculate();
	
}
calculate();
arsort($tweet_word_meter);//sorts the result in decending order.
echo "<ul>\n";
foreach ($tweet_word_meter as $key => $value){
		echo "<li value = ".$value.">".$key."</li>\n";
	}
	echo "</ul>\n";
echo "</body>\n";
echo "</html>";
?>