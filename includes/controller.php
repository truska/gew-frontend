<!-- START controller.php -->

<?php
$debug = 'No';
$devdebug = 'No';
if($_SERVER['REMOTE_ADDR'] == '82.153.24.226') {$devdebug = 'No';}

// Use the same private database bootstrap as the active frontend entry point.
require_once __DIR__ . '/../../private/dbcon.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
	http_response_code(500);
	echo "<h2>Database connection error</h2>";
	if (!empty($DB_LEGACY_ERROR)) {
		echo "<p>Legacy DB error: " . htmlspecialchars($DB_LEGACY_ERROR, ENT_QUOTES, 'UTF-8') . "</p>";
	}
	if (!empty($DB_ERROR)) {
		echo "<p>PDO DB error: " . htmlspecialchars($DB_ERROR, ENT_QUOTES, 'UTF-8') . "</p>";
	}
	exit;
}

//Get Page URL
	$pageURL = $_GET['url'] ?? '';
	$pageURL = rtrim($pageURL,'/') ;

/* 2nd level urls and variables */

	$pagenumber = basename( $pageURL );
	//echo "Page Number = " . $pagenumber . "<br>" ;
	$segs = explode('/', $pageURL);
	if (!isset($segs[0]) || $segs[0] === '') {
		$segs[0] = 'welcome';
	}

if ($debug == 'Yes' ) {
	echo "<p>Debug = Yes</p>" ;
	echo "segs[0] = " .$segs[0] . "<br>"; 
	echo "segs[1] = " .$segs[1] . "<br>"; 
	echo "segs[2] = " .$segs[2] . "<br>"; 
	echo "segs[3] = " .$segs[3] . "<br>"; 
	echo "segs[4] = " .$segs[4] . "<br>"; 
	echo "segs[5] = " .$segs[5] . "<br>";  
	echo "new url = " . $newpageURL = $segs[0] . "/" . $segs[1] . "<br>" ;
	echo "Page URL = " . $pageURL . "<br>" ;
echo "<hr>Gets:<br>" ;
	
var_dump($_GET);
foreach ($_GET as $getParam => $value) {
    echo $getParam . ' = ' . $value . PHP_EOL;
	}
}


// Load the functions
include("includes/functions.php");

	if ($debug == 'Yes' ){
		echo "<p><strong>Check DB Connection </strong><br>" ;
		$selecttest = "SELECT * FROM `cms_preferences` ";
		echo "select for test : " . $selecttest . "<br>" ;
		$querytest = mysqli_query($conn,$selecttest);
		while ($rowtest = mysqli_fetch_assoc($querytest) ) {
			{
				echo "id " . $rowtest["id"] . ": " .$rowtest["name"] . " = " . $rowtest["value"] . "<br> " ;
			}
			
		}
	}
		echo "</p>";
// Load Functions
$prefs=loadPrefs($conn);
$prefshop=loadShopPrefs($conn);
$prefmodule=loadModulePrefs($conn);
$preftext = loadShopText($conn) ;
	if ($debug == 'Yes' ){
		echo "<p><strong>Check Functions</strong><br>" ;
		echo "Main Finction Company Name: ".getCompanyName($prefs)."<br>" ;
		echo "Shop Function Shop Name: ".$prefshop['prefShopName']."<br>" ; 
		echo "Shop Text: ".$preftext['emptycart']."<br>" ;
		echo "Module Function PostCode: ".$prefmodule['prefModulePostcode']."<br>" ;
	}

// Set base URL for all internal links from current request host/scheme.
		$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
		$isHttpsRequest = (
			(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
			(($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
		);
		$scheme = $isHttpsRequest ? 'https' : 'http';
		$baseURL = $scheme . "://" . $host;
	if ($debug == 'Yes' OR $devdebug == 'Yes' ){
		echo "<p><strong>Check URL</strong><br>SSL = " . $prefs['prefSSL'] . "<br>baseURL = " . $baseURL . "</p>" ;
	}



// Select Page  
	$selectpage = "SELECT * FROM `pages` WHERE `slug` = '" . $segs[0] . "' ";
	$querypage = mysqli_query($conn,$selectpage);
	$rowpage = mysqli_fetch_assoc($querypage) ;
		$slugID = $rowpage['id'] ?? 0;
	
	if ($slugID > 0 ) {
			$slugID = $slugID ;
			$page404 = 'No' ;
		} 
		else
		{
				$newpageurl = "page-not-found-404/" . ($segs[1] ?? '');
				$segs = explode('/', $newpageurl);
				$slugID = 6 ;
				$select404 = "SELECT * FROM `pages` WHERE `id` = '6' LIMIT 1";
				$query404 = mysqli_query($conn, $select404);
				$rowpage = mysqli_fetch_assoc($query404) ?: [];
				$page404 = 'Yes' ;
			}
		
		if ($debug == 'Yes'  OR $devdebug == 'Yes')
		{
			echo "<p><strong>Select Page</strong><br>SQL = " .$selectpage . "<br>Page/Slug ID = " .  $slugID . " -|- " . $rowpage['name'] . "</p>";
		}

// Select Page layout
		$pageLayout = '';
		if (!empty($rowpage["layout"])) {
			$selectLayout = "SELECT * FROM `layout` WHERE `id` = '" . $rowpage["layout"] . "' ";
			$queryLayout = mysqli_query($conn,$selectLayout);
			$rowLayout = mysqli_fetch_assoc($queryLayout) ;
			$pageLayout = $rowLayout["url"] ?? '';
		}
			if ($debug == 'Yes'  OR $devdebug == 'Yes')
			{
				echo "<p><strong>Page layout</strong><br>id = " . $rowpage["layout"] . " | " . $pageLayout . "</p>" ;
			}

// Home Page
	if (($rowpage['slug'] ?? '') === 'welcome')
	{$homePage = "Yes" ;} else {$homePage = "No" ;}
		$homePageURL = rtrim($baseURL, '/') . '/' ;
		// Check for holding page
		if ($slugID == '79') { // Holdingpage id
			$homePageURL = rtrim($baseURL, '/') . '/holding' ;
		}
			if ($debug == 'Yes' OR $devdebug == 'Yes' )
			{
				echo "<p><strong>Home Page</strong><br>Is this Home page = " . $homePage . " | Home Page Slug =  " . $homePageURL . "</p>" ;
			}


	//Banner Layout
	//$bannerLayout = $rowpage['id'] ;
		$bannerLayout = '';
		if (!empty($rowpage["bannerlayout"])) {
			$selectBanner = "SELECT * FROM `layout` WHERE `id` = '" . $rowpage["bannerlayout"] . "' ";
			$queryBanner = mysqli_query($conn,$selectBanner);
			$rowBanner = mysqli_fetch_assoc($queryBanner) ;
			$bannerLayout = $rowBanner["url"] ?? '';
		}
			if ($debug == 'Yes' )
			{
				echo "<p>Banner layout = " . $bannerLayout . "</p>" ;
			}
// Banners
	// Page top banner via Banner table
/*		$selectbannerpage = "SELECT * FROM `banner` WHERE `page` = '" . $rowpage['id'] . "' AND `position` = 'Top' AND `showonweb` = 'Yes' ORDER BY `sort` LIMIT 1 ";
		$querybannerpage = mysqli_query($conn,$selectbannerpage) ;
		$rowbannerpage = mysqli_fetch_assoc($querybannerpage) ;
		$pagetopbannerbg = $rowbannerpage['image'] ;

			if ($debug == 'Yes' OR $devdebug == 'Yes' )
			{
				echo "<p><strong>Banners</strong><br>Top Banner = " . $pagetopbannerbg . "</p>" ;
			}
*/
// Product Image Folders
		$lg = $prefs['prefImageFolderLarge'] ?? '';
		$md = $prefs['prefImageFolderMedium'] ?? '';
		$sm = $prefs['prefImageFolderSmall'] ?? '';
		$xs = $prefs['prefImageFolderThumbnail'] ?? '';
			if ($debug == 'Yes' )
			{
				echo "<p>" ;
					echo "Product Images folders<br>" ;
					echo "Large = " . $lg . " | Medium = " . $md . " | Small = " . $sm. " | Thumbnail = " . $xs . "" ;
				echo "</p>" ;
			}

// Page Background
	if($rowpage['imagebg'] )
	{
		$pageBackground = $rowpage['imagebg'];
	}
	else
	{
		$pageBackground = 'bgdefault.jpg';
	}
			if ($debug == 'Yes'  OR $devdebug == 'Yes')
			{
				echo "<p><strong>Background Image</strong><br>Image file name = " . $pageBackground . "</p>" ;
			}
// Set Currency
	if ($prefshop['prefCurSym1']) {$Curr=$prefshop['prefCurSym1'];} 
	if ($prefshop['prefCurrency']) {$Currency = $prefshop['prefCurrency'];} 
    $collection=$prefshop["prefCollection"];    
?>
<!-- END controller.php -->
