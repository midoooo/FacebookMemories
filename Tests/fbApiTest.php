<?php

/*All tests performed below were tested by using mock session token and user id to create 
* scenario of a logged in user.
* To perform the tests just set the session token value into fbapi.php just after session_start();
* To perform the tests by your own, change the $expectedResult and $albumid accordingly
*/


require_once __DIR__ . '/../fbapi.php';

class fbApiTest extends \PHPUnit_Framework_TestCase
{

    function setUp()
    {
        if ($_SESSION['validatedLogin']) {
            $this->fbapi = new Fbapiclass();
        } else {
            echo "No session token created/Session token failed authentication. Read the instructions on the top of the page\n";
            exit();
        }

    }

    function testClassExistence()
    {
        $this->assertTrue(class_exists('Fbapiclass'));
    }

    function testMethodsExistence()
    {
        $this->assertTrue(method_exists($this->fbapi, 'getFromFb'));
        $this->assertTrue(method_exists($this->fbapi, 'getAlbumNameFromId'));
        $this->assertTrue(method_exists($this->fbapi, 'getImageListFromAlbumId'));
        $this->assertTrue(method_exists($this->fbapi, 'getImageAndNameListFromAlbumId'));
        $this->assertTrue(method_exists($this->fbapi, 'createDirFromAlbumId'));
        $this->assertTrue(method_exists($this->fbapi, 'createMultipleAlbumDirs'));
        $this->assertTrue(method_exists($this->fbapi, 'getDirNameCounter'));
        $this->assertTrue(method_exists($this->fbapi, 'getFileNameCounterWZip'));
        $this->assertTrue(method_exists($this->fbapi, 'createZipFromDirs'));
        $this->assertTrue(method_exists($this->fbapi, 'Zip'));
    }

    function testGettingAlbumNameFromId()
    {
        $albumid = '1174351274714';
        $expectedResult = 'Timeline Photos';
        $this->assertEquals($this->fbapi->getAlbumNameFromId($albumid), $expectedResult);

        $albumid = '2183002210357';
        $expectedResult = 'Cover Photos';
        $this->assertEquals($this->fbapi->getAlbumNameFromId($albumid), $expectedResult);
    }

    function testFailGettingAlbumNameFromId()
    {
        $albumid = '117435127471';
        $expectedResult = 'Invalid albumid';
        $this->assertEquals($this->fbapi->getAlbumNameFromId($albumid), $expectedResult);
    }


    function testGettingImageAndNameListFromAlbumId()
    {
        //getting all the images with their captions from album id
        //test 1 album id=2183002210357
        $albumid = '2183002210357';
        $expectedResult = [["https://scontent.xx.fbcdn.net/hphotos-xpa1/t31.0-8/s2048x2048/1956766_10203717777720713_4053020401764178208_o.jpg", "DA-IICT Convocation 2015"], ["https://fbcdn-sphotos-d-a.akamaihd.net/hphotos-ak-xfa1/v/t1.0-9/183710_3751846110474_685948330_n.jpg?oh=4b5234755b1fe824dc4ae94a9578062a&oe=557FE758&__gda__=1434619280_a1adbd01dc1849318885bdbe12c511ea", null]];
        $this->assertEquals($this->fbapi->getImageAndNameListFromAlbumId($albumid), $expectedResult);


        //test 2 album id=1174351274714
        $albumid = '1174351274714';
        $expectedResult = [["https://fbcdn-sphotos-c-a.akamaihd.net/hphotos-ak-xap1/t31.0-8/10914947_10203979513023932_7872741371153245843_o.jpg", "Sure."], ["https://scontent.xx.fbcdn.net/hphotos-xpf1/v/t1.0-9/10312578_10202132440448272_2841917272859720432_n.jpg?oh=fb08dd1cec19a051b7734e5a3a337b8d&oe=5570056B", "Even Barack Obama did not receive this kind of special treatment. Truly, Rajnikant can do anything ;)"], ["https://fbcdn-sphotos-g-a.akamaihd.net/hphotos-ak-xfp1/v/t1.0-9/1897745_10202048258583778_9184983365931527538_n.jpg?oh=08417f288581f9b50bbf541142b5710c&oe=5573C5DB&__gda__=1433818507_e342bf3d57070c0fe87dccf6b4dde827", "If you know what I mean.\n\ncc: Mudassir Malik, Saurabh G. Patel, Kunjan Doshi, Utkarsh Patel\n\n#GameOfThrones #RahulGandhi"], ["https://fbcdn-sphotos-g-a.akamaihd.net/hphotos-ak-ash2/t31.0-8/620712_10200632551071975_1588693049_o.jpg", "Map of world adjusted for the population size of countries."], ["https://fbcdn-sphotos-d-a.akamaihd.net/hphotos-ak-ash2/v/t1.0-9/531794_4307871530762_113917083_n.jpg?oh=140b51a828773ee30cc13424bce7fa5f&oe=55742F2E&__gda__=1434034551_210b80154fea815f449f74bb61d3d48a", "Have a look at No. 22 (FYI he is son-in-law of Sonia Gandhi).\r\nvia - Tushar Thakker"], ["https://fbcdn-sphotos-a-a.akamaihd.net/hphotos-ak-xaf1/v/t1.0-9/523142_3843038550228_2140993083_n.jpg?oh=4469223a84154787b4cd7c0aa06db2ab&oe=557543F3&__gda__=1434656660_e07a2b36711317615d5595af9d238761", null], ["https://scontent.xx.fbcdn.net/hphotos-xaf1/v/t1.0-9/262165_1999342978991_5087244_n.jpg?oh=454272cd55296d91f662af3e5fafcb70&oe=55801B84", "hehe :P"], ["https://fbcdn-sphotos-c-a.akamaihd.net/hphotos-ak-xpf1/v/t1.0-9/168143_1612503148237_7280145_n.jpg?oh=2a12a0b305f7af1c7f3a3f37a857c6b2&oe=556FA14A&__gda__=1434871571_17ff366fe0d37ce1d460b24204899f74", null], ["https://fbcdn-sphotos-g-a.akamaihd.net/hphotos-ak-xap1/v/t1.0-9/60874_1485201445774_3167185_n.jpg?oh=f1ce666754014d1a5a50bece148a4eb5&oe=557F95C7&__gda__=1438484573_3f6571eb111296c55e801329d77b7265", "hehe :)) courtesy Manthan Palkhiwala"]];
        $this->assertEquals($this->fbapi->getImageAndNameListFromAlbumId($albumid), $expectedResult);
    }

    function testFailGettingImageAndNameListFromAlbumId()
    {
        $albumid = '21830022103';
        $expectedResult = "Invalid albumid";
        $this->assertEquals($this->fbapi->getImageAndNameListFromAlbumId($albumid), $expectedResult);
    }

    /*Also tests getting image list from album id
    * for this test add the absolute path in the main class instead of the default path "DownloadFiles/"
    * change it to "parentfolder/DownloadFiles/"
    * as we have included the file to testFile. It considers the test directory as the local directory
    */

    function testCreateDirFromAlbumId()
    {
        $albumid = '2183002210357';
        $result = $this->fbapi->createDirFromAlbumId($albumid);
        $imageList = $this->fbapi->getImageListFromAlbumId($albumid);
        $fi = new FilesystemIterator('rtcamp/DownloadFiles/2183002210357', FilesystemIterator::SKIP_DOTS);
        $this->assertEquals(sizeof($imageList), iterator_count($fi));
        system("rm -rf " . escapeshellarg("rtcamp/DownloadFiles/" . $albumid));

    }

    function testFailCreateDirFromAlbumId()
    {
        //inputing wrong album id
        $albumid = '21830022103';
        $result = $this->fbapi->createDirFromAlbumId($albumid);
        $this->assertEquals($result, "Invalid albumid");
        system("rm -rf " . escapeshellarg("rtcamp/DownloadFiles/21830022103"));
    }

    /*for this test add the absolute path in the main class instead of the default path "DownloadFiles/"
    * change it to "parentfolder/DownloadFiles/"
    * as we have included the file to testFile. It considers the test directory as the local directory
    */

    function testCreateMultipleAlbumDirs()
    {
        $albumid = ['2183002210357', '1174351274714'];
        $this->fbapi->createMultipleAlbumDirs($albumid);
        $fi = new FilesystemIterator('rtcamp/DownloadFiles/1106032240/', FilesystemIterator::SKIP_DOTS);
        $this->assertEquals(sizeof($albumid), iterator_count($fi));
        system("rm -rf " . escapeshellarg("rtcamp/DownloadFiles/1106032240"));
    }

    function testFailCreateMultipleAlbumDirs()
    {
        $albumid = ['2183002210357', '123235253'];
        $response = $this->fbapi->createMultipleAlbumDirs($albumid);
        $this->assertEquals($response, "Invalid albumid");
    }

    function testCreateZipFromDirs()
    {
        mkdir('rtcamp/DownloadFiles/testdir');
        $this->fbapi->createZipFromDirs('testdir');
        $this->assertTrue(file_exists('rtcamp/DownloadFiles/1106032240.zip'));
        $this->assertTrue(!file_exists('rtcamp/DownloadFiles/testdir'));
        system("rm -rf " . escapeshellarg("rtcamp/DownloadFiles/1106032240.zip"));
    }


}

?>