<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');

//This controls all interations with the resume. basic info (name, phone number etc. is pulled from the user controller
class resume {
	
    public function __construct(){
        //establish a connection with db
		$this->dbh = new PDO('mysql:host=localhost;dbname=xxxxxxx', 'xxxxxxxx', 'xxxxxxxx');
    }

	
	//Create the users objective
	public function createUserObjective($userId, $objective){
		$updates = array(
		'userId'  	 			=> $userId,
		'objective'   	 		=> $objective
	);
	
	$updates = array_filter($updates, 'strlen'); //Removes any NULL
	
	//Check here to see if db contains existing data. If so, update, else insert
	
	    $sth = $this->dbh->prepare("SELECT objective FROM objective WHERE userId = :userId"); 
		$sth->bindParam(":userId",$userId);
        $sth->execute();
        $result = $sth->fetchColumn();
		
		//If new, Insert
		if (empty($result)){
			$query = 'INSERT INTO objective SET';
			$values = array();

			foreach ($updates as $name => $value) {
				$query .= ' '.$name.' = :'.$name.','; // the :$name part is the placeholder, e.g. :zip
				$values[':'.$name] = $value; // save the placeholder
			}

			$query = substr($query, 0, -1).';'; // remove last , and add a ;
			$sth = $this->dbh->prepare($query);
			if($sth->execute($values)){ // bind placeholder array to the query and execute everything
				//$_SESSION["userId"] = $this->dbh->lastInsertId();
			} else {
				print_r($sth->errorInfo());
			} 
			
		//If updating, UPDATE
		} else {
			$sth = $this->dbh->prepare("UPDATE objective SET objective = :objective WHERE userId = :userId"); 
			$sth->bindParam(":userId",$userId);
			$sth->bindParam(":objective",$objective);
			RETURN $sth->execute();
			//$result = $sth->fetchColumn()
		}
	}
	
	
	//Create a new user education profile
	public function createUserEducation($userId, $fieldId, $educationTitle, $educationDegree, $educationStartDate, $educationEndDate, $educationDescription, 
										$educationHighlight1, $educationHighlight2, $educationHighlight3, $educationHighlight4){
	
	$dates = array(
		'startDate' 			=> $educationStartDate,
		'endDate' 				=> $educationEndDate
	);
	
	//Coverts date format to insert into MySQL
	$startDateConverted = array();
	
	foreach($dates['startDate'] as $startDate){
		$startDateConverted[] = date('Y-m-d', strtotime($startDate));
	}
	
	//Coverts date format to insert into MySQL
	$endDateConverted = array();
	
	foreach($dates['endDate'] as $endDate){
		$endDateConverted[] = date('Y-m-d', strtotime($endDate));
	}

		$submittedChanges = array(
		'userId'  	 			=> $userId,
		'fieldId'				=> $fieldId,
		'educationTitle'   	 	=> $educationTitle,
		'educationDegree'    	=> $educationDegree,
		'startDate' 			=> $startDateConverted,
		'endDate' 				=> $endDateConverted,
		'educationDescription' 	=> $educationDescription,
		'educationHighlight1' 	=> $educationHighlight1,
		'educationHighlight2' 	=> $educationHighlight2,
		'educationHighlight3' 	=> $educationHighlight3,
		'educationHighlight4' 	=> $educationHighlight4
	);
	
	

	//Previously saved data from database
	$existingEdu = $this->getUserEdu($userId);

	//Pulling fieldId from database values in order to compare against submitted data
	$compare = array();
	foreach($existingEdu as $field){
		$compare[] = $field['fieldId'];
	}
	
	//Finds matching fieldId in database vs submitted data
	$matches = array_intersect($compare, $submittedChanges['fieldId']);
	
	
	//This compares $matches against the users data. If a match is found, it adds it to $eduUpdate else it's added to $eduAdd
	//We want to update any fieldId that exists and add a new record if the fieldId does not exist
	$eduAdd = array();
	$eduUpdate = array();
	$itodel = array();
	
	foreach($submittedChanges['fieldId'] as $i => $v){ 
		if(isset($matches[$i]) and $matches[$i] == $v){
			$itodel[] = $i;
		}
	}	

	foreach ($submittedChanges as $key => $arr) {
		if (!is_array($arr)) continue;
		foreach ($arr as $i => $v) {
			if (in_array($i, $itodel))
				$eduUpdate[$key][$i] = $v;
			else
				$eduAdd[$key][$i] = $v;
		}
	}
		$eduAdd = array_map('array_values', $eduAdd);
		
		//Adding the userId since the loop filtered it out
		$eduAdd['userId'] = $userId;
		
		// echo "<pre>";
		// echo "adding";
		// print_r($eduAdd);
		// echo "</pre>";
		
	if(!empty($eduAdd)){
	
		$count = count($eduAdd['educationTitle']);
		$idx = 0;
		
		for($i = 1; $i<=$count; $i++){
		
			$query = 'INSERT INTO education SET';
			$values = array();

			foreach ($eduAdd as $name => $value) {
				if(is_array($value)) {
					$query .= ' '.$name.' = :'.$name.','; // the :$name part is the placeholder, e.g. :zip
					$values[':'.$name] = $value[$idx]; // save the placeholder and increment
				} else {
					$query .= ' '.$name.' = :'.$name.','; // the :$name part is the placeholder, e.g. :zip
					$values[':'.$name] = $value; // save the userId placeholder so It does not increment
				}
			}
			
			$query = substr($query, 0, -1).';'; // remove last , and add a ;
			
			$sth = $this->dbh->prepare($query);
			if($sth->execute($values)){ // bind placeholder array to the query and execute everything
				//return $this->dbh->lastInsertId();
				//return TRUE;
			} else {
				print_r($sth->errorInfo());
			} 
			$idx++;
		}
	}

		if(!empty($eduUpdate)){
	
		$count = count($eduUpdate['educationTitle']);
		$idx = 0;
		
			for($i = 1; $i<=$count; $i++){
			
				$query = 'UPDATE education SET';
				$values = array();
				$counter = 0;
				
				foreach ($eduUpdate as $name => $value) {
					if(is_array($value)) {
						$query .= ' '.$name.' = :'.$name; //.','; // the :$name part is the placeholder, e.g. :zip
							$counter++;
								if ($counter < count($eduUpdate)) {
									$query .= ',';
								}
						$values[':'.$name] = $value[$idx]; // save the placeholder and increment
						$values['userId'] = $userId;
					} else {
						$query .= ' '.$name.' = :'.$name; //  .','; // the :$name part is the placeholder, e.g. :zip
							$counter++;
								if ($counter <= count($eduUpdate)) {
									$query .= ',';
								}
						$values[':'.$name] = $value; // save the userId placeholder so It does not increment
						$values['userId'] = $userId;
					}
					
				}
				
				$query .= ' WHERE userId = :userId AND fieldId = :fieldId;';
				
				$sth = $this->dbh->prepare($query);
				if($sth->execute($values)){ // bind placeholder array to the query and execute everything
					$sth->bindParam(":userId",$userId);
					
					//return $this->dbh->lastInsertId();
					//return TRUE;
				} else {

					print_r($sth->errorInfo());
				} 
				$idx++;
			}
		}
	}
	
	public function deleteUserEducation($userId, $fieldId){
		$sth = $this->dbh->prepare("DELETE FROM education WHERE userId = :userId and fieldId = :fieldId"); 
		$sth->bindParam(":userId",$userId);
		$sth->bindParam(":fieldId",$fieldId);
			if($sth->execute()){
				return TRUE;
			} else {
				return FALSE;
			}
		//$result = $sth->fetchColumn();
	}
	
	//Create a new user work profile
	public function createUserWork($userId, $fieldId, $positionTitle, $company, $jobDescription, $jobHighlight1, $jobHighlight2, $jobHighlight3, 
									$jobHighlight4, $jobStartDate, $jobEndDate, $jobCity, $jobState){ 
	
	$dates = array(
		'startDate' 			=> $jobStartDate,
		'endDate' 				=> $jobEndDate
	);
	
	//Coverts date format to insert into MySQL
	$startDateConverted = array();
	
	foreach($dates['startDate'] as $startDate){
		$startDateConverted[] = date('Y-m-d', strtotime($startDate));
	}
	
	//Coverts date format to insert into MySQL
	$endDateConverted = array();
	
	foreach($dates['endDate'] as $endDate){
		$endDateConverted[] = date('Y-m-d', strtotime($endDate));
	}
	
		$updates = array(
		'userId'  	 			=> $userId,
		'fieldId'  	 			=> $fieldId,
		'positionTitle'   	 	=> $positionTitle,
		'company'				=> $company,
		'jobDescription'		=> $jobDescription,
		'jobHighlight1'			=> $jobHighlight1,
		'jobHighlight2'			=> $jobHighlight2,
		'jobHighlight3'			=> $jobHighlight3,
		'jobHighlight4'			=> $jobHighlight4,
		'startDate'    			=> $startDateConverted,
		'endDate' 				=> $endDateConverted,
		'jobCity' 				=> $jobCity,
		'jobState' 				=> $jobState
	);
	
	
	//Previously saved data from database
	$existingJobs = $this->getUserWorkHistory($userId);
	
	
	//Pulling fieldId from database values in order to compare against submitted data
	$compare = array();
	foreach($existingJobs as $field){
		$compare[] = $field['fieldId'];
	}
	
	//Finds matching fieldId in database vs submitted data
	$matches = array_intersect($compare, $updates['fieldId']);
	
	//This compares $matches against the users data. If a match is found, it adds it to $jobUpdate else it's added to $jobAdd
	//We want to update any fieldId that exists and add a new record if the fieldId does not exist
	$jobAdd = array();
	$jobUpdate = array();
	$itodel = array();
	
	foreach($updates['fieldId'] as $i => $v){ 
		if(isset($matches[$i]) and $matches[$i] == $v){
			$itodel[] = $i;
		}
	}	

	foreach ($updates as $key => $arr) {
		if (!is_array($arr)) continue;
		foreach ($arr as $i => $v) {
			if (in_array($i, $itodel))
				$jobUpdate[$key][$i] = $v;
			else
				$jobAdd[$key][$i] = $v;
		}
	}
		$jobAdd = array_map('array_values', $jobAdd);
		
		//Adding the userId sine the loop filtered it out
		$jobAdd['userId'] = $userId;
		
		
	if(!empty($jobAdd)){
	
		$count = count($jobAdd['positionTitle']);
		$idx = 0;
		
		for($i = 1; $i<=$count; $i++){
		
			$query = 'INSERT INTO jobhistory SET';
			$values = array();

			foreach ($jobAdd as $name => $value) {
				if(is_array($value)) {
					$query .= ' '.$name.' = :'.$name.','; // the :$name part is the placeholder, e.g. :zip
					$values[':'.$name] = $value[$idx]; // save the placeholder and increment
				} else {
					$query .= ' '.$name.' = :'.$name.','; // the :$name part is the placeholder, e.g. :zip
					$values[':'.$name] = $value; // save the userId placeholder so It does not increment
				}
			}
			
			$query = substr($query, 0, -1).';'; // remove last , and add a ;
			
			$sth = $this->dbh->prepare($query);
			if($sth->execute($values)){ // bind placeholder array to the query and execute everything
				//return $this->dbh->lastInsertId();
				//return TRUE;
			} else {
				print_r($sth->errorInfo());
			} 
			$idx++;
		}
	}
	

		if(!empty($jobUpdate)){
	
		$count = count($jobUpdate['positionTitle']);
		$idx = 0;
		
			for($i = 1; $i<=$count; $i++){
			
				$query = 'UPDATE jobhistory SET';
				$values = array();
				$counter = 0;
				
				foreach ($jobUpdate as $name => $value) {
					if(is_array($value)) {
						$query .= ' '.$name.' = :'.$name; // the :$name part is the placeholder, e.g. :zip
							$counter++;
								if ($counter < count($jobUpdate)) { // Add a comma after each except for last item
									$query .= ',';
								}
						$values[':'.$name] = $value[$idx]; // save the placeholder and increment
						$values['userId'] = $userId;
					} else {
						$query .= ' '.$name.' = :'.$name; // the :$name part is the placeholder, e.g. :zip
							$counter++;
								if ($counter <= count($jobUpdate)) { // Add a comma after each except for last item
									$query .= ',';
								}
						$values[':'.$name] = $value; // save the userId placeholder so It does not increment
						$values['userId'] = $userId;
					}
					
				}
				
				$query .= ' WHERE userId = :userId AND fieldId = :fieldId;';
				
				$sth = $this->dbh->prepare($query);
				if($sth->execute($values)){ // bind placeholder array to the query and execute everything
					$sth->bindParam(":userId",$userId);

				} else {
					print_r($sth->errorInfo());
				} 
				$idx++;
			}
		}
	}
	
	public function deleteUserJob($userId, $fieldId){
		$sth = $this->dbh->prepare("DELETE FROM jobhistory WHERE userId = :userId and fieldId = :fieldId"); 
		$sth->bindParam(":userId",$userId);
		$sth->bindParam(":fieldId",$fieldId);
			if($sth->execute()){
				return TRUE;
			} else {
				return FALSE;
			}
		//$result = $sth->fetchColumn();
	}
	
	//Create a new user skill profile
	public function createUserSkills($userId, $fieldId, $skillName){
	
	//$filteredSkillName = array_filter($skillName, 'strlen'); //Removes any NULL, FALSE and Empty Strings but leaves 0 values
	
		$updates = array(
		'userId'  	 			=> $userId,
		'fieldId'  	 			=> $fieldId,
		'skillName'   	 		=> $skillName
		);
		
	
	//Previously saved data from database
	$existingSkills = $this->getUserSkills($userId);
	
	//Pulling fieldId from database values in order to compare against submitted data
	$compare = array();
	foreach($existingSkills as $field){
		$compare[] = $field['fieldId'];
	}
	
	//Finds matching fieldId in database vs submitted data
	$matches = array_intersect($compare, $updates['fieldId']);
	
	//This compares $matches against the users data. If a match is found, it adds it to $skillUpdate else it's added to $skillAdd
	//We want to update any fieldId that exists and add a new record if the fieldId does not exist
	$skillAdd = array();
	$skillUpdate = array();
	$itodel = array();
	
	foreach($updates['fieldId'] as $i => $v){ 
		if(isset($matches[$i]) and $matches[$i] == $v){
			$itodel[] = $i;
		}
	}	

	foreach ($updates as $key => $arr) {
		if (!is_array($arr)) continue;
		foreach ($arr as $i => $v) {
			if (in_array($i, $itodel))
				$skillUpdate[$key][$i] = $v;
			else
				$skillAdd[$key][$i] = $v;
		}
	}
		$skillAdd = array_map('array_values', $skillAdd);
		
		//Adding the userId sine the loop filtered it out
		$skillAdd['userId'] = $userId;	
		
	if(!empty($skillAdd)){
	
		$count = count($skillAdd['skillName']);
		$idx = 0;
		
		for($i = 1; $i<=$count; $i++){
		
			$query = 'INSERT INTO userhasskills SET';
			$values = array();

			foreach ($skillAdd as $name => $value) {
				if(is_array($value)) {
					$query .= ' '.$name.' = :'.$name.','; // the :$name part is the placeholder, e.g. :zip
					$values[':'.$name] = $value[$idx]; // save the placeholder and increment
				} else {
					$query .= ' '.$name.' = :'.$name.','; // the :$name part is the placeholder, e.g. :zip
					$values[':'.$name] = $value; // save the userId placeholder so It does not increment
				}
			}
			
			$query = substr($query, 0, -1).';'; // remove last , and add a ;
			
			$sth = $this->dbh->prepare($query);
			if($sth->execute($values)){ // bind placeholder array to the query and execute everything
				//return $this->dbh->lastInsertId();
				//return TRUE;
			} else {
				print_r($sth->errorInfo());
			} 
			$idx++;
		}
	}
	
	

		if(!empty($skillUpdate)){
	
		$count = count($skillUpdate['skillName']);
		$idx = 0;
		
		for($i = 1; $i<=$count; $i++){
		
			$query = 'UPDATE userhasskills SET';
			$values = array();
			$counter = 0;
			
			foreach ($skillUpdate as $name => $value) {
				if(is_array($value)) {
					$query .= ' '.$name.' = :'.$name; // the :$name part is the placeholder, e.g. :zip
						$counter++;
							if ($counter < count($skillUpdate)) { // Add a comma after each except for last item
								$query .= ',';
							}
					$values[':'.$name] = $value[$idx]; // save the placeholder and increment
					$values['userId'] = $userId;
				} else {
					$query .= ' '.$name.' = :'.$name; // the :$name part is the placeholder, e.g. :zip
						$counter++;
							if ($counter <= count($skillUpdate)) { // Add a comma after each except for last item
								$query .= ',';
							}
					$values[':'.$name] = $value; // save the userId placeholder so It does not increment
					$values['userId'] = $userId;
				}
				
			}
			
			$query .= ' WHERE userId = :userId AND fieldId = :fieldId;';
			
			$sth = $this->dbh->prepare($query);
			if($sth->execute($values)){ // bind placeholder array to the query and execute everything
				$sth->bindParam(":userId",$userId);

			} else {
				print_r($sth->errorInfo());
			} 
			$idx++;
		}
	}
		
	}
	
	public function deleteUserSkill($userId, $fieldId){
		$sth = $this->dbh->prepare("DELETE FROM userhasskills WHERE userId = :userId and fieldId = :fieldId"); 
		$sth->bindParam(":userId",$userId);
		$sth->bindParam(":fieldId",$fieldId);
			if($sth->execute()){
				return TRUE;
			} else {
				return FALSE;
			}
	}
	
	//Return Results...
	//Return the users objective statement
	public function getUserObjective($userId){
	if($userId){
        $sth = $this->dbh->prepare("SELECT objective FROM objective WHERE userId = :userId"); 
		$sth->bindParam(":userId",$userId);
        $sth->execute();
        $result = $sth->fetchColumn();
        return $result; 
		} else {
			return NULL;
		}
	}
	
	//Return the users education profile
	public function getUserEdu($userId){
	if($userId){
        $sth = $this->dbh->prepare("SELECT * FROM education WHERE userId = :userId"); 
		$sth->bindParam(":userId",$userId);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result; 
		} else {
			return NULL;
		}
	}
	
	//Return the users work profile
	public function getUserWorkHistory($userId){
	if($userId){
        $sth = $this->dbh->prepare("SELECT * FROM jobhistory WHERE userId = :userId"); 
		$sth->bindParam(":userId",$userId);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result; 
		} else {
			return NULL;
		}
	}
	
	//Return the users skills
	public function getUserSkills($userId){
	if($userId){
        $sth = $this->dbh->prepare("SELECT * FROM userhasskills WHERE userId = :userId"); 
		$sth->bindParam(":userId",$userId);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result; 
		} else {
			return NULL;
		}
	}
	
	//Email the user a PDF of their resume using PHPMailer
	public function emailUserResume($userId){
	
	require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/user_controller.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/PHPMailer/class.phpmailer.php');
	$user = new user();
	$mail = new PHPMailer(true); //defaults to using php "mail()"; the true param means it will throw exceptions on errors, which we need to catch


		
		$currentUserInfo = $user->userDetails($userId);
		
		try {
		  $mail->AddAddress($currentUserInfo['emailAddress'], $currentUserInfo['firstName']);
		  $mail->SetFrom('name@yourdomain.com', 'First Last');
		  $mail->AddReplyTo('name@yourdomain.com', 'First Last');
		  $mail->Subject = 'Your Resume from ResumeBeacon.com';
		  $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
		  $mail->MsgHTML("This is a test");
		  //$mail->MsgHTML(file_get_contents('contents.html'));
		  //$mail->AddAttachment('images/phpmailer.gif');      // attachment
		  //$mail->AddAttachment('images/phpmailer_mini.gif'); // attachment
		  $mail->Send();
		  echo "Message Sent OK</p>\n";
		} catch (phpmailerException $e) {
		  echo $e->errorMessage(); //Pretty error messages from PHPMailer
		} catch (Exception $e) {
		  echo $e->getMessage(); //Boring error messages from anything else!
		}
	}
}

