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
//use Assetic\Filter\Sass\SassFilter;
//use Assetic\Filter\Sass\ScssFilter;
//use Assetic\Filter\StylusFilter;
//use Assetic\Filter\LessFilter;
//use Assetic\Filter\Yui\CssCompressorFilter as YuiCss;

/* Paths to the folders containing each of the file types you're using */
$globDirs = array(
   //'Sass'   => dirname(__FILE__) . '/lib/sass/*.sass',
   //'Scss'   => dirname(__FILE__) . '/lib/sass/*.scss',
   //'Stylus' => dirname(__FILE__) . '/lib/stylus/*.styl',
   //'Less'   => dirname(__FILE__) . '/lib/less/*.less',
   //'css'    => dirname(__FILE__) . '/lib/css/*.css'
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
$localGlob = glob(dirname(__FILE__) . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "*.css");
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
elseif($count === 1 && "$fileHash.css" == basename($localGlob[0])) {

  header('Content-Type: text/css');
  include dirname(__FILE__) . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "$fileHash.css";

/* If no file, or mismatched hashes, write a new file to cache */
}
elseif($count === 0 || "$fileHash.css" != basename($localGlob[0])) {

  /* Clean old file */
	foreach ($localGlob as $key => $value) { unlink($value); }

  /* Create array for processing */
  $toFilter = array();
  
  /* Iterate through all provided asset file paths */
  foreach($globDirs as $key => $value) {

    if($key != 'css' && count(glob($value)) != 0) {
      //if($key == 'Sass') { $toFilter[] = new GlobAsset($value, array(new SassFilter())); }
      //elseif($key == "Scss") { $toFilter[] = new GlobAsset($value, array(new ScssFilter())); }
      //elseif($key == "Stylus") { $toFilter[] = new GlobAsset($value, array(new StylusFilter())); }
      //elseif($key == "Less") { $toFilter[] = new GlobAsset($value, array(new LessFilter())); }
    }
    elseif($key == 'css' && count(glob($value)) != 0) {
      $toFilter[] = new GlobAsset($value);
    }
  }

  // Process and output into a new file, then call that file
  $css = new AssetCollection($toFilter, array(
    new YuiCss('/usr/share/yui-compressor/yui-compressor.jar'),
  ));

  $content  = $css->dump();
	$name		  = $fileHash.".css";

	$in			  = dirname(__FILE__) . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . $name;
	$make		  = fopen($in, 'x') or die("Can't open file!");
	$inject	  = $content;
	fwrite($make, $inject);
	fclose($make);

	header('Content-Type: text/css');
	include $in;

}

?>
