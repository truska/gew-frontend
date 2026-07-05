<!doctype html>
<html>
<head>
<meta charset="utf-8">
<?php
include ("dbcon.php");
include ("functions.php");
?>
</head>

<body>
<h1>Preferences</h1>
<hr>
<?php


$prefs=loadPrefs($conn);

 echo getCompanyName($prefs) . "<br>";

 echo getAddressLong($prefs) . "<br>";

 echo getAddressShort($prefs) . "<br>";
 
 echo "<hr>" ;
 

print_r($prefs);

echo "<hr>" ;
echo "Home URL = " . (rtrim(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost')), '/') . '/') . "<br>" ;

echo $prefs['prefCompanyName'] . "<br>" ;
echo "<hr>" ;
echo "<h1>Preference Variables for " . $prefSiteName . " site</h1>" ;
echo "<h3>" . $prefs['prefURL'] . "</h3>" ;

	foreach ($prefs as $prefName => $prefValue) {
		echo "result: " . $prefName . " = " . $prefValue . "<br> " ;
	}
	?>
</body>
</html>
