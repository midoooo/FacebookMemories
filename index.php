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

//1.Stat Session
session_start();
session_regenerate_id(true);
$params = array(
    'scope' => 'email','public_profile','user_photos','user_about_me','user_birthday'
);
$redirect_url='http://rtcamp-thakkaraakash.rhcloud.com/';
FacebookSession::setDefaultApplication($app_id, $app_secret);
$helper = new FacebookRedirectLoginHelper($redirect_url);
try{
    $sess = $helper->getSessionFromRedirect();
}catch(Exception $e){
}
if (isset($_SESSION['token'])) {
    $sess=new FacebookSession($_SESSION['token']);
    try {
        $sess->validate($app_id, $app_secret);
    } catch(FacebookAuthorizationException $e){
        echo "Unauthorized";
    }
}
if (isset($sess)) {
    $_SESSION['token']=$sess->getAccessToken();
    header("Location: home.php");
    exit();
}

?>

<html>
<head>
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script
        type="text/javascript"
        src="lib/vegas/jquery.vegas.js">
    </script>
    <link rel="stylesheet" type="text/css" href="lib/vegas/jquery.vegas.min.css">
    <script type="text/javascript" src="lib/vegas/jquery.vegas.min.js"></script>
    <style>
        a, a:hover, a:active, a:visited { color: white; }
        .vertical-center {
            left: 0;
            line-height: 200px;
            margin: auto;
            margin-top: -100px;
            position: absolute;
            top: 48%;
            width: 100%;
        }
        .vertical-center-below{
            left: 0;
            line-height: 200px;
            margin: auto;
            margin-top: -100px;
            position: absolute;
            top: 53%;
            width: 100%;
        }

    </style>
    <script>
        $.vegas('slideshow', {
            backgrounds:[
                { src:'images/5.png', fade:5000, valign:'5%' },
                { src:'images/1.jpg', fade:5000 },
                { src:'images/2.png', fade:5000, valign:'5%' },
                { src:'images/3.png', fade:5000, valign:'5%' },
                { src:'images/4.png', fade:5000, valign:'5%' },
                { src:'images/6.png', fade:2000 }
            ]
        })('overlay', {
            src:'/vegas/overlays/11.png'
        });
    </script>
</head>
<body>

<div class="row vertical-center">
    <font color="white" face="Verdana" size="10"><center>facebook<b>memories</b></center></font>
</div>

<div class="row vertical-center-below">
    <?php
    if (!isset($sess)) {
        echo '<center><a href='.$helper->getLoginUrl($params).'><U><I>Login with facebook</I></U></a></center></center>';
    }
    ?>
</div>
</body>
</html>