(function() {

	var loadedCampuses = 0, loadedCourses = 0;

	var courseFrames = 5;

	var campuses, campusSize, courses;

	var totalCourses = 0;
	var courseRoundRobin = 0;

	var activeFrames = [];
	for (var i=0; i< courseFrames; i++) activeFrames[i] = i;

		var eWindow;

	var userAgent = window.navigator.userAgent;
	var ie = (userAgent.indexOf("MSIE ") == -1 && userAgent.indexOf('Trident/') == -1) ? false : true;

	$(document).ready(function() {

		autoTemplate.bind("courses", "campuses");

		//If user is already authenticated, hide login process and get the enrollments
		if(sessionStorage["userSession"] != null && sessionStorage["userSession"].trim() != "") {

			bypassLogin();
			return;
		}

		$(".courseLogin").show(); 
		
	});

	function bypassLogin() {
		$(".courseLogin").hide(); 
		$(".courseList").show();
		$(".desync").show();
		getCampusData(function() {
			getEnrollments(); 

		}); 
	}

	$(document).on("click", ".bypass", bypassLogin);

	function getCampusData(callback) {

		$.ajax({

			type: "GET",
			url: site_path + "/home/getData/",
			data: { },
			dataType: "json"

		}).done(function(data) {

			campuses = data['campuses']; 
			campusSize = data['campuses'].length;
			courses = data['courses'];

			if(typeof callback == "function") {
				callback();
			}
		});

	}


	//Load a new frame, attach it to a destination and bind a callback
	function loadFrame(frame, destination, callback) {

		var newFrame = $(frame);

		if(destination) {
			$(destination).append(newFrame);
		}

		$(newFrame).unbind('load');
		$(newFrame).load(callback);

	}

	//Start the load course chain with course id 0
	function loadCourses(callback) {

		totalCourses = courses.length;
		loadCourse(0, callback);

	}

	//Load a singular course based on an offset into the course list, callback will be called when all courses load
	function loadCourse(courseOffset, callback) 
	{
		//Delay if all frames are being used
		if(activeFrames.length == 0) {
			setTimeout(function() {    
				loadCourse(courseOffset, callback);  
			}, 500);

			return; 
		}

		//Call the callback when all courses have finished loading
		if(courseOffset >= totalCourses) {

			if(typeof callback == "function") {
				callback();
			}
			return;
		}  

		var randomInt = Math.floor(Math.random() * (activeFrames.length-1));
		var randomFrame = activeFrames[randomInt];

		var courseFrame = $("#courseFrame" + randomFrame);

		var course = courses[courseOffset];

		var campus = $.grep(campuses, function(e){ return e.Code == course.Campus; })[0];

		if(!campus) {
			return loadCourse(parseInt(courseOffset)+1, callback);  
		}   
		activeFrames.splice(randomInt, 1);

		$(courseFrame).attr("src", campus.src +"/d2l/home/" + course.OrgID);

		//Return used frame to the pool
		loadFrame($(courseFrame), null, function() {

			activeFrames.push(randomFrame);

		});

		//Load next course
		setTimeout(function() {    
			loadCourse(parseInt(courseOffset)+1, callback);  
		}, 500);

	}

	$(document).on('click', '.desync', function() {

		deSync();

	});

	function deSync() {

		sessionStorage.removeItem("userSession");
		$(".courses").html("");
		$(".courseList").hide();
		$(".courseLogin").show();
		$(".desync").hide();

	}

	function getEnrollments() {

		$.ajax({
			type: "GET",
			url: site_path + "/home/getEnrollments/",
			data: { },
			dataType: "json"
		}).done(function(data) {

			if(data["success"] == 0) {

				$(".noCourses").show();
				$(".dimBackground").show();

			} 
			else 
			{
				if(data["courses"].length == 0) {
					$(".noCourses").show();
				}

				var enrolledCampuses = {};
				for(var i=0;i< data["courses"].length; i++) {

					var course = data["courses"][i].CourseID;

					var courseData = $.grep(courses, function(e){ return e.OrgID == course; })[0];

					var campusData = $.grep(campuses, function(e){ return e.Code == courseData.Campus; })[0];
					
					if(!campusData) {
						continue;
					}

					if(!enrolledCampuses[courseData.Campus]) {
						enrolledCampuses[courseData.Campus] = { "name": campusData.Campus, "emblem": campusData.Emblem, "location": campusData.Location, "src": campusData.src, "code": campusData.Code, "terms": {} };
					}
					if(!enrolledCampuses[courseData.Campus]['terms'][courseData.Term]) {
						enrolledCampuses[courseData.Campus]['terms'][courseData.Term] = { "name": courseData.YearTerm, "courses": [] };
					}

					enrolledCampuses[courseData.Campus]['terms'][courseData.Term]['courses'].push(courseData);

				}

				autoTemplate.update("campuses", enrolledCampuses);


			}

		});


	}

	$("form[name='missingLogin']").submit(function(e) {

		e.preventDefault();

		var formData = { password: $(this).find("input[name='password']").val(), course: $(this).find("input[name='course']").val() };

		$(".missingCourseLogin").find(".field_error").html("");
		if(!formData.password) {
			$(".missingCourseLogin").find(".password_error").html("Please enter a password");
			return;
		}

		$(".missingCourseLogin").hide(); 
		$(".missingMessage").show().html("Please wait...");

		$(".loading").show();
		$(".dimBackground").show();
		$(".loadingMessage").html("Logging in to the course..."); 

		$(".desync").show();

		//IE popup creation - If it fails, the pop up blocker got it and we need to instruct users how to turn it off
		if(ie) {
			eWindow = window.open('', 'etechPage', 'toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,left=' + screen.width*2 + ', top=10000, width=1, height=1, visible=none');
			if(!eWindow) {
				$(".loading").hide();
				$(".dimBackground").hide();
				$(".ieHelp").show();
				return false;
			}
		}

		login(0, formData, function() {

			//Finally after logging into the campus, display the course in an iframe
			$(".coursePage").hide();
			$(".frame").attr("src", "https://"+campuses[0].Location+".learn.minnstate.edu/d2l/home/" + formData.course);

			loadFrame($(".frame"), null, function() {

				$.ajax({
					type: "GET",
					url: site_path + "/home/getEnrollments/",
					data: { },
					dataType: "json"
				}).done(function(data) {

					for(var i in data['courses']) {

						if(data['courses'][i].CourseID == formData.course) {
							$(".missingMessage").show().addClass("success").html("Course Successfully added!<br /><a href='/home/courses'>Return to courses</a>");
							return true;
						}

					}
				
					$(".missingMessage").show().addClass("failure").html("The course you requested was not added. This may be because you do not have access to this course, or you entered the wrong StarID Password. Please try again or contact support if the problem persists.<br /><a href='/home/courses'>Return to courses</a>");

				});

			});

		});


	});

	//Kick off log in process
	$("form[name='login']").submit(function(e) {

		e.preventDefault();

		var formData = { password: $("input[name='password']").val() };

		$(".courseLogin").find(".field_error").html("");
		if(!formData.password) {
			$(".courseLogin").find(".password_error").html("Please enter a password");
			return;
		}

		$(".courseLogin").hide(); 

		$(".loading").show();
		$(".dimBackground").show();
		$(".loadingMessage").html("Logging in to your campuses..."); 

		getCampusData(function() {

			$(".desync").show();
			getEnrollments();

			//IE popup creation - If it fails, the pop up blocker got it and we need to instruct users how to turn it off
			if(ie) {
				eWindow = window.open('', 'etechPage', 'toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,left=' + screen.width*2 + ', top=10000, width=1, height=1, visible=none');
				if(!eWindow) {
					$(".loading").hide();
					$(".dimBackground").hide();
					$(".ieHelp").show();
					return false;
				}
			}

			sessionStorage["userSession"] = 1;

			if(campusSize == 0) {

				$(".noCourses").show();
				endLoading();
				return;
			}

			login(0, formData);

		});


		return false; 

	});

	$(document).on('click', ".closeHelp", function() {

		$(".ieHelp").hide();
		location.reload();

	});

	$(document).on('click', ".popout-close", function() {

		$(".mainFrame").hide(); 
		$(".dimBackground").hide();

	});

	/*
	Create a new frame for each campus and log the user in
	After all campuses are logged in, endLoading is called

	*/
	function login(campusIndex, formData, callback) {

		var campus = campuses[campusIndex];

		if(!campus) {
			$(".problem").show().html("We are sorry, an error has occurred with this course. Please try again at another time.");
			$(".missingMessage").hide();
			endLoading();

			return;
		}

		if(ie) {
			$("#whisperForm").attr("action", "https://"+campus.Location+ ".learn.minnstate.edu/d2l/lp/auth/login/login.d2l").attr("target", "etechPage");
			$("#whisperForm").find("input[name='password']").val(formData.password);

			$("#whisperForm").submit();
			eWindow.blur();

			setTimeout(function() {

				if(campusIndex < campuses.length-1) {

					login(campusIndex+1, formData);

				}

				onSubmitLogin();

			}, 1200);

			return;
		}

		var newFrame = $("<iframe id='campusFrame" + campusIndex + "' name='campusFrame" + campusIndex + "' src='whisperLogin.php' class='campusFrame'></iframe>");

		loadFrame($(newFrame),".campusFrames",function() {

			$("#whisperForm").attr("action", "https://"+campus.Location+ ".learn.minnstate.edu/d2l/lp/auth/login/login.d2l").attr("target", "campusFrame" + campusIndex);
			$("#whisperForm").find("input[name='password']").val(formData.password);

			$("#whisperForm").submit();

			$(newFrame).attr("sandbox", "");

			//Log user into next campus
			if(campusIndex < campuses.length-1) {
				login(campusIndex+1, formData);
			}

			//When login inside the frame submits and the campus has logged in the user, increment the number of loaded campuses
			//After all campuses are loaded, end the login process by calling endLoading

			loadFrame($(newFrame), null, function() { onSubmitLogin(callback) });

		});

	}
	
	function onSubmitLogin(callback) {

		loadedCampuses++;

		//Campus Log In Complete, Show Courses
		if(loadedCampuses >= campusSize) {
			if(ie) { 
				eWindow.close();
			}
			endLoading();

			if(typeof callback == "function") {
				callback();
			}	
		}

	}

	/*  End of login process
	Time to check if the session is marked logged in
	*/
	function endLoading() {

		loadedCampuses = 0;
		$(".courseList").show();
		$(".loading").hide();
		$(".dimBackground").hide(); 

		
	}

	$(document).on('click', '.course', function() {

		var courseCampus = $(this).attr("data-campus");
		var campus = $.grep(campuses, function(e){ return e.Code == courseCampus; })[0];
		var campusLink = "https://"+campus.Location+".learn.minnstate.edu";

		window.open(campusLink+"/d2l/home/" + $(this).attr("data-course"), '_blank');

	});


	$(".missingCourseSearch").submit(function(e) {

		e.preventDefault();

		var course = $(this).find("select[name='missingCourse']").val();

		$.ajax({
			type: "GET",
			url: site_path + "/home/getCourseData/" + course,
			data: { },
			dataType: "json"
		}).done(function(data) {

			campuses = data['campus'];
			campusSize = 1;

			$(".coursePage").hide();
			$(".missingCourseLogin").show();
			$(".missingCourseLogin").find("input[name='course']").val(data['orgID']);

		});

		return false;
	});

})();
