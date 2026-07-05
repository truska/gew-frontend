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
<!-- START content-standard-1-right-col.php -->
		
<?php
	//  Set Col widths	

		echo "<div class='col-lg-12'>" ; // Heading
			if ($rowcontent["showtitle"] == 'Yes' )
			{
				echo "<h1>"	. $rowcontent["title"] . "</h1>" ;
			}
			if ($rowcontent["subhead1"])
			{
				echo "<h2>"	. $rowcontent["subhead1"] . "</h2>" ;
			}
				echo ""	. $rowcontent["text1"] . "" ;
				echo "<p style='float:right; color:#dddddd;'>Ref:"	. $rowcontent["id"] . "</p>" ;
		echo "</div>" ;
		
	// END OF LEFT COL
?>	
<div class="clearfix"></div>

<!-- END content-standard-1-right-col.php -->
		<?php echo '</div>'; ?>
