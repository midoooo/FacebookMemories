<?php
/**
 * Created by PhpStorm.
 * User: aakashthakkar
 * Date: 02/02/15
 * Time: 10:17 AM
 */
/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/
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
     * This function inputs the album id and returns album name
     * @param $albumid  inputting album ID
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
     * This function inputs album id, fetches images and saves it in a temporary directory
     * @param String $albumid is the album id of which the directory is to be made
     * Creates single download upload album directory
     * @return null
     **/
    function createDirFromAlbumId($albumid)
    {
        try {
            $imagelist = $this->getImageListFromAlbumId($albumid);
            system("rm -rf " . escapeshellarg(DOWNLOADDIR . "/" . $albumid));
            mkdir(DOWNLOADDIR . "/" . $albumid);
            $i = 0;
            foreach ($imagelist as $img) {
                $file = basename($img, ".jpg");
                copy($img, DOWNLOADDIR . "/" . $albumid . "/" . $albumid . $i . ".jpg");
                $i++;
            }
            return 1;
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
                echo "exists";
                $counter = $this->getDirNameCounter();
                $dirname = $dirname . $counter;
            }
            mkdir($dirname);
            foreach ($albumids as $album) {

                $imagelist = $this->getImageListFromAlbumId($album);

                if (is_array($imagelist)) {
                    system("rm -rf " . escapeshellarg($dirname . "/" . $album));
                    mkdir($dirname . "/" . $album);
                    $i = 0;
                    foreach ($imagelist as $img) {
                        $file = basename($img, ".jpg");
                        copy($img, $dirname . "/" . $album . "/" . $album . $i . ".jpg");
                        $i++;
                    }
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
            return "Invalid albumid";
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

                $this->Zip(DOWNLOADDIR . "/" . $dirs, DOWNLOADDIR . "/" . $_SESSION['userid'] . $counter . ".zip", true);
                if (file_exists(DOWNLOADDIR . "/" . $_SESSION['userid'] . $counter . ".zip")) {
                    system("rm -rf " . escapeshellarg(DOWNLOADDIR . "/" . $dirs));
                    return $counter;
                } else {
                    return 0;
                }
            } else {
                $this->Zip(DOWNLOADDIR . "/" . $dirs, DOWNLOADDIR . "/" . $_SESSION['userid'] . ".zip", true);
                system("rm -rf " . escapeshellarg(DOWNLOADDIR . "/" . $dirs));
                return file_exists(DOWNLOADDIR . "/" . $_SESSION['userid'] . ".zip");
            }
        } catch (Exception $e) {
            return "Invalid Request";
        }

    }

    /**
     * @param $source String source folder
     * @param $destination String destination folder path
     * @param $include_dir Boolean bool to include dirs in the zip
     * Creates ZIP files with parameters of source, destination and a flag to include directories
     * @return Boolean of successfull creation
     **/
    function Zip($source, $destination, $include_dir = true)
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }
        if (file_exists($destination)) {
            unlink($destination);
        }
        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }
        $source = str_replace('\\', '/', realpath($source));
        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
            if ($include_dir) {
                $arr = explode("/", $source);
                $maindir = $arr[count($arr) - 1];
                $source = "";
                for ($i = 0; $i < count($arr) - 1; $i++) {
                    $source .= '/' . $arr[$i];
                }
                $source = substr($source, 1);
                $zip->addEmptyDir($maindir);
            }
            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
                    continue;
                $file = realpath($file);
                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else if (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } else if (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }
        return $zip->close();
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
    //Used to download single albums through AJAX
    if ($_POST['functype'] == 'downloadImagesFromAlbum' && isset($_POST['funcval'])) {

        $fbapi->createDirFromAlbumId($_POST['funcval']);
        echo $fbapi->createZipFromDirs($_POST['funcval']);
        //var_dump($graphObject);

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