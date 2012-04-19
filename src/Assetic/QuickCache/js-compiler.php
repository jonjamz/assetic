<?php

/* QUICKCACHE FOR ASSETIC BY JON JAMES (github.com/jonjamz) 
 * Globs files at user-given paths, checks a hash of their individual file hashes against the existing
 * CSS and JS files in the provided 'cache' directory
 * This 'cache' directory must have permissions suitable to being written in by PHP
 * I've left my commented out my experimental settings as an example
 * ============================================= */

require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'autoload.php';

use Assetic\Asset\AssetCollection;
use Assetic\Asset\GlobAsset;

/* After setting up the filters, tell it to use the ones you want */
//use Assetic\Filter\CoffeeScriptFilter;
//use Assetic\Filter\Yui\JsCompressorFilter as YuiJs;

/* Paths to the folders containing each of the file types you're using */
$globDirs = array(
   //'CoffeeScript' => dirname(__FILE__) . '/lib/coffee/*.coffee',
   //'js'           => dirname(__FILE__) . '/lib/js/*.js'
);

/* Calculate file hash for cache comparison */
foreach($globDirs as $key => $value) {
  $thisGlob  = glob($value);
  $fileHash = '';
  if(count($thisGlob) != 0) {
    foreach($thisGlob as $key => $value) {
      $fileHash .= hash_file('md5', $value);
    }
    $fileHash = md5($fileHash);
  }
}

/* Check local folder for existing cache file */
$localGlob = glob(dirname(__FILE__) . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "*.js");
$count = count($localGlob);

/* If multiple, throw error */
if($count > 1) { 
  echo "
  Error. 
  Please remove all files from 'cache' folder. 
  If you put files there manually, move them to lib folder instead.
  ";
  
/* If a file exists, compare name with hash */
}
elseif($count === 1 && "$fileHash.js" == basename($localGlob[0])) {

  header('Content-Type: application/js');
  include dirname(__FILE__) . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "$fileHash.js";

/* If no file, or mismatched hashes, write a new file to cache */
}
elseif($count === 0 || "$fileHash.js" != basename($localGlob[0])) {

  /* Clean old file */
	foreach ($localGlob as $key => $value) { unlink($value); }

  /* Create array for processing */
  $toFilter = array();
  
  /* Iterate through all provided asset file paths */
  foreach($globDirs as $key => $value) {
    if($key != 'js' && count(glob($value)) != 0) {
      //$toFilter[] = new GlobAsset($value, array(new CoffeeScriptFilter()));
    } elseif($key == 'js' && count(glob($value)) != 0) {
      $toFilter[] = new GlobAsset($value);
    }
  }

  // Process and output into a new file, then call that file
  $js = new AssetCollection($toFilter, array(
    new YuiJs('/usr/share/yui-compressor/yui-compressor.jar'),
  ));

  $content  = $js->dump();
	$name     = $fileHash.".js";

	$in       = dirname(__FILE__) . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . $name;
	$make     = fopen($in, 'x') or die("Can't open file!");
	$inject   = $content;
	fwrite($make, $inject);
	fclose($make);

	header('Content-Type: application/js');
	include $in;

}

?>
