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

/*PROCESS*/
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
    <script
        type="text/javascript"
        src="lib/vegas/jquery.vegas.js">
    </script>
    <link rel="stylesheet" type="text/css" href="lib/vegas/jquery.vegas.min.css">
    <script type="text/javascript" src="lib/vegas/jquery.vegas.min.js"></script>
    <style>
        a, a:hover, a:active, a:visited { color: white; }
    </style>
    <script>
        $.vegas('slideshow', {
            backgrounds:[
                { src:'images/5.png', fade:5000, valign:'5%' },
                { src:'images/1.jpg', fade:5000 },
                { src:'images/2.png', fade:5000, valign:'5%' },
                { src:'images/3.jpg', fade:5000, valign:'5%' },
                { src:'images/4.png', fade:5000, valign:'5%' },
                { src:'images/6.png', fade:2000 }
            ]
        })('overlay', {
            src:'/vegas/overlays/11.png'
        });
    </script>
</head>
<body>
<?php

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
<div class="row">
    <div class="col-md-3 col-md-offset-3">
    </div>
    <div class="col-md-3 col-md-offset-8"><font color="white" face="Verdana" size="5"><center>facebook<b>memories</b></center></font></div>
</div>
<div class="row">
    <div class="col-md-3 col-md-offset-3">
    </div>
    <div class="col-md-3 col-md-offset-8">
        <?php
        if (!isset($sess)) {
            echo '<center><a href='.$helper->getLoginUrl($params).'><U><I>Login with facebook</I></U></a></center></center>';
        }
        ?>
    </div>
</body>
</html>