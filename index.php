<?php
/*error_reporting(E_ALL);
ini_set('display_errors', 'On');*/

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
    <meta name="viewport" content="width=device-width, initial-scale=1, height=device-height">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, maximum-scale=1, user-scalable=no">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">



    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>


    <script type="text/javascript">
        if (window.location.hash && window.location.hash == '#_=_') {
            window.location.hash = '';
            history.pushState('', document.title, window.location.pathname); // nice and clean
            e.preventDefault();
        }
    </script>
    <script
        type="text/javascript"
        src="lib/vegas/jquery.vegas.js">
    </script>

    <title>Login</title>


    <style type="text/css">
        .page-header {
            margin-top: 0;
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

<nav class="navbar navbar-inverse vcenter" role="navigation">
    <div class="container">
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-2" align="right">
                <a class="navbar-brand inactiveLink"><font color="white" face="Verdana" size="5">facebook<b>memories</b></font></a>
            </div>
            <div class="col-md-8" align="right">

            <div class="col-md-1"></div>
        </div>
    </div>
</nav>
<br><br>
<div class="container" align="center">

    <div class="col-md-2">
    </div>



    <div class="col-md-8">
        <div class="panel panel-default">
            <center>
            <div class="panel-body">
                
                

                <div class="panel panel-default">
                    <div class="panel-body">

                        <div class="page-header">
                            <div align="left"><font size="1" color="grey">Welcome to,</font></div>
                            <div><img src="images/memories.png" style="width:100%">

                            <br><font color="grey" face="Verdana" size="3">Where memories can be saved!</font></div>
                        </div>
                        <b>facebookMemories</b> is a small tool which lets you save your facebook memories for you to remember it offline. Downloading pictures from facebook can be tedious, and each and everyone has hundreds of pictures on facebook with no option to download all the pictures or move them to Picasa/Google+. Hence what facebookMemories provides is :<br><br>
                        
                        <b>Download All Facebook albums<br>
                        Download selected Facebook albums<br>
                        Transfer All Facebook albums to Google+/Picasa<br>
                        Transfer Selected Facebook albums to Google+/Picasa<br></b>
                        <br><br>Not a member yet? Sign in with facebook now!
                        <br><br>
                        <a href= <?php 
                        if (!isset($sess)) {
                            echo $helper->getLoginUrl($params);
                        }

                        ?>><input type="image" src="images/facebook.png" height="40px"/></a>
                        
                    </div>
                </div>


                <p>
                    
                </p>



                
            </div>
        </center>

        </div>
    </div>


    <div class="col-md-2">
    </div>
    
</div>


</body>
</html>
