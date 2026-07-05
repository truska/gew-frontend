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
<!-- START content-policy.php -->
<!--Middle section-->
<?php

//Set up Content Pages Data 
$selectcontent = "SELECT * FROM `content` WHERE `page` = '" . $rowpage["id"] . "' AND `showonweb` = 'Yes' ORDER BY `sort` ";
$querycontent = mysqli_query($conn, $selectcontent );
$policyPrefs = $prefs;

foreach (['prefCompanyNo', 'prefVATNo', 'prefPolicyDate', 'prefEmail', 'prefTel1', 'prefTel2', 'prefTelIntCode'] as $key) {
    $policyPrefs[$key] = prefValue($policyPrefs, $key);
}
?>

<section class="policy-content textsection">
  <div class="container">
    <div class="editContainer">

    <div class="row Middle-section">
      <div class="col-xs-12 policy-content">
    <!--    <h2 class="heading2Size">Privacy Policy</h2> -->
        <p class="ptextSize">In this document the following references apply:</p>
			<p>Where reference is made to "business or site owner, we or us" we are referring to <?php echo getCompanyName($policyPrefs) ; ?> <br>
			Our registered address is: <?php echo getAddressLong($policyPrefs) ; ?> <br>
			
			Our UK telephone number is: <?php echo getTel1Display($policyPrefs) ; ?> <br>
			Our RoI telephone number is: <?php echo getTel2Display($policyPrefs) ; ?> <br>
			Our email address is: <?php echo getEmail($policyPrefs) ; ?> <br>
			<?php
				if ($policyPrefs['prefCompanyNo']) {
					echo "Our Company Number is: " . $policyPrefs['prefCompanyNo'] . "<br>" ;
				}
				if ($policyPrefs['prefVATNo']) {
					echo "Our VAT Number is: " . $policyPrefs['prefVATNo'] . "<br>" ;
				}
				if ($policyPrefs['prefPolicyDate']) {
					echo "This policy is effective from: " . $policyPrefs['prefPolicyDate'] . "<br>" ;
				}
			?>
		  </p>

      </div>
    </div> 
		
		
	  <?php
	  while ($rowcontent = mysqli_fetch_assoc( $querycontent ) )
	  {
    	echo "<div class='row Middle-section'>" ;
			echo "<div class='col-xs-10 policy-content'>" ;
				if ($rowcontent["showheading"] == 'Yes') {
					echo "<h2 class='heading2Size'>" . $rowcontent["heading"] . "</h2>" ;
				}
				echo "<p class='ptextSize'>" . $rowcontent["text"] . "</p>" ;
			echo "</div>" ;
    	echo "</div>  " ;
	  }
	  ?>
		

		

		
		
		
  </div>
  </div>
</section>
<!-- END ccontent-policy.php -->
<?php echo '</div>'; ?>
