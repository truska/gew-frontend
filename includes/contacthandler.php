<!-- START contacthandler.php DP-->
<div class="container">

    <style>
        .contact-response {
            background-color: transparent;
            color: #1a1a1a;
            margin-left:auto;
            margin-right:auto;
            margin-top:100px;
            margin-bottom:200px;
        }
    </style>

    <section class="row contact-response" style="">
        <div class="col-lg-10 col-lg-offset-1 body-area-top">

            <div class="row">

                <div class="col-xs-12 col-sm-12 col-md-12 contacthandler">
                    <div class="row">
                        <div class="contacthandler">

                        <!--	<p>Form Processing</p> -->

                        <?php
                        /*Validating variables*/
                        $realname = mysqli_real_escape_string($conn, $_POST['realname']);
                        $Company = mysqli_real_escape_string($conn, $_POST['Company']);
                        $email = mysqli_real_escape_string($conn, $_POST['email']);
                        $tele = mysqli_real_escape_string($conn, $_POST['tele']);
                        $county = mysqli_real_escape_string($conn, $_POST['county']);
                        $message = mysqli_real_escape_string($conn, $_POST['message']);

                        $sourcepage = $_POST["sourcepage"] ;
                        $sourcepage = $_POST['referringpage'] ;
                        $subject = "Web Enquiry - " . $realname . " ***" ;
                        $today = date("D M j Y G:i:s T");               // Sat Mar 10 17:16:18 MST 2001
                        $pagename = $_SERVER["PHP_SELF"] . " - " .  $_SERVER["REQUEST_URI"] . "" ;
                        $iplocation = $_SERVER['REMOTE_ADDR'] ;

                                
                            // Check for value in test field - if blank carry on
                                    if(isset($_POST['g-recaptcha-response'])){
                                      $captcha=$_POST['g-recaptcha-response'];
                                    }

                                    // Captcha 3 Integrated
                                    $url = "https://www.google.com/recaptcha/api/siteverify";
                                    $data = [
                                        'secret' =>  $prefs['prefCaptchaSecretKeyV3'] ,
                                        'response' => $_POST['token'],
                                        'remoteip' =>  $_SERVER['REMOTE_ADDR']
                                    ];

                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content'=> http_build_query($data)
                                        )
                                    );
                                    $context = stream_context_create($options);
                                    $response = file_get_contents($url, false, $context);
                                    $res = json_decode($response, true);
                                    if($res['success'] == true || $captcha != '' || $_POST['prefCaptcha'] != '')
                                    {
                                        
                                            if ($_POST['type'] == 'doprequest') {
                                                $subject = "REQUEST for Tech Data " . $_POST['product'] . "- " . $realname . " MST" ;
                                            }
                                            else
                                            {
                                                $subject = "Web Enquiry - " . $realname . " MST" ;
                                            }
                                        
                                        
                                            $today = date("D M j Y G:i:s T");               // Sat Mar 10 17:16:18 MST 2001
                                            $pagename = $_SERVER["PHP_SELF"] . " - " .  $_SERVER["REQUEST_URI"] . "" ;
                                            $iplocation = $_SERVER['REMOTE_ADDR'] ;

                                            if($_POST['prefCaptcha']=='yes')
                                            {
                                                mysqli_query($conn,"INSERT INTO contactforms (email,realname,company,tele,county,message,referred,sourcepage,ip,url) VALUES ('".$email."','".$realname."','".$Company."','".$tele."','".$county."','".$message."','".$_POST['referred']."','".$_POST['sourcepage']."','".$iplocation."','".$_SERVER['SERVER_NAME']."')");
                                            }

                                            if($captcha)
                                            {   
                                                $response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$prefs['prefCaptchaSecretKey']."&response=".$captcha."&remoteip=".$ip);
                                                $responseKeys = json_decode($response,true);

                                                if(intval($responseKeys["success"]) !== 1) {
                                                    echo '<h2>You look like a spammer ! Please leave</h2>';
                                                    exit;
                                                }
                                            }
                                          
                                            //	$to = stripslashes($rowmaster["email"]) ;
                                            //    $to = 'info@truska.com' ;
                                                $to = 'sales@mstimber.com' ;

                                                  $message = 'Name: '.$realname."\n"
                                                    .'Email From:   '.$email."\n"
                                                    .'Company:      '.$Company."\n"   
                                                    .'Telephone:    '.$tele."\n"   
                                                    .'Location:     '.$county."\n"
                                                    .'Message:      '.$message."\n\n"
                                                    .'Referred:     '.$_POST['referred']."\n"
                                                    .'Source:       '.$_POST['sourcepage']."\n"
                                                    .'Posted:       '.$today."\n\n"
                                                    .'From Page:    '.$pagename."\n"
                                                    .'From IP:      '.$iplocation."\n"
                                                    .'From URL:     '.$_SERVER['SERVER_NAME']."\n";
                                                
                                                
                                                
                                                  
                                                
                                                //	.'Slug:         '.$_POST['slugpage']."\n"
                                            //     .'Referring:    '.$_POST['referringpage']."\n"

                                            $headers .= 'From: ' . $realname . '<' . $email . '>' . "\r\n";
                                            $headers .= 'Cc:' . "\r\n";
                                            $headers .= 'Bcc:' . "clients@truska.com";

                                            mail($to, $subject, $message, $headers) ;

                                            echo "<h2 style='font-size:30px; padding-top:100px;'>Thank you for your enquiry - we will contact you shortly.</h2>";
                                            echo "<p>&nbsp;</p><p>You will be returned to the page you submitted the form from in a few seconds.</p>" ;
                                            // echo "<p>Form sent to " . $to . "</p>" ;

                                            // echo "debug section<br>" ;
                                            // echo "mail(" . $to . "," . $subject. "," . $message. "," . $headers . ")<br>" ;


                                            $delay = 3 ;
                                            $url = $baseURL ."/" . $_POST["sourcepage"] . "" ;
                                            echo "<meta http-equiv=\"Refresh\" content=\"" . $delay . "; URL=" . $url . "\">";
                                        
                                       
                                    }
                                    else
                                    {
                                        echo "<h3 style='padding-top:30px; padding-bottom:30px;'>Captcha is invalid.<br><a href='" . $baseURL . "/" . $_POST["sourcepage"] . "'>Back to Contact Form</a></h3>";
                                              exit;
                                    }
                                    /*echo "<pre>";
                                    print_r($response);*/
                                    


                        ?>

                        </div> <!-- End class contacthandler -->
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- END contacthandler.php -->