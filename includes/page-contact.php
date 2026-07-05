<!-- START page-contact.php -->
<style>
    .google-maps {
        position: relative;
        padding-bottom: 75%; /* This is the aspect ratio */
        height: 0;
        overflow: hidden;
    }
    .google-maps iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100% !important;
        height: 100% !important;
    }
</style>
<?php
$cmsPrefs = $prefs;
$contactPrefKeys = [
    'prefSiteName',
    'prefAddress1',
    'prefAddress2',
    'prefTown',
    'prefCounty',
    'prefCountry',
    'prefPostcode',
    'prefTel1',
    'prefTel2',
    'prefTelIntCode',
    'prefEmail',
    'prefBelfastDepotName',
    'prefBelfastDepotMap',
    'prefBelfastDepotAddress1',
    'prefBelfastDepotAddress2',
    'prefBelfastDepotAddress3',
    'prefBelfastDepotTown',
    'prefBelfastDepotCounty',
    'prefBelfastDepotPostcode',
    'prefBelfastDepotCountry',
    'prefBelfastDepotEmail',
    'prefDublinDepotName',
    'prefDublinDepotMap',
    'prefDublinDepotAddress1',
    'prefDublinDepotAddress2',
    'prefDublinDepotAddress3',
    'prefDublinDepotTown',
    'prefDublinDepotCounty',
    'prefDublinDepotPostcode',
    'prefDublinDepotCountry',
    'prefDublinDepotEmail'
];
foreach ($contactPrefKeys as $key) {
    $cmsPrefs[$key] = cms_pref($key, $cmsPrefs[$key] ?? '');
}
?>

		<div class="container inner inner-page">
		<!-- Breadcrumb Trail -->
			<div class="liquid-nav">
				<ul>
					<li>
						<a href="<?php echo $baseURL ;?>/welcome">Home</a>
					</li>
					<li>
						<a href="#"><?php echo $rowpage["name"] ; ?></a>
					</li>
				</ul>
			</div> 
            
            <div class="row g-4">
               <?php 
                
                echo "<div class='col-12 inner-contact'>" ;
                    echo "<h1>" . $rowpage["name"] . "</h1>" ;
                echo "</div>" ;
                ?>
                

				
				<div class="col-12 col-lg-6">
					<div class="contact-wrapper">
					<!--	<div id="googleMap3" class="tow-map map-section"></div> --> <!-- NO MAP for HQ -->
						<h2>Headquarters </h2>
						<p>&nbsp;</p>
						<ul>
							
							<?php
							echo "<li>" . $cmsPrefs['prefSiteName'] . "</li>" ;
							echo getAddressShortList($cmsPrefs) . "" ;
							?>						
							<li class="mt-10"><span class="col2"><i class="fas fa-phone-alt"></i></span>&nbsp;&nbsp;<a href="tel:<?php echo getTel1Dial($cmsPrefs); ?>"><?php echo getTel1Display($cmsPrefs); ?></a> (UK)</li>
                           <li class="mt-10"><span class="col2"><i class="fas fa-phone-alt"></i></span>&nbsp;&nbsp;<a href="tel:<?php echo getTel2Dial($cmsPrefs); ?>"><?php echo getTel2Display($cmsPrefs); ?></a> (RoI)</li> 
							<li class="mt-10"><span class="col2"><i class="fas fa-at"></i></span>&nbsp;&nbsp;<a href="mailto:<?php echo getEmail($cmsPrefs); ?>"><?php echo getEmail($cmsPrefs); ?></a></li>
						</ul>
                        
                        
                        <!-- Debug Captcha -->
                        <?php
                        /*
                            echo "<p>Captch use? " . $prefs['prefCaptcha'] . "<br>" ;
                            echo "Captch Ver? " . $prefs['prefCaptchaVer'] . "<br>" ;
                            echo "Captch Site? " . $prefs['prefCaptchaSiteKey'] . "<br>" ;
                            echo "Captch Secr? " . $prefs['prefCaptchaSecretKey'] . "</p>" ;
                        */
                        ?>
					</div>
				</div>
				
				<div class="col-12 col-lg-6">
					<div class="contact-wrapper">
						
						<h2>Contact Us </h2>
						<p>&nbsp;</p>
                    
                        <?php
                            include("includes/contact-form.php"); 
                        ?>
                        

					</div>
				</div>
                
                
                <div class="col-12">
					<div class="contact-wrapper">
                        
						<h2>Find Us</h2>

                        <div class="google-maps">
                            
                        <?php
                            $mapAddressParts = prefAddressParts($cmsPrefs);
                            $mapQuery = implode(', ', $mapAddressParts);
                            if ($mapQuery !== '') {
                                $mapSrc = 'https://maps.google.com/maps?q=' . rawurlencode($mapQuery) . '&t=&z=13&ie=UTF8&iwloc=&output=embed';
                                echo "<iframe src='" . cms_h($mapSrc) . "' frameborder='0' style='border:0;' allowfullscreen='' loading='lazy' referrerpolicy='no-referrer-when-downgrade'></iframe>";
                            } else {
                                echo $cmsPrefs['prefBelfastDepotMap'];
                            }
                        ?>
						<!--	<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4552.344522834317!2d-5.921558858904399!3d54.613619729024556!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNTTCsDM2JzQ3LjQiTiA1wrA1NScxMS4xIlc!5e0!3m2!1sen!2suk!4v1571658082659!5m2!1sen!2suk" width="600" height="450" frameborder="0" style="border:0;" allowfullscreen=""></iframe> -->
                        
                        </div>
						<p>&nbsp;</p>
						<ul>
							
							<?php
							echo "<li>" . $cmsPrefs['prefSiteName'] . "</li>" ;
							foreach (prefAddressParts($cmsPrefs) as $addressLine) {echo "<li>" . $addressLine . "</li>" ; }
							?>						

							<li class="mt-10"><span class="col2"><i class="fas fa-phone-alt"></i></span>&nbsp;&nbsp;  <a href="tel:<?php echo getTel1Dial($cmsPrefs); ?>"><?php echo getTel1Display($cmsPrefs); ?></a> </li>
							<li class="mt-10"><span class="col2"><i class="fas fa-at"></i></span>&nbsp;&nbsp;  <a href="mailto:<?php echo getEmail($cmsPrefs); ?>"><?php echo  getEmail($cmsPrefs); ?></a></li>
							<?php
					//		if ($prefs['prefBelfastDepotLat']) {echo "<li> " . $prefs['prefBelfastDepotLat'] . "</li>" ; }	
					//		if ($prefs['prefBelfastDepotLong']) {echo "<li> " . $prefs['prefBelfastDepotLong'] . "</li>" ; }					
							?>
							<!--
							<li>Latitude: 54.613060</li>
							<li>Longitude: -5919604</li>-->
						</ul>
					</div>
				</div>
			</div>
		
		</div>



<!-- END page-contact.php -->
