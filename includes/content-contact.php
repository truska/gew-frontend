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
<!-- START content-contact.php  Layout id= 9 -->
<?php
		echo "<div class='col-12'>" ; // Heading
			if ($rowcontent["showheading"] == 'Yes' )
			{
				echo "<h1>"	. $rowcontent["heading"] . "</h1>" ;
			}
		echo "</div>" ;

			
		echo "<div class='col-12 col-lg-6 body-area-left'>" ; // Left Column wit sub head and text 1
			
				if ($rowcontent["subhead1"])
				{
					echo "<h2>"	. $rowcontent["subhead1"] . "</h2>" ;
				}
			


			  echo "<p><strong>" . getCompanyName($prefs) . "</strong></p>" ;
			  echo "<p>" . getAddressShort($prefs) . "</p>" ;

			  echo "<p>tel. " . getTel1($prefs) . "</p>" ;
			  echo "<p><a href='mailto:" . getEmail($prefs) . "'>e-mail: " . getEmail($prefs) . "</a></p>" ;
				if ($rowcontent["image"])	
				{

					echo "<img src='filestore/images/content/"	. $rowcontent["image"] . "' class='img-fluid' alt='"	. $rowcontent["title"] . " " . $rowcontent["subhead1"] . "'>" ;
				}
			echo "<p>&nbsp;</p>" ;
		echo "</div>" ;
			
		echo "<div class='col-12 col-lg-6 body-area-right'>" ; // Right Column
			
				if ($rowcontent["subhead2"])
				{
					echo "<h2>"	. $rowcontent["subhead2"] . "</h2>" ;
				}

				if ($rowcontent["text2"])	
				{
					include("includes/contact-form.php");
				echo "<p>&nbsp;</p>" ;

				}
			
		?>
									
		<?php
		echo "</div>" ;

		echo "<div class='col-12'>" ; // Map
				echo "" . getGoogleMap($prefs) . "";
		echo "</div>" ;
	?>
<!-- END content-contact.php -->


		
<?php echo '</div>'; ?>
