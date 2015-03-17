<?php
//ini_set('display_errors', 'On');
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
define('DOWNLOADDIR', 'DownloadFiles');


?>
<!DOCTYPE html>

<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, height=device-height">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" href="//blueimp.github.io/Gallery/css/blueimp-gallery.min.css">
    <link rel="stylesheet" href="lib/slider/css/bootstrap-image-gallery.min.css">


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


    <style>
        .inactiveLink {
            pointer-events: none;
            cursor: default;
        }

    </style>
    <title>Home</title>
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
                <a href="logout.php" class="btn btn-default navbar-btn">Logout</a></div>
            <div class="col-md-1"></div>
        </div>
    </div>
</nav>

<?php

if (isset($_SESSION['token'])) {
    FacebookSession::setDefaultApplication($app_id, $app_secret);
    $sess = new FacebookSession($_SESSION['token']);

    try {
        $sess->validate($app_id, $app_secret);
    } catch (FacebookAuthorizationException $e) {
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

try {
//Getting profile info
    $request = new FacebookRequest($sess, 'GET', '/me');
    $response = $request->execute();
    $graph = $response->getGraphObject(GraphUser::className());
    $name = $graph->getName();
    $user_id = $graph->getId();
    $_SESSION['userid'] = $user_id;
    $user_bio = $graph->getProperty('bio');
    $user_bday = $graph->getProperty('birthday');
    $user_email = $graph->getProperty('email');

//Getting DP
    $graphObject = getFromFB('/me?fields=picture.width(800)');
    $profile_pic = $graphObject->getProperty('picture')->getProperty('url');

//getting album details post here
    $graphObject = getFromFB('/me/albums?fields=id,name,cover_photo');
    $data = $graphObject->getProperty('data');
    $arr = $data->asArray();
    foreach ($arr as $row) {
        // echo $row->id . "<br/>"; //prints id
        // echo $row->name . "<br/>"; //prints album name
        $coverget = "/" . $row->cover_photo; //gets cover pic id
        $graphObject = getFromFB($coverget); //gets graph object from that cover pic
        // echo $graphObject->getProperty('source'); // graph object to url of that cover pic
        //echo "</br></br>";
    }

} catch (Exception $e) {
    ;
}


?>

<div class="container" id="fullpagecontent">

<div class="row">
    <div class="col-md-1"></div>
    <div id="loadericon" class="col-md-10" align="center"><img style="height: 50px; width: 50px"
                                                               src="images/loading32x32.gif"></div>
    <div class="col-md-1"></div>
</div>

<div class="row" id="contentdata" hidden="true">
    <div id="blankonespace" class="col-xs-1"></div>
    <div id="leftside" class="col-xs-3" align="center"> <!-- left portion of the page -->
        <div class="row">
            <div class="col-xs-12">

                <div class="thumbnail"><img id="profilepic" src="<?php echo $profile_pic ?>" width="100%"></div>
                <div class="panel panel-default">
                    <div class="panel-heading">Download/Move Bulk Albums</div>
                    <div class="panel-body">

                        <button title="Download Selected Albums(ZIP)" class="btn btn-default downloadSelected"  name="downloadSelected" id="downloadSelected" style="width: 100%;">
                            <center>
                                <img src="images/downloadico.png" style="width: 15%;"> Download Selected Albums
                            </center>
                        </button>




                        <button title="Move Selected Albums" class="btn btn-default moveSelected"  name="moveSelected" id="moveSelected" style="width: 100%;">
                            <center>
                                <img src="images/gplus.png" style="width: 11%;"> Move Selected Albums
                            </center>
                        </button>




                        <button title="Download All Albums(ZIP)" class="btn btn-default downloadSelected" name="downloadAll" id="downloadAll" style="width: 100%;">
                            <center>
                                <img src="images/downloadico.png" style="width: 15%; "> Download All Albums
                            </center>
                        </button>


                        <button title="Move All Albums" class="btn btn-default moveSelected" name="moveAll" id="moveAll" style="width: 100%;">
                            <center>
                                <img src="images/gplus.png" style="width: 11%;"> Move All Albums
                            </center>
                        </button>



                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">About me</div>
                    <div class="panel-body">
                        <font color="#d3d3d3">Name</font><br><?php echo $name; ?><br/>

                        <?php
                        if ($user_bio != null) {
                            echo "<font color=\"#d3d3d3\">Bio</font><br>" . $user_bio;
                        }
                        ?>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Information</div>
                    <div class="panel-body">
                        <font color="#d3d3d3">Email</font><br><?php echo $user_email; ?><br/>
                        <font color="#d3d3d3">Birthday</font><br><?php echo $user_bday; ?><br/>
                        <font color="#d3d3d3">Gender</font><br><?php echo $graph->getProperty('gender'); ?><br/>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Google+/Picasa Connectivity</div>
                    <div class="panel-body" name="picasaConnectivity" id="picasaConnectivity">
                        <img src="images/loading32x32.gif">
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div id="rightside" class="col-xs-7"> <!-- right portion of the page -->
        <div class="panel panel-default">
            <div class="panel-heading">My Albums


            </div>
            <div class="panel-body">
                <div class="row">
                    <center>
                        <?php
                        foreach ($arr as $row) {
                            try {
                                $coverget = "/" . $row->cover_photo; //gets cover pic id
                                $graphObject = getFromFB($coverget); //gets graph object from that cover pic
                                echo "<div class=\"col-sm-6 col-md-4\">";
                                echo "<div class=\"thumbnail\">";

                                echo "<div id=\"thumbnaildiv{$row->id}\" name=\"{$row->id}\" class=\"albumthumbnail\" style=\" height: 200px; background-image: url({$graphObject->getProperty('source')}); background-size: cover; background-repeat: no-repeat; background-position: 50% 50%;\"></div>";


                                echo "<input type=\"checkbox\" class=\"selectCheckBox\" name=\"{$row->id}\"> Select<br/>";
                                echo "<b><label class=\"albumname\" id=\"name{$row->id}\">" . $row->name . "</label></b>" ?>
                                <br/>
                                <button title="Download Album(ZIP)" class="btn btn-default albumdownload"
                                        style="width: 35%;height: 25%"
                                        id="<?php echo $row->id ?>">
                                    <center>
                                        <img src="images/downloadico.png" style="width: 90%; height: 50% ">
                                    </center>
                                </button>
                                <button type="button" style="width:60%" class="btn btn-default movePicasa"
                                        id="<?php echo $row->id ?>">Move to
                                    <img src="images/gplus.png" height="20%" width="30%"></button>

                                <?php
                                echo "</div>";
                                echo "</div>";
                            } catch (Exception $e) {
                                ;
                            }
                        }
                        ?>
                    </center>
                </div>
            </div>
        </div>
    </div>
    <div id="rightblank" class="col-xs-3"></div>
</div>
<div class="row" id="secondrow" hidden="true">
    <div class="col-xs-1"></div>


</div>
<?php

?>
<script type="text/javascript">
$(document).ready(function () {

    $('#downloadSelected').toggle(false);
    $('#moveSelected').toggle(false);
    $('#downloadAll').toggle(true);
    $('#moveAll').toggle(true);


    var currentstate;
    var originaldata = document.getElementById('fullpagecontent').innerHTML;

    if (window.innerWidth < 1000) {
        currentstate = "mobile";
        $('#rightside').detach().appendTo('#secondrow');
        $('\<div id=\"addedone\" class=\"col-xs-1\"\>\</div\>').appendTo('#secondrow');
        document.getElementById('leftside').className = "col-xs-10";
        document.getElementById('rightblank').className = "col-xs-1";
        document.getElementById('rightside').className = "col-xs-10";

        document.getElementById('loadericon').innerHTML = "";
        document.getElementById('contentdata').hidden = false;
        document.getElementById('secondrow').hidden = false;


    } else {
        currentstate = "desktop";
        document.getElementById('loadericon').innerHTML = "";
        document.getElementById('contentdata').hidden = false;
    }

    window.onresize = function (event) {

        if (window.innerWidth < 1000) {
            if (currentstate == "desktop") {
                document.getElementById('contentdata').hidden = true;
                document.getElementById('secondrow').hidden = true;

                document.getElementById('loadericon').innerHTML = "\<img style=\"height: 50px; width: 50px\" src=\"images/loading32x32.gif\"\>";
                $('#rightside').detach().appendTo('#secondrow');
                $('\<div id=\"addedone\" class=\"col-xs-1\"\>\</div\>').appendTo('#secondrow');
                document.getElementById('leftside').className = "col-xs-10";
                document.getElementById('rightblank').className = "col-xs-1";
                document.getElementById('rightside').className = "col-xs-10";

                document.getElementById('loadericon').innerHTML = "";
                document.getElementById('contentdata').hidden = false;
                document.getElementById('secondrow').hidden = false;
                currentstate = "mobile";

            }

        }
        else {

            if (currentstate == "mobile") {
                document.getElementById('contentdata').hidden = true;
                document.getElementById('secondrow').hidden = true;

                document.getElementById('loadericon').innerHTML = "\<img style=\"height: 50px; width: 50px\" src=\"images/loading32x32.gif\"\>";
                $('#addedone').remove();
                $('#rightside').insertBefore('#rightblank');
                document.getElementById('leftside').className = "col-xs-3";
                document.getElementById('rightblank').className = "col-xs-3";
                document.getElementById('rightside').className = "col-xs-7";
                document.getElementById('loadericon').innerHTML = "";
                document.getElementById('contentdata').hidden = false;
                currentstate = "desktop";

            }
        }
    };

    var picasastatus = false;


    $('.albumname').click(function (e) {
        var nameid = $(this).attr('id');
        var id = nameid.substr(4, nameid.length);
        document.getElementById("thumbnaildiv" + id).click();
    });

    $('.movePicasa').click(function (e) {

        if (picasastatus) {
            var domelement = $(this).get(0);
            var originalelement = domelement.innerHTML;
            var originalthis = $(this);
            var ids = new Array();
            ids[0] = $(this).attr('id');
            domelement.innerHTML = "\<img style=\"height: 50%; width: 30%\" src=\"images/loading32x32.gif\"\> Moving...";
            $(this).prop('disabled', true);
            $.ajax({
                type: "POST",
                url: "http://rtcamp-thakkaraakash.rhcloud.com/picasaapi.php",
                data: {func: 'moveAlbumToPicasa', funcval: ids},
                dataType: "JSON",
                success: function (response, status, jqXHR) {
                    console.log(jqXHR.responseText);
                }
            }).done(function (data) {
                if (data == 1) {
                    $('#dialogue').dialog('close');
                    document.getElementById('dialogue').innerHTML = "Album Uploaded to Google+/Picasa.";
                    $('#dialogue').dialog('open');
                    originalthis.prop('disabled', false);
                    domelement.innerHTML = originalelement;
                }
                else {
                    $('#dialogue').dialog('close');
                    document.getElementById('dialogue').innerHTML = "Something went wrong! We are trying to fix it."
                    $('#dialogue').dialog('open');
                    originalthis.prop('disabled', false);
                    domelement.innerHTML = originalelement;

                }
            });
        }
        else {

            $('#dialogue').dialog('close');
            document.getElementById('dialogue').innerHTML = "Connect to Google+/Picasa first. " + document.getElementById('picasaConnectivity').innerHTML;
            $('#dialogue').dialog('open');
        }


    });


    $.ajax({
        type: "POST",
        url: "http://rtcamp-thakkaraakash.rhcloud.com/picasaapi.php",
        data: {func: 'getPicasaStatus'},
        dataType: "JSON",
        success: function (response, status, jqXHR) {
            console.log(jqXHR.responseText);
        }
    }).done(function (data) {
        if (data == 1) {
            document.getElementById('picasaConnectivity').innerHTML = "";
            document.getElementById('picasaConnectivity').innerHTML = "<h4 style=\"color:green\"> Connected </h4> ";
            picasastatus = true;
        }
        else {
            $.ajax({
                type: "POST",
                url: "http://rtcamp-thakkaraakash.rhcloud.com/picasaapi.php",
                data: {func: 'getPicasaUrl'},
                dataType: "TEXT",
                success: function (response, status, jqXHR) {
                    console.log(jqXHR.responseText);
                }
            }).done(function (data) {
                document.getElementById('picasaConnectivity').innerHTML = "";
                document.getElementById('picasaConnectivity').innerHTML = "<a href=" + data + ">Click to connect!</a>";
            });

        }
    });


    $('#dialogue').dialog({
        autoOpen: false,
        title: 'Status',
        buttons: [
            {
                text: "Hide",
                click: function () {
                    $(this).dialog("close");
                }
            }
        ]
    });

    $('.selectCheckBox').click(function (e) {
        var node = $(this).get(0);
        var parentnode = node.parentNode;
        if ($(this).is(':checked')) {

            parentnode.style.border = "1px solid #021a40";
            $('#downloadSelected').toggle(true);
            $('#moveSelected').toggle(true);
            $('#downloadAll').toggle(false);
            $('#moveAll').toggle(false);


        }
        else {
            parentnode.style.border = "";
            var k = false;
            $('.selectCheckBox').each(function (i, obj) {
                if ($(this).is(':checked')) {
                    k = true;
                    return;
                }
            });
            if (!k) {
                $('#downloadSelected').toggle(false);
                $('#moveSelected').toggle(false);
                $('#downloadAll').toggle(true);
                $('#moveAll').toggle(true);
            }
        }
    });
    $('.downloadSelected').click(function (e) {

        var albumids = new Array();
        var k = 0;

        if ($(this).prop('name') == 'downloadAll') {
            $('.selectCheckBox').each(function (i, obj) {
                albumids[k] = $(this).prop('name');
                k++;
            });
        }
        else {
            $('.selectCheckBox').each(function (i, obj) {
                if ($(this).is(':checked')) {
                    albumids[k] = $(this).prop('name');
                    k++;
                }
            });
        }

        if (k > 0) {
            $('#dialogue').dialog('close');
            document.getElementById('dialogue').innerHTML = "\<p><img src=\"images/loading32x32.gif\"\> Please wait, creating ZIP file!</p>"
            $('#dialogue').dialog('open');
            $.ajax({
                type: "POST",
                url: "http://rtcamp-thakkaraakash.rhcloud.com/fbapi.php",
                data: {functype: 'downloadMultipleAlbums', funcval: albumids},
                dataType: "JSON",
                success: function (response, status, jqXHR) {
                    console.log(jqXHR.responseText);
                }
            }).done(function (data) {
                if (data == 1) {
                    $('#dialogue').dialog('close');
                    document.getElementById('dialogue').innerHTML = "Here is your download <a href=\"<?php echo DOWNLOADDIR."/".$_SESSION['userid']?>.zip\">Click here to download!</a>"
                    $('#dialogue').dialog('open');

                }
                else if (data == 0 || data == null) {
                    $('#dialogue').dialog('close');
                    document.getElementById('dialogue').innerHTML = "Something went wrong! We are trying to fix it."
                    $('#dialogue').dialog('open');
                }
                else {
                    $('#dialogue').dialog('close');
                    document.getElementById('dialogue').innerHTML = "Here is your download <a href=\"<?php echo DOWNLOADDIR."/".$_SESSION['userid']?>" + data + ".zip\">Click here to download!</a>"
                    $('#dialogue').dialog('open');
                }

            });


        }
    });

    $('.moveSelected').click(function (e) {




        if (picasastatus) {
            var albumids = new Array();
            var k = 0;

            if ($(this).prop('name') == 'moveAll') {
                $('.selectCheckBox').each(function (i, obj) {
                    albumids[k] = $(this).prop('name');
                    k++;
                });
            } else {
                $('.selectCheckBox').each(function (i, obj) {
                    if ($(this).is(':checked')) {
                        albumids[k] = $(this).prop('name');
                        k++;
                    }
                });
            }
            if (k > 0) {

                $('#dialogue').dialog('close');
                document.getElementById('dialogue').innerHTML = "\<p><img src=\"images/loading32x32.gif\"\> Please wait, Uploading to Google+/Picasa.!</p>"
                $('#dialogue').dialog('open');

                $.ajax({
                    type: "POST",
                    url: "http://rtcamp-thakkaraakash.rhcloud.com/picasaapi.php",
                    data: {func: 'moveAlbumToPicasa', funcval: albumids},
                    dataType: "JSON",
                    success: function (response, status, jqXHR) {
                        console.log(jqXHR.responseText);
                    }
                }).done(function (data) {
                    if (data == 1) {
                        $('#dialogue').dialog('close');
                        document.getElementById('dialogue').innerHTML = "Album(s) Uploaded to Google+/Picasa.";
                        $('#dialogue').dialog('open');
                    }
                    else {
                        $('#dialogue').dialog('close');
                        document.getElementById('dialogue').innerHTML = "Something went wrong! We are trying to fix it."
                        $('#dialogue').dialog('open');
                    }
                });
            }
        }
        else {
            $('#dialogue').dialog('close');
            document.getElementById('dialogue').innerHTML = "Connect to Google+/Picasa first. " + document.getElementById('picasaConnectivity').innerHTML;
            $('#dialogue').dialog('open');
        }

    });


    $('.albumdownload').click(function (e) {

        document.getElementById('dialogue').innerHTML = "\<p><img src=\"images/loading32x32.gif\"\> Please wait, creating ZIP file!</p>";
        $('#dialogue').dialog('open');
        $.ajax({
            type: "POST",
            url: "http://rtcamp-thakkaraakash.rhcloud.com/fbapi.php",
            data: {functype: 'downloadImagesFromAlbum', funcval: $(this).attr('id')},
            dataType: "JSON",
            success: function (response, status, jqXHR) {
                console.log(jqXHR.responseText);
            }
        }).done(function (data) {
            if (data == 1) {
                $('#dialogue').dialog('close');
                document.getElementById('dialogue').innerHTML = "Here is your download <a href=\"<?php echo DOWNLOADDIR."/".$_SESSION['userid']?>.zip\">Click here to download!</a>"
                $('#dialogue').dialog('open');
            }
            else if (data == 0 || data == null) {
                $('#dialogue').dialog('close');
                document.getElementById('dialogue').innerHTML = "Something went wrong! We are trying to fix it."
                $('#dialogue').dialog('open');
            }
            else {
                $('#dialogue').dialog('close');
                document.getElementById('dialogue').innerHTML = "Here is your download <a href=\"<?php echo DOWNLOADDIR."/".$_SESSION['userid']?>" + data + ".zip\">Click here to download!</a>"
                $('#dialogue').dialog('open');
            }
        });
    });
    $('.albumthumbnail').click(function (e) {
        $('#dialogue').dialog('close');
        document.getElementById('dialogue').innerHTML = "\<p><img src=\"images/loading32x32.gif\"\> Opening Album....."
        $('#dialogue').dialog('open');

        var selectedalbumid = $(this).attr('name');
        $.ajax({
            type: "POST",
            url: "http://rtcamp-thakkaraakash.rhcloud.com/fbapi.php",
            data: {functype: 'getImagesAndNamesFromAlbumid', funcval: selectedalbumid},
            dataType: "JSON",
            success: function (response, status, jqXHR) {
                console.log(jqXHR.responseText);
            }
        }).done(function (data) {
            var i;
            document.getElementById('links').innerHTML = "";
            for (i = 0; i < data.length; i++) {
                if (data[i][1] == null) {
                    data[i][1] = "\"\"";
                }
                if (i == 0) {
                    document.getElementById('links').innerHTML += "<a id=\"check\" href=\"" + data[i][0] + "\"title=\"" + data[i][1] + "\" data-gallery\>";
                    document.getElementById('links').innerHTML += "<img src=\"" + data[i][0] + "\" alt=\"" + data[i][1] + "\"\> </a\>";

                } else {
                    document.getElementById('links').innerHTML += "<a href=\"" + data[i][0] + "\"title=\"" + data[i][1] + "\" data-gallery\>";
                    document.getElementById('links').innerHTML += "<img src=\"" + data[i][0] + "\" alt=\"" + data[i][1] + "\"\> </a\>";
                }
            }
            $('#dialogue').dialog('close');
            document.getElementById('check').click();

        });
    });

});
</script>

<div id="dialogue" title="test dialog" hidden="true">
    <p><img src="images/loading32x32.gif">

        Please wait, creating ZIP file!</p>
</div>


<!-- The Bootstrap Image Gallery lightbox, should be a child element of the document body -->
<div id="blueimp-gallery" class="blueimp-gallery">
    <!-- The container for the modal slides -->
    <div class="slides"></div>
    <!-- Controls for the borderless lightbox -->
    <h3 class="title"></h3>
    <a class="prev">‹</a>
    <a class="next">›</a>
    <a class="close">×</a>
    <a class="play-pause"></a>
    <ol class="indicator"></ol>
    <!-- The modal dialog, which will be used to wrap the lightbox content -->
    <div class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body next"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left prev">
                        <i class="glyphicon glyphicon-chevron-left"></i>
                        Previous
                    </button>
                    <button type="button" class="btn btn-primary next">
                        Next
                        <i class="glyphicon glyphicon-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="links" hidden="true">

</div>


<script src="http://blueimp.github.io/Gallery/js/jquery.blueimp-gallery.min.js"></script>
<script src="lib/slider/js/bootstrap-image-gallery.min.js"></script>
</body>
</html>