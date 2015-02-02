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


if(isset($_POST['functype']) && isset($_POST['funcval'])){
    if($_POST['functype']=='getImagesFromAlbum'){

        $reqstring="/".$_POST['funcval']."/photos";

        $graphObject = getFromFB($reqstring);
        $data=$graphObject->getProperty('data');
        $arr=$data->asArray();

        $outputarray=array();
        $initial=0;
        foreach($arr as $row){
            $outputarray[$initial][0]=$row->images[0]->source;
            $outputarray[$initial][1]= $row->name;
            $initial++;
        }
        //var_dump($graphObject);
        echo json_encode($outputarray);
    }

}
else{
    header("Location: index.php");
    exit();
}


?>