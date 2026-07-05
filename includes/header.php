<!-- START header.php -->
<?php
if ($debug == "Yes") {
    echo "<p>Logo file = " . $prefs['prefLogo'] . "</p>" ;
    echo "<p>Logo Path = " . getLogo($prefs) . "</p>" ;
}

$cmsHeaderPrefs = $prefs;
$headerPrefKeys = [
    'prefLogo',
    'prefCompanyName',
    'prefTel1',
    'prefTel2',
    'prefTelIntCode',
    'prefEmail'
];
foreach ($headerPrefKeys as $key) {
    $cmsHeaderPrefs[$key] = cms_pref($key, $cmsHeaderPrefs[$key] ?? '');
}

$req_url=$_SERVER['REQUEST_URI']; 
if($req_url=='/welcome?url=index.php'){
    echo "<script>window.location.href='https://www.mstimber.com/welcome'</script>";
}
//if(isset(mysqli_real_escape_string($_GET['url'])) && mysqli_real_escape_string($_GET['url'])=='index.php'){
// header('location:https://www.mstimber.com/welcome');
//} 
?> 
<style>
    .logo_fabic img {
        max-height:80px;
    }
</style>

<!--  H E A D E R   S E C T I O N A  -->
<div class="top_header">
    <div class="container inner">
        <div class="row">
            <div class="col-md-3 col-xs-5"> <!-- LOGO -->
                <div class="logo">
                    <a href="<?php echo $baseURL ;?>/welcome">

                        <img src="<?php echo $baseURL . "/" . getLogo($cmsHeaderPrefs) ; ?>" title="<?php echo getCompanyName($cmsHeaderPrefs) ; ?>" alt="<?php echo getCompanyName($cmsHeaderPrefs) ; ?>" class="img-responsive logo"> 

                        <!--<img src="<?php echo $baseURL ;?>/images/logo.jpg" alt="logo" title="manual logo"> -->           		
                    </a>
                </div>
            </div>
            <div class="col-md-9 col-xs-7 header-tel-area">
                <div class="row">
                    <div class="col-md-8 col-sm-12 desktop"> <!-- LOGOS -->
                        <div class="logo_fabic text-center"> 
                            <a href="#">
                                <img src="<?php echo $baseURL ;?>/filestore/images/content/fsc-logo.png" class="img-responsive" alt="FSC - the mark of responsible forestry" title="FSC - the mark of responsible forestry- FSC C124905">
                            </a>
                            <a href="#">
                                <img src="<?php echo $baseURL ;?>/filestore/images/content/pefc-logo.png"class="img-responsive" alt="PEFC - Promoting Sustainable Forest Management"  title="PEFC - Promoting Sustainable Forest Management - PEFC/16-37-1352">
                            </a>
                            <a href="#">
                                <img src="<?php echo $baseURL ;?>/filestore/images/content/responsible-purchaser-logo.png"class="img-responsive" alt="Responsible Purchaser - Timber Trade Federation" title="Responsible Purchaser - Timber Trade Federation">
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 header-numbers"> <!-- TELE Nunbers -->
                        <div class="info_details">
                            <ul class="text-right header-tel">
                                <li><p class="header-tel"><span class="col2"><i class="fas fa-phone-alt"></i></span>&nbsp;&nbsp; <a href="tel:<?php echo getTel1Dial($cmsHeaderPrefs); ?>"><?php echo getTel1Display($cmsHeaderPrefs); ?></a> (UK)</p></li>
                                <li><p class="header-tel"><span class="col2"><i class="fas fa-phone-alt"></i></span>&nbsp;&nbsp; <a href="tel:<?php echo getTel2Dial($cmsHeaderPrefs); ?>"><?php echo getTel2Display($cmsHeaderPrefs); ?></a> (RoI)</p></li>
                                <li><p class="header-tel"><span class="col2"><i class="fas fa-at"></i></span>&nbsp;&nbsp; <a href="mailto:<?php echo getEmail($cmsHeaderPrefs); ?>"><?php echo getEmail($cmsHeaderPrefs); ?></a></p></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!--<End Row -->
    </div>
</div>

</header>

<!-- END header.php -->
