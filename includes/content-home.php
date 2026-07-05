<?php
if (!isset($contentItem) || !is_array($contentItem)) {
  if (isset($rowcontent) && is_array($rowcontent)) {
    $contentItem = $rowcontent;
  } elseif (isset($rowcontent1) && is_array($rowcontent1)) {
    $contentItem = $rowcontent1;
  } else {
    $contentItem = [];
  }
}
if (!isset($contentSourceFormId) || $contentSourceFormId === null || $contentSourceFormId === '') {
  $contentSourceFormId = (isset($contentItem['source_form_id']) && is_numeric((string) $contentItem['source_form_id']))
    ? (int) $contentItem['source_form_id']
    : null;
}
echo '<div class="cms-edit-target">';
echo cms_render_frontend_edit_button($contentItem, ['form_id' => $contentSourceFormId ?? null]);
?>
<!-- START content-home.php -->
		
<?php
	//  Set Col widths	
		echo "<div class='homecalendar'>";

		echo "<div class='col-lg-12 maincontent-area'>" ; // Heading across full width
			if ($rowcontent["showheading"] == 'Yes' )
			{
				echo "<h1>"	. $rowcontent["heading"] . "</h1>" ;
			}
		echo "</div>" ;


			// check div after this - set div for left hand column
				if ($rowcontent["left_width"] == '50%') {echo "<div class='col-lg-6  col-md-6 col-sm-6 col-xs-12 body-area-left'>" ; } 
				if ($rowcontent["left_width"] == '60%') {echo "<div class='col-lg-8  col-md-8 col-sm-6 col-xs-12 body-area-left'>" ; } 
				if ($rowcontent["left_width"] == '70%') {echo "<div class='col-lg-9  col-md-7 col-sm-8 col-xs-12 body-area-left'>" ; } 
				if ($rowcontent["left_width"] == '80%') {echo "<div class='col-lg-10 col-md-7 col-sm-6 col-xs-12 body-area-left'>" ; }

					if ($rowcontent["subheading"])
					{
						echo "<h2>"	. $rowcontent["subheading"] . "</h2>" ;
					}
					echo ""	. $rowcontent["text"] . "" ;
				echo "</div>" ;
	// END OF LEFT COL
	
	// START OF RIGHT COL - set div for right hand column
				if ($rowcontent["left_width"] == '50%') {echo "<div class='col-lg-6 col-md-6 col-sm-6 col-xs-12 body-area-right'>" ;}
				if ($rowcontent["left_width"] == '60%') {echo "<div class='col-lg-4 col-md-4 col-sm-6 col-xs-12 body-area-right'>" ;} 			
				if ($rowcontent["left_width"] == '70%') {echo "<div class='col-lg-3 col-md-5 col-sm-4 col-xs-12 body-area-right'>" ;} 		
				if ($rowcontent["left_width"] == '80%') {echo "<div class='col-lg-2 col-md-5 col-sm-6 col-xs-12 body-area-right'>" ;} 			
					
					echo "<h2>Events</h2>" ;

		$selectcalendar = "SELECT * FROM `events` WHERE `showonweb` = 'Yes' ORDER BY `date` LIMIT 5 ";
		$querycalendar = mysqli_query($conn,$selectcalendar);

		while ($rowcalendar = mysqli_fetch_assoc($querycalendar) )
		{
			$pieces = explode("-", flipdate($rowcalendar["date"],"-","-"));
			

			echo "<div class='col-lg-4 col-md-4 col-sm-6 col-xs-12'>" ;
				echo "" . date('j M', mktime(0, 0, 0, $pieces[1]  , $pieces[0] , $pieces[2])) . "";
			echo "</div>" ;
			echo "<div class='col-lg-8 col-md-8 col-sm-6 col-xs-12'>" ;
				echo "<a href='" . $baseURL . "/events/" . $rowcalendar["name"] . "/" . $rowcalendar["id"] . "'>" ;
			echo "" . $rowcalendar["name"] . "</a>";
			echo "</div>" ;

		}

					echo "<hr>" ;
					echo "<h2>Latest News</h2>" ;

		$selectnews = "SELECT * FROM `news` WHERE `showonweb` = 'Yes' ORDER BY `date` LIMIT 5 ";
		$querynews = mysqli_query($conn,$selectnews);

		while ($rownews = mysqli_fetch_assoc($querynews) )
		{
			$pieces = explode("-", flipdate($rownews["date"],"-","-"));
			

			echo "<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'>" ;
				echo "<a href='" . $baseURL . "/news/" . $rownews["slug"] . "/" . $rownews["id"] . "'>" ;
				echo "<h4>" . $rownews["home-head"] . "</h4></a>";
				echo "<p>" . $rownews["home-subhead"] . "</p>";
				echo "<p>" . date('D M j', mktime(0, 0, 0, $pieces[1]  , $pieces[0] , $pieces[2])) . "</p>";
			echo "</div>" ;

		}

			echo "</div>" ; 

	echo "</div>" ; 

?>
<div class="clearfix"></div>

<!-- END content-home.php -->
		<?php echo '</div>'; ?>
