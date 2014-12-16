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
<!DOCTYPE html>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>

<meta name="viewport" content="width=device-width, initial-scale=1, height=device-height">
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, maximum-scale=1, user-scalable=no">

<html>
<head>
    <script type="text/javascript">
        if (window.location.hash && window.location.hash == '#_=_') {
            window.location.hash = '';
            history.pushState('', document.title, window.location.pathname); // nice and clean
            e.preventDefault();
        }
    </script>
    <style>
        .inactiveLink {
            pointer-events: none;
            cursor: default;
        }
    </style>
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
                    <button type="button" class="btn btn-default navbar-btn">Logout</button></div>
                <div class="col-md-1"></div>
            </div>
        </div>
    </nav>


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

//Getting profile info
$request = new FacebookRequest($sess, 'GET', '/me');
$response = $request->execute();
$graph = $response->getGraphObject(GraphUser::className());
$name= $graph->getName();
$user_id=$graph->getId();
$user_bio=$graph->getProperty('bio');
$user_bday=$graph->getProperty('birthday');
$user_email=$graph->getProperty('email');

//Getting DP
$graphObject=getFromFB('/me?fields=picture.width(800)');
$profile_pic=$graphObject->getProperty('picture')->getProperty('url');

//getting album details post here
$graphObject=getFromFB('/me/albums?fields=id,name,cover_photo');
$data=$graphObject->getProperty('data');
$arr=$data->asArray();
//echo "<br/>";
foreach ($arr as $row) {
   // echo $row->id . "<br/>"; //prints id
   // echo $row->name . "<br/>"; //prints album name
    $coverget="/".$row->cover_photo; //gets cover pic id
    $graphObject=getFromFB($coverget); //gets graph object from that cover pic
   // echo $graphObject->getProperty('source'); // graph object to url of that cover pic
    //echo "</br></br>";
}

?>

    <div class="container">
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-3" align="right"></div>
            <div class="col-md-7"></div>
            <div class="col-md-1"></div>
        </div>
        <div class="row">
            <div class="col-xs-1"></div>
            <div class="col-xs-3"> <!-- left portion of the page -->

                <div class="row">
                    <div class="col-xs-12">
                        <div class="panel panel-default">

                             <img src="<?php echo $profile_pic?>" width="100%">
                            <div class="panel panel-default">
                                <div class="panel-heading">About me:</div>
                                <div class="panel-body">
                                    <font color="#d3d3d3">Name:</font><br><?php echo $name;?><br/>
                                    <font color="#d3d3d3">Bio:</font><br>

                                    <?php echo $user_bio;?>


                                </div>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">Information:</div>
                                <div class="panel-body">
                                    <font color="#d3d3d3">Email:</font><br><?php echo $user_email;?><br/>
                                    <font color="#d3d3d3">Birthday:</font><br><?php echo $user_bday;?><br/>
                                    <font color="#d3d3d3">Gender:</font><br><?php echo $graph->getProperty('gender');?><br/>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
            <div class="col-xs-7" align="left"> <!-- right portion of the page -->

                <div class="panel panel-default">
                    <div class="panel-heading">My Albums</div>

                    <div class="panel-body">

                        <div class="row">
<?php
foreach ($arr as $row) {
    $coverget="/".$row->cover_photo; //gets cover pic id
    $graphObject=getFromFB($coverget); //gets graph object from that cover pic
    echo "<div class=\"col-sm-6 col-md-4\">";
    echo "<div class=\"thumbnail\">";
    echo "<img data-src=\"holder.js/300x300\" alt=\"100%x200\" src=\"{$graphObject->getProperty('source')}\" data-holder-rendered=\"true\" style=\"height: 200px; width: 100%; display: block;\">";
    echo $row->name;
    echo "</div>";
    echo "</div>";


    // echo $row->id . "<br/>"; //prints id
    // echo $row->name . "<br/>"; //prints album name
    // echo $graphObject->getProperty('source'); // graph object to url of that cover pic
    //echo "</br></br>";
}





?>



                        </div>


                    </div>
                </div>

                </div>
            <div class="col-xs-1"></div>
        </div>
    </div>



</body>
</html>