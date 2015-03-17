<?php
/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/
require_once('fbapi.php');
require_once('lib/Zend/Loader.php');
Zend_Loader::loadClass('Zend_Gdata_Photos');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
Zend_Loader::loadClass('Zend_Gdata_AuthSub');
//database credentials

define('DB_HOST', getenv('OPENSHIFT_MYSQL_DB_HOST'));
define('DB_PORT', getenv('OPENSHIFT_MYSQL_DB_PORT'));
define('DB_USER', getenv('OPENSHIFT_MYSQL_DB_USERNAME'));
define('DB_PASS', getenv('OPENSHIFT_MYSQL_DB_PASSWORD'));
define('DB_NAME', getenv('OPENSHIFT_APP_NAME'));
//echo DB_HOST." ".DB_PORT. " ". DB_USER. " ". DB_PASS;

/*define('DB_HOST', '127.8.159.130');
define('DB_PORT', '3306');
define('DB_USER', 'adminSDZFAbJ');
define('DB_PASS', 'geDmRAQB6YhC');
define('DB_NAME', 'rtcamp');*/

//echo DB_HOST." ".DB_PORT. " ". DB_USER. " ". DB_PASS;


if (isset($_GET['token']) && isset($_SESSION['userid'])) {
    $userid = $_SESSION['userid'];
    $_SESSION['sessionToken'] = Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
    $sessiontoken = $_SESSION['sessionToken'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, "", DB_PORT) or die("Error: " . mysqli_error($conn));
    mysqli_select_db($conn, DB_NAME) or die("Error: " . mysqli_error($conn));
    if ($conn->connect_error) {

        //header("Location: index.php");
    } else {
        $sql = "SELECT * FROM user_details where user_id=" . $userid;
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $sql = 'DELETE FROM user_details where user_id=' . $userid;
            if ($conn->query($sql) == false) {
                header("Location: index.php");
            }
        }
        $sql = "insert into user_details values(" . $userid . ",'" . $sessiontoken . "')";
        //echo $sql;
        if ($conn->query($sql) == true) {
            $conn->close();
            header("Location: home.php");
        } else {
            $conn->close();
            header("Location: index.php");
        }
    }


}

/**
 * Class Picasaapi
 * @author Aakash Thakkar <thakkaraakash@hotmail.com>
 */
class Picasaapi
{
    /**
     * This function takes facebook album id in input, gets all the images from fbapi and uploads each pic in picasa
     * @param $albumid facebook album id
     * @return string returns album name
     */
    function moveAlbumToPicasa($albumid)
    {
        try {
            $fbapi = new Fbapiclass();
            $albumname = $fbapi->getAlbumNameFromId($albumid);
            if ($albumname == 'Invalid albumid') {
                throw new Exception("Invalid albumid");
            }
            $images = $fbapi->getImageAndNameListFromAlbumId($albumid);
            if ($images == 'Invalid albumid') {
                throw new Exception("Invalid albumid");
            }
            $client = $this->getAuthSubHttpClient();
            $gp = new Zend_Gdata_Photos($client, "FacebookMemories");

            $entry = new Zend_Gdata_Photos_AlbumEntry();
            $entry->setTitle($gp->newTitle($albumname));
            $entry->setSummary($gp->newSummary($albumname));
            $createdEntry = $gp->insertAlbumEntry($entry);
            $picasaalbumid = $createdEntry->getGphotoId();
            for ($i = 0; $i < sizeof($images); $i++) {
                try {
                    $this->uploadPicToPicasa($picasaalbumid, $images[$i][0], $images[$i][1]);
                } catch (Exception $e) {
                    ;
                }

            }
            return $albumname;

        } catch (Exception $e) {
            return "Invalid albumid";
        }
    }


    /**
     * This function takes a single image and uploads to picasa album.
     * @param $picasaalbumid - the picasa album id in which the picture has to be uploaded
     * @param $url - the location of the image
     * @param $photoCaption - caption of the image
     */
    function uploadPicToPicasa($picasaalbumid, $url, $photoCaption)
    {
        try {
            $username = "default";
            $content = file_get_contents($url);
            file_put_contents("tempimages/" . basename($url, ".jpg") . PHP_EOL, $content);
            $filename = "tempimages/" . basename($url, ".jpg") . PHP_EOL;
            if ($photoCaption == null) {
                $photoCaption = "Uploaded from Facebook Memories!";
            }
            $photoName = $photoCaption;
            $albumId = $picasaalbumid;
            $client = $this->getAuthSubHttpClient();
            $gp = new Zend_Gdata_Photos($client, "FacebookMemories");
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
            system("rm -rf " . escapeshellarg($filename));
        } catch (Exception $e) {
            echo "Invalid Request";
        }


    }


    /**
     * This function is part of picasaapi, what it does is gets authorization url to redirect while authorization
     * @return string
     */
    function getAuthSubUrl()
    {
        // the $next variable should represent the URL of the PHP script
        // an example implementation for getCurrentUrl is in the sample code

        $next = "http://rtcamp-thakkaraakash.rhcloud.com/picasaapi.php";
        $scope = 'https://picasaweb.google.com/data';
        $secure = false;
        $session = true;
        return Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure, $session);
    }

    /**
     * This function gets token from the database and saves it in a session variable.
     * @param $toprint
     */
    function getSessionTokenFromDb($toprint)
    {
        try {
            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, "", DB_PORT) or die("Error: " . mysqli_error($conn));
            mysqli_select_db($conn, DB_NAME) or die("Error: " . mysqli_error($conn));
            if ($conn->connect_error) {
                //header("Location: index.php");
            } else {


                if (isset($_SESSION['userid'])) {
                    $sql = "select session_token from user_details where user_id=" . $_SESSION['userid'];
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        $rows = array();
                        while ($r = $result->fetch_assoc()) {
                            $rows[] = $r;
                        }
                        $_SESSION['sessionToken'] = $rows[0]['session_token'];
                        if ($toprint) {
                            echo 1;
                        }
                    } else {
                        if ($toprint) {
                            echo 0;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Picasaapi function
     * @return null|Zend_Gdata_HttpClient
     * @throws Zend_Gdata_App_HttpException
     */
    function getAuthSubHttpClient()
    {
        $this->getSessionTokenFromDb(false);
        $client = Zend_Gdata_AuthSub::getHttpClient($_SESSION['sessionToken']);
        return $client;
    }
}


//used for getting ajax calls
if (isset($_POST['func'])) {


    $picasaobj = new Picasaapi();
    //Gets if the user has connected his picasa account
    if ($_POST['func'] == 'getPicasaStatus') {
        $picasaobj->getSessionTokenFromDb(true);
    } //
    elseif ($_POST['func'] == 'getPicasaUrl') {
        echo $picasaobj->getAuthSubUrl();
    } elseif ($_POST['func'] == 'moveAlbumToPicasa' && isset($_POST['funcval'])) {
        mkdir("tempimages");
        $albumids = $_POST['funcval'];
        foreach ($albumids as $albumid) {
            $albumname = $picasaobj->moveAlbumToPicasa($albumid);


        }
        echo "1";
    }
}






?>