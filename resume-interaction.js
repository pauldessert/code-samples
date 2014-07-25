
	function addSkills(){
		var ids = $("input.uniqueSkillId").map(function() { 
			return parseInt($(this).val(), 10); 
			}); 
			
			if(Math.max.apply(Math, ids) >= 1){
				var currentCount = Math.max.apply(Math, ids) + 1;
				var secondCount = currentCount + 1;
			} else {
				var currentCount = 1;
				var secondCount = 2;
			}

		var newContent = "<p class='dual'><input type='hidden' name='fieldId[]' value='" + currentCount + "' class='uniqueSkillId' /><input type='text' name='skillName[]' placeholder='Skill Name' /><input type='hidden' name='fieldId[]' value='" + secondCount + "' class='uniqueSkillId' /><input type='text' name='skillName[]' placeholder='Skill Name' /></p>";
		$("#skillset").append(newContent); 
		$( ".datepicker" ).datepicker();
	}
	
	function addEdu(){
		var ids = $("input.uniqueId").map(function() { 
			return parseInt($(this).val(), 10); 
			}); 
			
			if(Math.max.apply(Math, ids) >= 1){
				var currentCount = Math.max.apply(Math, ids) + 1;
			} else {
				var currentCount = 1;
			}
		var newEdu = "<div class='push push-20 separator'></div><input type='hidden' name='fieldId[]' value='" + currentCount + "' class='uniqueId' /><p class='dual'><input type='text' name='educationTitle[]' placeholder='Name of School' class='required' data-validation='name' /><input type='text' name='educationDegree[]' placeholder='Degree/Certificate/License' class='required' data-validation='name' /></p><p class='dual'><input type='text' name='educationStartDate[]' placeholder='Start Date' class='datepicker' /><input type='text' name='educationEndDate[]' placeholder='End Date' class='datepicker' /></p><p class='full'><textarea rows='5' cols='50' name='educationDescription[]' placeholder='Description of Education'></textarea></p><p class='full'><input type='text' name='educationHighlight1' placeholder='Highlight 1' /></p><p class='full'><input type='text' name='educationHighlight2' placeholder='Highlight 2' /></p><p class='full'><input type='text' name='educationHighlight3' placeholder='Highlight 3' /></p><p class='full'><input type='text' name='educationHighlight4' placeholder='Highlight 4' /></p>"
		$("#eduHistory").append(newEdu); 
		$( ".datepicker" ).datepicker();
	}
	
	function addJob(){
		var ids = $("input.uniqueWorkId").map(function() { 
			return parseInt($(this).val(), 10); 
			}); 
			
			if(Math.max.apply(Math, ids) >= 1){
				var currentCount = Math.max.apply(Math, ids) + 1;
			} else {
				var currentCount = 1;
			}
		var newJob = "<div class='push push-20 separator'></div><div id='job" + currentCount + "'><input type='hidden' name='fieldId[]' value='" + currentCount + "' class='uniqueWorkId' /><p class='dual'><input type='text' name='positionTitle[]' placeholder='Position Title' class='required' data-validation='name' /><input type='text' name='company[]' placeholder='Comapny' class='required' data-validation='name' /></p><p class='dual'><input type='text' name='jobCity[]' placeholder='Job City' class='required' data-validation='name' /><input type='text' name='jobState[]' placeholder='Job State' class='required' data-validation='name' /></p><p class='dual'><input type='text' name='jobStartDate[]' placeholder='Start Date' class='datepicker required' data-validation='name' /><input type='text' name='jobEndDate[]' placeholder='End Date' class='datepicker required' data-validation='name' /></p><p class='full'><textarea rows='5' cols='50' name='jobDescription[]' placeholder='Job Description'></textarea></p><p class='full'><input type='text' name='jobHighlight1[]' placeholder='Highlight 1' /></p><p class='full'><input type='text' name='jobHighlight2[]' placeholder='Highlight 2' /></p><p class='full'><input type='text' name='jobHighlight3[]' placeholder='Highlight 3' /></p><p class='full'><input type='text' name='jobHighlight4[]' placeholder='Highlight 4' /></p><a href='#' class='deleteJobButton' id='deleteJobButton" + currentCount + "'>Delete</a></div>"
		$("#jobHistory").append(newJob); 
		$( ".datepicker" ).datepicker();
	}
	
	
	//Deletes Job
	$(".deleteJobButton").click(function() {
		var elementId = $(this).parents('div:eq(0)').attr('id');
		var id = elementId.slice(-1);
		var removeElement = "#job" + id;

		  $.ajax({
			type: "POST",
			url: "form_process.php?source=deleteJob",
			data: {userid: <?php echo json_encode($userId); ?>, fieldId: id},
			success: function(){
				$(removeElement).remove();
			}
		  });
		 return false;
	});
	
	//Deletes edu
	$(".deleteEduButton").click(function() {
		var elementId = $(this).parents('div:eq(0)').attr('id');
		var id = elementId.slice(-1);
		var removeElement = "#job" + id;

		  $.ajax({
			type: "POST",
			url: "form_process.php?source=deleteEducation",
			data: {userid: <?php echo json_encode($userId); ?>, fieldId: id},
			success: function(){
				$(removeElement).remove();
			}
		  });
		 return false;
	});
	
	//Deletes Skill
	$(".deleteSkillButton").click(function() {
		var elementId = $(this).parents('div:eq(0)').attr('id');
		var id = elementId.substring(3);
		var removeElement = "#job" + id;

		  $.ajax({
			type: "POST",
			url: "form_process.php?source=deleteSkill",
			data: {userid: <?php echo json_encode($userId); ?>, fieldId: id},
			success: function(){
				$(removeElement).remove();
			}
		  });
		 return false;
	});
	
	$(function() {
		$( ".datepicker" ).datepicker({
		    changeMonth: true,
			changeYear: true
		});
	});
	