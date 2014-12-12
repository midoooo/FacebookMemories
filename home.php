<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
/* INCLUSION OF LIBRARY FILEs*/
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

?>
<html>

<body>
<pre>
<?php

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
/** Generic method to call in get method from Graph API **/
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

$request = new FacebookRequest($sess, 'GET', '/me');
$response = $request->execute();
$graph = $response->getGraphObject(GraphUser::className());
$name= $graph->getName();
echo "hi {$name} <br/><br/><br/>";
$graphObject=getFromFB('/me?fields=cover,picture.width(800)');
//var_dump($graphObject);
$cover_pic=$graphObject->getProperty('cover')->getProperty('source');
$profile_pic=$graphObject->getProperty('picture')->getProperty('url');
echo $cover_pic."<br/>";
echo $profile_pic."<br/>";

//getting album details post here
$graphObject=getFromFB('/me/albums?fields=id,name,cover_photo');
$data=$graphObject->getProperty('data');
$arr=$data->asArray();
echo "<br/>";
foreach ($arr as $row) {
    echo $row->id . "<br/>";
    echo $row->name . "<br/>";
    $coverget="/".$row->cover_photo;
    $graphObject=getFromFB($coverget);
    echo $graphObject->getProperty('source');
    echo "</br></br>";
}

?>
    </pre>
</body>
</html>