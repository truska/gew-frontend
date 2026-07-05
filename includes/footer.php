<!-- START footer.php -->
<style>
    .accreditationfooter img {
        max-height:80px;
        margin-top:40px;
    }
</style>
			<?php
			if ($homePage == "Yes"){
			//	echo "<div class='container inner'> ";
            ?>
            <style>
                footer  {
                    padding-top:80px !important;
                }
            </style>
            <?php
			}
			else
			{
			//	echo "<div class='container inner inner-footer'>" ;
			}
            $cmsPrefs = $prefs;
            $footerPrefKeys = [
                'prefSiteName',
                'prefCompanyName',
                'prefCopyrightStartYear',
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
                'prefBelfastDepotAddress1',
                'prefBelfastDepotAddress2',
                'prefBelfastDepotAddress3',
                'prefBelfastDepotTown',
                'prefBelfastDepotCounty',
                'prefBelfastDepotCountry',
                'prefBelfastDepotPostcode',
                'prefDublinDepotName',
                'prefDublinDepotAddress1',
                'prefDublinDepotAddress2',
                'prefDublinDepotAddress3',
                'prefDublinDepotTown',
                'prefDublinDepotCounty',
                'prefDublinDepotCountry',
                'prefDublinDepotPostcode',
                'prefShowFooterDebug'
            ];
            foreach ($footerPrefKeys as $key) {
                $cmsPrefs[$key] = cms_pref($key, $cmsPrefs[$key] ?? '');
            }
?>
  		<footer class="footer">          
<?php
            echo "<div class='container inner inner-footer'>" ;
			?>
			<!--<div class="container inner"> -->
            	<div class="row">
            		<div class="col-sm-4 col-md-3">
            			<ul>
							<?php
							$selectnavfootermenu = "SELECT * FROM `menu` WHERE  `showonhomemobile` = 'Yes' ORDER BY `menu`, `submenu` " ;
							$querynavfootermenu = mysqli_query($conn,$selectnavfootermenu);
							while ($rownavfootermenu = mysqli_fetch_assoc($querynavfootermenu) )
							{
								echo "<li><a href='" . $baseURL . "/product-list-section/" . $rownavfootermenu["section"] . "'>" . $rownavfootermenu["title"] . "</a></li>" ;
							}

							?>
            			</ul>
            		</div>





					
            		<div class="col-sm-4 col-md-3">
            			<ul class="addres">
							<?php
							echo "<p>" . getAddressShortList($cmsPrefs) . "</p>" ;
                            ?>
                            <!--
                            	<li><i class="fas fa-phone-alt"></i></span>&nbsp;&nbsp; <a href="tel:<?php echo getTel1Int($prefs); ?>"><?php echo getTel1($prefs); ?></a> (UK)<br><br></li>
				           		<li><p><span class="col2"><i class="fas fa-at"></i></span>&nbsp;&nbsp; <a href="mailto:<?php echo getEmail($prefs); ?>"><?php echo getEmail($prefs); ?></a></p></li>
							-->
            			</ul>
            		</div>
            		<div class="col-md-6">
            			<div class="row">
            				<div class="col-sm-6">
            					<ul class="phone">
                                    <li><span class="col2"><i class="fas fa-phone-alt"></i></span>&nbsp;&nbsp; <a href="tel:<?php echo getTel1Dial($cmsPrefs); ?>"><?php echo getTel1Display($cmsPrefs); ?></a> </li>
                                    <li><span class="col2"><i class="fas fa-phone-alt"></i></span>&nbsp;&nbsp; <a href="tel:<?php echo getTel2Dial($cmsPrefs); ?>"><?php echo getTel2Display($cmsPrefs); ?></a> </li>
				                    <li><span class="col2"><i class="fas fa-at"></i></span>&nbsp;&nbsp; <a class="footer-contact-email" href="mailto:<?php echo getEmail($cmsPrefs); ?>"><?php echo getEmail($cmsPrefs); ?></a></li>

            					</ul>
            				</div>
            				<div class="col-sm-6 social">
                                <!--
            					<ul>
            						<li><a href="#" target="_blank"><img src="<?php echo $baseURL ;?>/images/fac.png"></a></li>
            						<li><a href="#" target="_blank"><img src="<?php echo $baseURL ;?>/images/in.png"></a></li>
            						<li><a href="#" target="_blank"><img src="<?php echo $baseURL ;?>/images/tw.png"></a></li>
            					</ul>
-->
            				</div>
            			</div>
            			<div class="row">
            				<div class="col-sm-12 social accreditationfooter">
            					<ul>
            						<li>
                                        <a href="#">
                                            <img src="<?php echo $baseURL ;?>/filestore/images/content/fsc-logo.png" class="img-responsive" alt="FSC - the mark of responsible forestry" title="FSC - the mark of responsible forestry- FSC C124905">
                                        </a>
                                    </li>
            						<li>
                                        <a href="#">
                                            <img src="<?php echo $baseURL ;?>/filestore/images/content/pefc-logo.png"class="img-responsive" alt="PEFC - Promoting Sustainable Forest Management"  title="PEFC - Promoting Sustainable Forest Management - PEFC/16-37-1352">
                                        </a>
                                    </li>
            						<li>
                                        <a href="#">
                                            <img src="<?php echo $baseURL ;?>/filestore/images/content/responsible-purchaser-logo.png"class="img-responsive" alt="Responsible Purchaser - Timber Trade Federation" title="Responsible Purchaser - Timber Trade Federation">
                                        </a>
                                    </li>
                                    
            						<li>
                                        <a href="#" target="_blank">
                                            <img src="<?php echo $baseURL ;?>/filestore/images/content/TTFederation.png" class="img-responsive" alt="Member of the Timber Trade Federation">
                                        </a>
                                    </li>
            					</ul>
            				</div>
            			</div>
            		</div>
            	</div>


                <div class="row imprint">
                    <div class="col-sm-6 ">
                        <?php
                        $copyrightStartYear = (string) ($cmsPrefs["prefCopyrightStartYear"] ?? '');
                        $copyrightCurrentYear = date('Y');
                        $copyrightYears = $copyrightCurrentYear;
                        if ($copyrightStartYear !== '' && $copyrightStartYear !== $copyrightCurrentYear) {
                            $copyrightYears = $copyrightStartYear . " - " . $copyrightCurrentYear;
                        }
                        ?>
                        <p class="footerimprint">&copy; Copyright <?php echo $copyrightYears . " " . getCompanyName($cmsPrefs) ; ?><br><a href='<?php echo $baseURL ;?>/privacy' target='_blank'>Privacy</a> | <a href='<?php echo $baseURL ;?>/site-policy' target='_blank'>T&Cs</a><br>...</p>
                    </div>
                    <div class="col-sm-6 footerimprint">
                        <p style="text-align: right">&nbsp;<br><a href='https://digita.agency' target='_blank'>website design, build and hosting by digita.agency</a></p>
                    </div>
                </div>
			<?php
                if ($cmsPrefs['prefShowFooterDebug'] == 'Yes') {
				    include("includes/footer-debug.php");
                }
			?>
            </div>
		</footer>


<!-- END footer.php -->
