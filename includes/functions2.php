<!-- START functions2.php -->

<?php

function loadPrefscss() {
		global $conn;
		$selectprefscss = "SELECT `name`, `value` FROM `cms_skin` ORDER BY `prefCat` ";
		$queryprefscss = mysqli_query($conn,$selectprefscss);
	while ($rowprefscss = mysqli_fetch_assoc($queryprefscss) )
		{
			$prefscss[$rowprefscss["name"]] = $rowprefscss["value"];
		}
		return $prefscss;
	}

/* General Functions*/

function valid_email($email) {
  return filter_var((string)$email, FILTER_VALIDATE_EMAIL) !== false;
}

function flipdate($dt, $seperator_in, $seperator_out)
	{
	return implode($seperator_out, array_reverse(explode($seperator_in, $dt)));
	}
?>


<!-- END functions2.php -->
