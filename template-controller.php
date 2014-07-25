<?php
//session_start();
ob_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/resume_controller.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/mpdf/mpdf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/user_controller.php');
$data = new resume();
$user = new user();
$mpdf = new mPDF();

//Either display the real account info, or the dummy account if not logged in
if(isset($_SESSION['loggedIn'])){
	$thisUsersId = $_SESSION["userId"];
	$currentUserInfo = $user->userDetails($userId); 
} else {
	$thisUsersId = "38"; //Dummy account to disply
}

$userInfo = $user->userDetails($thisUsersId);
$usersObjective = $data->getUserObjective($thisUsersId);
$userEdu = $data->getUserEdu($thisUsersId);
$userWork = $data->getUserWorkHistory($thisUsersId);
$userSkills = $data->getUserSkills($thisUsersId);

//Setting the resume values
//If the user is logged in, either show their info, or ask for input

$userFirstName = (empty($userInfo['firstName'])) ? "Enter Your First Name" : $userInfo['firstName'];
$userLastName = (empty($userInfo['lastName'])) ? "Enter Your Last Name" : $userInfo['lastName'];
$userStreetAddress = (empty($userInfo['streetAddress'])) ? "Enter Your Address" : $userInfo['streetAddress'];
$userCity = (empty($userInfo['city'])) ? "Enter Your City" : $userInfo['city'];
$userState = (empty($userInfo['state'])) ? "Enter Your State" : $userInfo['state'];
$userZip = (empty($userInfo['zip'])) ? "Enter Your Zip" : $userInfo['zip'];
$userPhone1 = (empty($userInfo['phone1'])) ? "Enter Your Phone Number" : $userInfo['phone1'];
$userObjective = $usersObjective;
$userDegree = $edu['educationDegree'];


//require_once($_SERVER['DOCUMENT_ROOT'] . '/templates/resume1.php');
$output = ob_get_clean();


if($_GET['makePDF'] == TRUE){

	$style = '<style>
	@page *{
		margin-top: 2.54cm;
		margin-bottom: 2.54cm;
		margin-left: 3.175cm;
		margin-right: 3.175cm;
	}
	</style>';
	
	$mpdf->WriteHTML($style); //This writes the margin styles
	$stylesheet = file_get_contents('../templates/resume1.css');
	$mpdf->WriteHTML($stylesheet,1);
	$mpdf->WriteHTML($output,2);
	
	//These are here for use with PHPmailer script below. Not currently working.
	//$emailAttachment = $mpdf->Output('','S');
	//$emailAttachment = chunk_split(base64_encode($emailAttachment));
	
	//This works, but does not utilize PHPmailer.
	$content = $mpdf->Output('', 'S');
	$content = chunk_split(base64_encode($content));
	$mailto = $currentUserInfo['emailAddress'];
	$from_name = 'ResumeBeacon.com';
	$from_mail = 'marketing@resumebeacon.com';
	$replyto = 'noreply@resumebeacon.com';
	$uid = md5(uniqid(time())); 
	$subject = 'Your Resume from ResumeBeacon.com';
	$message = 'Thank you for using ResumeBeacon.com. Your resume is attached to this email. You will need Adobe Acrobat Reader in order to view the file. 
				Acrobat Reader is free software and is available for free at http://get.adobe.com/reader/';
	$filename = 'YourResume.pdf';

	$header = "From: ".$from_name." <".$from_mail.">\r\n";
	$header .= "Reply-To: ".$replyto."\r\n";
	$header .= "MIME-Version: 1.0\r\n";
	$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
	$header .= "This is a multi-part message in MIME format.\r\n";
	$header .= "--".$uid."\r\n";
	$header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
	$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	$header .= $message."\r\n\r\n";
	$header .= "--".$uid."\r\n";
	$header .= "Content-Type: application/pdf; name=\"".$filename."\"\r\n";
	$header .= "Content-Transfer-Encoding: base64\r\n";
	$header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
	$header .= $content."\r\n\r\n";
	$header .= "--".$uid."--";
	$is_sent = @mail($mailto, $subject, "", $header);

	$mpdf->Output();
	exit; 
	
	} else {
	//This should be user initiated, not sent by default.
		//$data->emailUserResume($_SESSION["userId"]);
		//echo $output;
	}

?>

	
</div><!-- .blog-posts -->
</section><!-- .block -->

<script type="text/javascript" src="/temaples/inc/js/resumeFlex.js"></script>