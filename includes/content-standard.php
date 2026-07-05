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
<!-- START content-standard.php -->
		
<?php
	//  Set Col widths	

		echo "<div class='col-lg-12'>" ; // Heading across full width
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
				if ($rowcontent["left_width"] == '100%') {echo "<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12 body-area-left'>" ; }

					if ($rowcontent["subheading"])
					{
						echo "<h2>"	. $rowcontent["subheading"] . "</h2>" ;
					}
					echo ""	. $rowcontent["text"] . "" ;
					if ($prefs["prefDeBug"] == 'Yes') {
					echo "<br>Content id = "	. $rowcontent["id"] . "<br>" ;
					}

				echo "</div>" ;
	// END OF LEFT COL
	
	// START OF RIGHT COL - set div for right hand column
				if ($rowcontent["left_width"] == '50%') {echo "<div class='col-lg-6 col-md-6 col-sm-6 col-xs-12 body-area-right'>" ;}
				if ($rowcontent["left_width"] == '60%') {echo "<div class='col-lg-4 col-md-4 col-sm-6 col-xs-12 body-area-right'>" ;} 			
				if ($rowcontent["left_width"] == '70%') {echo "<div class='col-lg-3 col-md-5 col-sm-4 col-xs-12 body-area-right'>" ;} 		
				if ($rowcontent["left_width"] == '80%') {echo "<div class='col-lg-2 col-md-5 col-sm-6 col-xs-12 body-area-right'>" ;} 			
				if ($rowcontent["left_width"] == '100%') {echo "<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12 body-area-right'>" ;} 			
					if ($rowcontent["subheading1"])
					{
						echo "<h2>"	. $rowcontent["subheading1"] . "</h2>" ;
					}

				?>
					<script type="text/javascript">
						MagicThumb.options = {
						'expand-speed':'700',
						'expand-effect':'cubic',

					}
					</script>
				<?php
				//	$selectgallery = "SELECT * FROM `gallery` WHERE `page` = " . $rowpage['id'] . " AND `showonweb` = 'Yes' ORDER BY `order` " ;
					$selectgallery = "SELECT * FROM `gallery` WHERE `content` = " . $rowcontent['id'] . " AND `showonweb` = 'Yes' ORDER BY `sort` " ;
				//	echo $selectgallery . "<br>";
					$querygallery = mysqli_query($conn,$selectgallery);
					$num_rows = mysqli_num_rows($querygallery);
					$count = 1 ;
				//	echo "Number of records = " . $num_rows . "<br>";
					while ($rowgallery = mysqli_fetch_assoc($querygallery) )
					{
						if ($count == 1 )
						{
							echo "<div class='col-lg-12 col-md-12 col-sm-12 slideshowsm'>" ;
					//		echo "Count = " . $count . "<br>" ;
								echo "<a href='filestore/images/content/lg-" . $rowgallery["image"] . "' class='MagicThumb' id='hello'>" ;
									echo "<img src='filestore/images/content/st-" . $rowgallery["image"] . "' class='img-responsive' />" ;
								echo "</a>" ;
								echo "<br><br>" ;
							echo "</div>" ;
						}
 							echo "<div class='col-md-4 col-sm-4 col-xs-6 slideshowsm'>" ;							
								echo "<a href='filestore/images/content/lg-" . $rowgallery["image"] . "' rel='thumb-id:hello' rev='filestore/images/content/st-" . $rowgallery["image"] . "'>" ;
									echo "<img src='filestore/images/content/sm-" . $rowgallery["image"] . "' class='img-responsive' style='margin-bottom:20px;' />" ;
								echo "</a>" ;
							echo "</div>" ;
						$count = $count + 1 ;
					}


					if ($rowcontent["image"])	
					{
						echo "<img src='filestore/images/content/"	. $rowcontent["image"] . "' class='img-responsive' alt='"	. $rowcontent["title"] . " " . $rowcontent["subheading"] . "'>" ;
					}
					if ($rowcontent["text2"])	
					{
						echo ""	. $rowcontent["text2"] . "" ;
					}

			echo "</div>" ; 


?>
<div class="clearfix"></div>

<!-- END content-standard.php -->
		
<?php echo '</div>'; ?>
