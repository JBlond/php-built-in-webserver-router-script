<?php
// Set timezone
date_default_timezone_set("UTC");

// Directory that contains error pages
define("ERRORS", dirname(__FILE__) . "/errors");

// Default index file
$DIRECTORY_INDEX = array(
	'index.php',
	'index.htm',
	'index.html'
);
// Optional array of authorized client IPs for a bit of security
$config["hostsAllowed"] = array();

function logAccess($status = 200) {
	file_put_contents("php://stdout", sprintf("[%s] %s:%s [%s]: %s\n",
		date("D M j H:i:s Y"), $_SERVER["REMOTE_ADDR"],
		$_SERVER["REMOTE_PORT"], $status, $_SERVER["REQUEST_URI"]));
}

// Parse allowed host list
if (!empty($config['hostsAllowed'])) {
	if (!in_array($_SERVER['REMOTE_ADDR'], $config['hostsAllowed'])) {
		logAccess(403);
		http_response_code(403);
		include ERRORS . '/403.php';
		exit;
	}
}

// if requesting a directory then serve the default index
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$ext = pathinfo($path, PATHINFO_EXTENSION);

if (empty($ext)) {
	foreach($DIRECTORY_INDEX as $index){
		$temp_path = rtrim($path, "/") . "/" . $index;
		// If the file index exists then return false and let the server handle it
		if (file_exists($_SERVER["DOCUMENT_ROOT"] . $temp_path)) {
			return false;
		}
	}
}

// If the file exists then return false and let the server handle it
if (file_exists($_SERVER["DOCUMENT_ROOT"] . $path) && !is_dir($_SERVER["DOCUMENT_ROOT"] . $path)) {
	return false;
}
elseif(!file_exists($_SERVER["DOCUMENT_ROOT"] . $path)){
	http_response_code(404);
	return false;
}

$this_dir = substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'],"/")+1);
$dir = $_SERVER['DOCUMENT_ROOT'].$this_dir;
if(!is_dir($dir)){
	http_response_code(404);
	return false;
}
$folder = opendir($dir);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD>
  <TITLE>Index of <?=$this_dir?></TITLE>
</HEAD>
<BODY>
<?php
if(file_exists('HEADER.html')){
	include 'HEADER.html';
}
?>
<H1>Index of <?=$this_dir?></H1>
<PRE><IMG SRC="/icons/blank.gif" ALT="	 "> Name                    Last modified       Size  Description
<HR>
<?php
$file_count=0;
if (readdir($folder)) {
	while ($file = readdir($folder)) {
		$file_count++;
		if ($this_dir=="/") $base=""; else $base=$this_dir;
		$i=0;
		$ispaces = 24-strlen($file);
		$spaces[0] = "";
		while ($i < $ispaces) {
			$spaces[0] .= " ";
			$i++;
		}
		$i=0;
		$ispaces = 6-strlen(filesize("$dir/$file"));
		$spaces[1] = "";
		while ($i < $ispaces) {
			$spaces[1] .= " "; $i++;
		}
		if ($file==".."){
			echo '<IMG SRC="/icons/folder.gif" ALT="[DIR]"> <A HREF="../">Parent Directory</A>        '.date("d-M-Y H:i", filemtime("$dir/$file")).'      -  '."\n";
		}
		elseif (substr($file,0,1)=="."){
			continue;
		}
		elseif (is_dir("$dir/$file")){
			echo '<IMG SRC="/icons/folder.gif" ALT="[DIR]"> <A HREF="'. urlencode($file).'/">'.$file.'</A>'.$spaces[0].''.date("d-M-Y H:i", filemtime("$dir/$file")).'      -  '."\n";
		}
		elseif (substr($file,-4,4) == ".htm" || substr($file,-5,4) == ".html" || substr($file,-6,4) == ".shtml" || substr($file,-4,4) == ".asp" || substr($file,-4,4) == ".txt"){
			echo '<IMG SRC="/icons/text.gif" ALT="[TXT]"> <A HREF="'.urlencode($file).'">'.$file.'</A>'.$spaces[0].''.date("d-M-Y H:i", filemtime("$dir/$file")).''.$spaces[1].''.filesize("$dir/$file").'k  '."\n";
		}
		elseif (substr($file,-4,4) == ".gif" || substr($file,-4,4) == ".jpg" || substr($file,-4,4) == ".png" || substr($file,-4,4) == ".jpeg"){
			echo '<IMG SRC="/icons/image2.gif" ALT="[IMG]"> <A HREF="'.urlencode($file).'">'.$file.'</A>'.$spaces[0].''.date("d-M-Y H:i", filemtime("$dir/$file")).''.$spaces[1].''.filesize("$dir/$file").'k  '."\n";
		}
		else
		{
			echo '<IMG SRC="/icons/unknown.gif" ALT="[IMG]"> <A HREF="'.urlencode($file).'">'.$file.'</A>'.$spaces[0].''.date("d-M-Y H:i", filemtime("$dir/$file")).''.$spaces[1].''.filesize("$dir/$file").'k  '."\n";
		}
	}
	if ($file_count==0){
		echo '<IMG SRC="/icons/folder.gif" ALT="[DIR]"> <A HREF="../">Parent Directory</A>        '.date("d-M-Y H:i", filemtime("$dir/$file")).'      -  '."\n";
	}
}
else
{
	echo "<br>Error 404. Not found.";
}
?>
</PRE><HR>
<?php
if (file_exists("$dir/readme.txt")){
	echo "<pre>"; include "$dir/readme.txt"; echo "</pre>";
}
else
{
	echo "<ADDRESS>" . $_SERVER['SERVER_SOFTWARE'] . " at " . $_SERVER['SERVER_NAME'] . " Port " . $_SERVER['SERVER_PORT'] . "</ADDRESS>";
}
?>
</BODY></HTML>