<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__.'/../picasaapi.php';


class picasaApiTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		if($_SESSION['validatedLogin']){
			$this->picasaapi=new Picasaapi();
		}
		else{
			echo "No session token created/Session token failed authentication. Read the instructions on the top of the page\n";
			exit();
		}
	}
	public function testClassExistence(){
				$this->assertTrue(class_exists('Picasaapi'),'Class Picasaapi not found');
	}
	function testMethodsExistence(){
		$this->assertTrue(method_exists($this->picasaapi, 'moveAlbumToPicasa'),'Method moveAlbumToPicasa does not exist in class picasaapi');
		$this->assertTrue(method_exists($this->picasaapi, 'uploadPicToPicasa'),'Method uploadPicToPicasa does not exist in class picasaapi');
		$this->assertTrue(method_exists($this->picasaapi, 'getAuthSubUrl'),'Method getAuthSubUrl does not exist in class picasaapi');
		$this->assertTrue(method_exists($this->picasaapi, 'getSessionTokenFromDb'),'Method getSessionTokenFromDb does not exist in class picasaapi');
		$this->assertTrue(method_exists($this->picasaapi, 'getAuthSubHttpClient'),'Method getAuthSubHttpClient does not exist in class picasaapi');
	}
	function testMovingAlbumToPicasa(){
		$albumid='2183002210357';
		$this->assertEquals($this->picasaapi->moveAlbumToPicasa($albumid),"Cover Photos");
	}
	function testFailMovingAlbumToPicasa(){
		$albumid='218abc';
		$this->assertEquals($this->picasaapi->moveAlbumToPicasa($albumid),"Invalid albumid");
	}

}