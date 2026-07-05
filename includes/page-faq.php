<!-- START page-event.php -->
<?php
		$selectfaq = "SELECT * FROM `faq` WHERE `showonweb` = 'Yes' ORDER BY `order` ";
		$queryfaq = mysqli_query($conn,$selectfaq);
		$num_rows = mysqli_num_rows($queryfaq);

?>
<section class="row body-area">
	
	<div class="col-lg-10 col-lg-offset-1 body-area-top">
		
		<div class="col-lg-9 col-md-9">
			<?php echo "<h1>FAQs</h1>" ; ?>
		</div>
		
		<div class="clearfix"></div>
		
		<div class="col-lg-12">
			
		<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
		
		<?php
			$counter = 1 ;
			$classstatus = '' ;
			$expand = 'true' ;
			$collapse = 'in' ;
		while ($rowfaq = mysqli_fetch_assoc($queryfaq) ) {
			
		  echo "<div class='panel panel-default'>" ;
			echo "<div class='panel-heading' role='tab' id='heading" . $counter . "'>" ;
			  echo "<h4 class='panel-title'>" ;
				echo "<a class='" . $classstatus . "' role='button' data-toggle='collapse' data-parent='#accordion' href='#collapse" . $counter . "' aria-expanded='" . $expand . "' aria-controls='collapse" . $counter . "'>" ;
				  echo "" . $rowfaq["question"] . "" ;
				echo "</a>" ;
			 echo " </h4>" ;
			echo "</div>" ;
			
			echo "<div id='collapse" . $counter . "' class='panel-collapse collapse " . $collapse . "' role='tabpanel' aria-labelledby='heading" . $counter . "'>" ;
			  echo "<div class='panel-body'>" ;
				echo "" . $rowfaq["answer"] . "" ;
			  echo "</div>" ;
			echo "</div>" ;
		  echo "</div>" ;
			
			$counter ++ ;
			$classstatus = 'collapsed' ;
			$expand = 'false' ;
			$collapse = '' ;

		}
		?>

		</div>

		</div>
	
	</div>
</section>


<!-- END page-event.php -->