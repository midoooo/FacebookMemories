<?php

require_once 'fbapi.php';
require_once 'lib/Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata_Photos');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
Zend_Loader::loadClass('Zend_Gdata_AuthSub');
//database credentials
define('DB_HOST', getenv('OPENSHIFT_MYSQL_DB_HOST'));
define('DB_PORT', getenv('OPENSHIFT_MYSQL_DB_PORT'));
define('DB_USER', getenv('OPENSHIFT_MYSQL_DB_USERNAME'));
define('DB_PASS', getenv('OPENSHIFT_MYSQL_DB_PASSWORD'));
define('DB_NAME', getenv('OPENSHIFT_APP_NAME'));
session_start();

if(isset($_GET['token']) && isset($_SESSION['userid'])){
    $userid=$_SESSION['userid'];
    $_SESSION['sessionToken'] = Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
    $sessiontoken=$_SESSION['sessionToken'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, "", DB_PORT) or die("Error: " . mysqli_error($conn));
    mysqli_select_db($conn, DB_NAME) or die("Error: " . mysqli_error($conn));
    if ($conn->connect_error) {

        header("Location: index.php");
    }
    else{
        $sql="SELECT * FROM user_details where user_id=".$userid;
        $result = $conn->query($sql);
        if ($result->num_rows > 0){
            $sql='DELETE FROM user_details where user_id='.$userid;
            if($conn->query($sql)==FALSE){
                header("Location: index.php");
            }
        }
        $sql="insert into user_details values(".$userid.",'".$sessiontoken."')";
        echo $sql;
        if ($conn->query($sql) == TRUE) {
            $conn->close();
            header("Location: home.php");
        } else {
            $conn->close();
            header("Location: index.php");
        }

    }


}
function moveAlbumToPicasa($albumid){
    $albumname=getAlbumNameFromId($albumid);
    $images=getImageAndNameListFromAlbumId($albumid);
   // echo $albumname;
    $client=getAuthSubHttpClient();
    $gp=new Zend_Gdata_Photos($client, "FacebookMemories");
    try{
        $entry = new Zend_Gdata_Photos_AlbumEntry();
        $entry->setTitle($gp->newTitle($albumname));
        $entry->setSummary($gp->newSummary($albumname));
        $createdEntry = $gp->insertAlbumEntry($entry);
        $picasaalbumid=$createdEntry->getGphotoId();
        for($i=0;$i<sizeof($images);$i++){
            uploadPicToPicasa($picasaalbumid,$images[$i][0],$images[$i][1]);
        }
        return $albumname;

    }catch (Exception $e){
        die($e->getMessage());
    }

}


function uploadPicToPicasa($picasaalbumid,$url,$photoCaption){

    $username = "default";
    $content = file_get_contents($url);
    file_put_contents("tempimages/".basename($url, ".jpg").PHP_EOL,$content);
    $filename="tempimages/".basename($url, ".jpg").PHP_EOL;
    if($photoCaption==null){
        $photoCaption="Uploaded from Facebook Memories!";
    }
    $photoName = $photoCaption;
    $albumId = $picasaalbumid;
    $client=getAuthSubHttpClient();
    $gp=new Zend_Gdata_Photos($client, "FacebookMemories");
    $fd = $gp->newMediaFileSource($filename);
    $fd->setContentType("image/jpeg");


    // Create a PhotoEntry
    $photoEntry = $gp->newPhotoEntry();

    $photoEntry->setMediaSource($fd);
    $photoEntry->setTitle($gp->newTitle($photoName));
    $photoEntry->setSummary($gp->newSummary($photoCaption));

    // We use the AlbumQuery class to generate the URL for the album
    $albumQuery = $gp->newAlbumQuery();

    $albumQuery->setUser($username);
    $albumQuery->setAlbumId($albumId);

// We insert the photo, and the server returns the entry representing
// that photo after it is uploaded
    $insertedEntry = $gp->insertPhotoEntry($photoEntry, $albumQuery->getQueryUrl());
    system("rm -rf ".escapeshellarg($filename));



}


if(isset($_POST['func'])){
    if($_POST['func']=='getPicasaStatus'){
        getSessionTokenFromDb(true);
    }
    elseif($_POST['func']=='getPicasaUrl'){
        echo getAuthSubUrl();
    }
    elseif($_POST['func']=='moveAlbumToPicasa' && isset($_POST['funcval'])){
        mkdir("tempimages");
        $albumids=$_POST['funcval'];
        foreach($albumids as $albumid){
            $albumname=moveAlbumToPicasa($albumid);


        }
        echo "1";
    }
}

function getAuthSubUrl()
{
    // the $next variable should represent the URL of the PHP script
    // an example implementation for getCurrentUrl is in the sample code

    $next = "http://rtcamp-thakkaraakash.rhcloud.com/picasaapi.php";
    $scope = 'https://picasaweb.google.com/data';
    $secure = false;
    $session = true;
    return Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure,
        $session);
}

function getSessionTokenFromDb($toprint){
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, "", DB_PORT) or die("Error: " . mysqli_error($conn));
    mysqli_select_db($conn, DB_NAME) or die("Error: " . mysqli_error($conn));
    if ($conn->connect_error) {
        //header("Location: index.php");
    }
    else{
        if(isset($_SESSION['userid'])){
            $sql="select session_token from user_details where user_id=".$_SESSION['userid'];
            $result = $conn->query($sql);
            if ($result->num_rows > 0){
                $rows=array();
                while($r=$result->fetch_assoc()){
                    $rows[]=$r;
                }
                $_SESSION['sessionToken']=$rows[0]['session_token'];
                if($toprint){
                    echo 1;
                }

            }
            else{
                if($toprint){
                    echo 0;
                }

            }
        }
    }
}

function getAuthSubHttpClient(){
    getSessionTokenFromDb(false);
    $client = Zend_Gdata_AuthSub::getHttpClient($_SESSION['sessionToken']);
    return $client;
}

?>