<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
require_once 'fbcredentials.php';
require_once('lib/Zipmaker.php');
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

define('DOWNLOADDIR', 'DownloadFiles');
session_start();
//this if will redirect back to index if not logged in, or unauthorized user
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


/**
 * This class transacts with the Facbook API as well as consists the functions which are used by the AJAX calls made by
 * view.
 * @author Aakash Thakkar <thakkaraakash@hotmail.com>
 **/
class Fbapiclass
{
    /**
     * This function inputs the query string for Facebook API call. Generic function to get Information from facebook
     * @param String $args query string
     * Sending a get request to FB API
     * @return Facebook Graph Object from that GET query
     **/
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

    /**
     * @param String $albumid inputting album ID
     * This function inputs the album id and returns album name
     * @return string - returns album name
     */
    function getAlbumNameFromId($albumid)
    {
        try {
            $reqstring = "/" . $albumid;
            $graphObject = $this->getFromFB($reqstring);
            //var_dump($graphObject);
            return $graphObject->getProperty('name');
        } catch (Exception $e) {
            return "Invalid albumid";
        }
    }

    /**
     * This function inputs album id and returns the list of image url's used for downloading
     * @param String $albumid input album id
     * Gets image list from a particular album id. It uses the FB API GET function to get list
     * @return Array of the image sources
     **/
    function getImageListFromAlbumId($albumid)
    {
        try {
            $reqstring = "/" . $albumid . "/photos";
            $graphObject = $this->getFromFB($reqstring);

            $data = $graphObject->getProperty('data');
            $arr = $data->asArray();

            $outputarray = array();
            $initial = 0;
            foreach ($arr as $row) {
                $outputarray[$initial] = $row->images[0]->source;
                $initial++;
            }
            return $outputarray;


        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    /**
     * This function inputs album id and returns an array with the URL of the image and the name of that image.
     * @param $albumid - inputs album id
     * @return array|string - returns a two dimensional array with [n][2]. [n][0] being the URL of the image and [n][1]
     * being the image name in facebook
     */
    function getImageAndNameListFromAlbumId($albumid)
    {
        try {
            $reqstring = "/" . $albumid . "/photos";
            $graphObject = $this->getFromFB($reqstring);
            $data = $graphObject->getProperty('data');
            //var_dump($data);
            $arr = $data->asArray();
            $outputarray = array();
            $initial = 0;
            foreach ($arr as $row) {
                $outputarray[$initial][0] = $row->images[0]->source;
                $outputarray[$initial][1] = $row->name;
                // $outputarray[$initial]=$row->images[0]->source;
                $initial++;
            }
            return $outputarray;
        } catch (Exception $e) {
            return "Invalid albumid";
        }
    }




    /**
     * This function takes an array of album ids, creates a temp directory with all the album dirs and pictures in it.
     * @param $albumids String Array of id's
     * gets multiple albums and creates one dir with all of them
     * @return Directory name
     **/
    function createMultipleAlbumDirs($albumids)
    {
        try {
            $dirname = DOWNLOADDIR . "/" . $_SESSION['userid'];
            $counter = 0;
            if (file_exists($dirname)) {
                //echo "exists";
                $counter = $this->getDirNameCounter();
                $dirname = $dirname . $counter;
            }
            mkdir($dirname);
            $count=1;

            foreach ($albumids as $album) {
                $imagelist = $this->getImageListFromAlbumId($album);
                $albumname=$this->getAlbumNameFromId($album);
                if (is_array($imagelist)) {
                    system("rm -rf " . escapeshellarg($dirname . "/" . $albumname));
                    mkdir($dirname . "/" . $albumname);
                    $i = 0;
                    foreach ($imagelist as $img) {
                        copy($img, $dirname . "/" . $albumname . "/" . "image-" . ($i+1) . ".jpg");
                        $i++;
                    }
                    $count++;
                } else {
                    throw new Exception("Invalid Request!");
                }

            }
            if ($counter == 0) {
                return $_SESSION['userid'];
            } else {
                return $_SESSION['userid'] . $counter;
            }

        } catch (Exception $e) {
            $val = "";
            if ($counter == 0) {
                $val = $_SESSION['userid'];
            } else {
                $val = $_SESSION['userid'] . $counter;
            }
            system("rm -rf " . escapeshellarg(DOWNLOADDIR . "/" . $val));
            echo $e->getMessage();
        }


    }

    /**
     * Directory name available counter
     * @return available directory name counter
     **/
    function getDirNameCounter()
    {
        $counter = 2;
        while (file_exists(DOWNLOADDIR . "/" . $_SESSION['userid'] . $counter)) {
            $counter++;
        }
        return $counter;
    }


    /**
     * Filename available counter
     * @return available filename counter
     **/
    function getFileNameCounterWZip()
    {
        $counter = 2;
        while (file_exists(DOWNLOADDIR . "/" . $_SESSION['userid'] . $counter . ".zip")) {
            $counter++;
        }
        return $counter;
    }

    /**
     * @param $dirs String inputs the directory name
     * Creates a zip of the directory name given in param
     * @return counter of filename available
     **/
    function createZipFromDirs($dirs)
    {


        try {
            if (file_exists(DOWNLOADDIR . "/" . $_SESSION['userid'] . ".zip")) {

                $counter = $this->getFileNameCounterWZip();

                Zipmaker::Zip(DOWNLOADDIR . "/" . $dirs, DOWNLOADDIR . "/" . $_SESSION['userid'] . $counter . ".zip", true);
                if (file_exists(DOWNLOADDIR . "/" . $_SESSION['userid'] . $counter . ".zip")) {
                    system("rm -rf " . escapeshellarg(DOWNLOADDIR . "/" . $dirs));
                    return $counter;
                } else {
                    return 0;
                }
            } else {
                Zipmaker::Zip(DOWNLOADDIR . "/" . $dirs, DOWNLOADDIR . "/" . $_SESSION['userid'] . ".zip", true);
                system("rm -rf " . escapeshellarg(DOWNLOADDIR . "/" . $dirs));
                return file_exists(DOWNLOADDIR . "/" . $_SESSION['userid'] . ".zip");
            }
        } catch (Exception $e) {
            return "Invalid Request";
        }
    }

}

if (isset($_POST['functype']) && isset($_POST['funcval'])) {
    $fbapi = new Fbapiclass();
    mkdir(DOWNLOADDIR);

    //delete all files older than 1 hour
    $dir = DOWNLOADDIR . "/";
    foreach (glob($dir . "*") as $file) {

        if (filemtime($file) < time() - 3600) {
            unlink($file);
        }
    }
    //used to download multiple or all albums through AJAX
    if ($_POST['functype'] == 'downloadMultipleAlbums' && isset($_POST['funcval'])) {
        $albumids = $_POST['funcval'];
        $dirname = $fbapi->createMultipleAlbumDirs($albumids);

        $isCreated = $fbapi->createZipFromDirs($dirname);
        echo $isCreated;

    }
    if ($_POST['functype'] == 'getImagesAndNamesFromAlbumid' && isset($_POST['funcval'])) {
        echo json_encode($fbapi->getImageAndNameListFromAlbumId($_POST['funcval']));
    }

}



?>