<?php
/**
 * Created by PhpStorm.
 * User: aakashthakkar
 * Date: 02/02/15
 * Time: 10:17 AM
 */
require_once 'fbcredentials.php';
require_once 'lib/Facebook/FacebookSession.php';
require_once 'lib/Facebook/FacebookRequest.php';
require_once 'lib/Facebook/FacebookResponse.php';
require_once 'lib/Facebook/FacebookSDKException.php';
require_once 'lib/Facebook/FacebookRequestException.php';
require_once 'lib/Facebook/FacebookRedirectLoginHelper.php';
require_once 'lib/Facebook/FacebookAuthorizationException.php';
require_once 'lib/Facebook/GraphObject.php';
require_once 'lib/Facebook/GraphUser.php';
require_once 'lib/Facebook/GraphSessionInfo.php';
require_once 'lib/Facebook/Entities/AccessToken.php';
require_once 'lib/Facebook/HttpClients/FacebookCurl.php';
require_once 'lib/Facebook/HttpClients/FacebookHttpable.php';
require_once 'lib/Facebook/HttpClients/FacebookCurlHttpClient.php';

/* USE NAMESPACES */

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\GraphUser;
use Facebook\GraphSessionInfo;
use Facebook\FacebookHttpable;
use Facebook\FacebookCurlHttpClient;
use Facebook\FacebookCurl;
session_start();
if ( isset($_SESSION['token']) ) {
    FacebookSession::setDefaultApplication($app_id, $app_secret);
    $sess=new FacebookSession($_SESSION['token']);

    try{
        $sess->validate($app_id, $app_secret);

    }catch(FacebookAuthorizationException $e){
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

function getFromFB($args)
{
    global $sess;
    $request = new FacebookRequest(
        $sess,
        'GET',
        $args
    );
    $response = $request->execute();
    $graphObject = $response->getGraphObject();
    return $graphObject;
}



function getImageListFromAlbumId($albumid){
    $reqstring="/".$albumid."/photos";
    $graphObject = getFromFB($reqstring);
    $data=$graphObject->getProperty('data');
    $arr=$data->asArray();

    $outputarray=array();
    $initial=0;
    foreach( $arr as $row ) {
        //$outputarray[$initial][0]=$row->images[0]->source;
        //$outputarray[$initial][1]= $row->name;
        $outputarray[$initial]=$row->images[0]->source;
        $initial++;
    }
    return $outputarray;
}
function createDirFromImages($albumid){
    $imagelist=getImageListFromAlbumId($albumid);
    mkdir("DownloadFiles");
    rmdir("DownloadFiles/".$albumid);
    mkdir("DownloadFiles/".$albumid);
    $i=0;
    foreach($imagelist as $img){
        $file = basename($img, ".jpg");
        copy($img,"DownloadFiles/".$albumid."/".$albumid.$i.".jpg");
        $i++;
    }


    return $imagelist;

}

function Zip($source, $destination, $include_dir = false)
{
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }
    if (file_exists($destination)) {
        unlink ($destination);
    }
    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }
    $source = str_replace('\\', '/', realpath($source));
    if (is_dir($source) === true) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
        if ($include_dir) {
            $arr = explode("/",$source);
            $maindir = $arr[count($arr)- 1];
            $source = "";
            for ($i=0; $i < count($arr) - 1; $i++) {
                $source .= '/' . $arr[$i];
            }
            $source = substr($source, 1);
            $zip->addEmptyDir($maindir);
        }
        foreach ($files as $file) {
            $file = str_replace('\\', '/', $file);
            if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                continue;
            $file = realpath($file);
            if (is_dir($file) === true) {
                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            }
            else if (is_file($file) === true) {
                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
        }
    }
    else if (is_file($source) === true) {
        $zip->addFromString(basename($source), file_get_contents($source));
    }
    return $zip->close();
}



function createZipFromDirs($dirs){

    if(file_exists("DownloadFiles/".$_SESSION['userid'].".zip")){
        $counter=2;
        while(file_exists("DownloadFiles/".$_SESSION['userid'].$counter.".zip")){
            $counter++;
        }
        Zip("DownloadFiles/".$dirs,"DownloadFiles/".$_SESSION['userid'].$counter.".zip",true);
        if(file_exists("DownloadFiles/".$_SESSION['userid'].$counter.".zip")){
            return $counter;
        }
        else{
            return 0;
        }



    }
    else{
        Zip("DownloadFiles/".$dirs,"DownloadFiles/".$_SESSION['userid'].".zip",true);
        return file_exists("DownloadFiles/".$_SESSION['userid'].".zip");
    }

}


if(isset($_POST['functype']) && isset($_POST['funcval'])){
    if($_POST['functype']=='downloadImagesFromAlbum'){

        $outputarray=createDirFromImages($_POST['funcval']);
        $isCreated=createZipFromDirs($_POST['funcval']);
        //var_dump($graphObject);
        echo $isCreated;


    }

}
else{
    header("Location: index.php");
    exit();
}


?>