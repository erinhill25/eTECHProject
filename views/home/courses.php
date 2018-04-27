<script src="/views/tagger/tagger.js"></script>
<link rel="stylesheet" href="/views/tagger/tagger.css">

<div class='problem failure' style='display:none;'></div>

<div class="dimBackground" style="display: none;"></div>

<div class="courseLogin page">
    <form name='login'>
        <h2>Your Courses</h2>
        <div class="errorMessage" style="display:none;"></div>
        Please enter your StarID password to continue<br>
        <input type="hidden" name="username" value="<?php echo $userID; ?>" autocomplete="off"> 
        <label>Password:</label> <input type="password" name="password" value="" autocomplete="off">
        <div class="field_error password_error"></div><br><br>
        <input type="submit" value="View Courses">
    </form>
    <a class='bypass'>&nbsp;</a>
</div>

<div class="missingCourseLogin page" style="display:none;">
    <form name='missingLogin'>
        <h2>Missing Course</h2>
        <div class="errorMessage" style="display:none;"></div>
        Please enter your StarID password to continue<br>
        <input type="hidden" name="username" value="<?php echo $userID; ?>" autocomplete="off"> 
        <input type="hidden" name="course" value="" autocomplete="off"> 
        <label>Password:</label> <input type="password" name="password" value="" autocomplete="off">
        <div class="field_error password_error"></div><br><br>
        <input type="submit" value="Add Course">
    </form>
</div>

<div class='mainFrame' style='display:none;'>
    <div class='frameTop'>
        <div class="popout-close">&times;</div>
    </div>
    <iframe src="<?php echo SITE_PATH; ?>/whisperLogin.php" class='frame'></iframe>
</div>

<div class="campusFrames">
</div>

<div class='coursePage'>
    <a class="desync button">REFRESH</a>

    <div class="page courseList" style="display:none;">
        <div class='courseHead'>
            <h1>Your Courses</h1> 
            <div class='missingCourses'>
                <div>Missing a course? Try adding it here:</div>
                <form class='missingCourseSearch'>
                    <div class='courseTag'><select class='missingCourse' name='missingCourse' placeholder='Find a Course...'></select></div>
                    <input type='submit' name='submit' value='Add' />
                </form>
            </div>
        </div>
        <div class='noCourses warning' style='display:none;'>No applicable courses found at this time. Please view the course you are missing at D2L. If after that you do not see your course, please contact support</div>
        <div class='courses-template-show'>
        </div>
    </div>
</div>

<div class='missingMessage' style='display:none;'></div>

<div class="ieHelp page" style='display:none;'>
    <h2>Action Needed</h2>
    <br />
    <div class="warning" style="width:100%">We have detected that you are using internet explorer and the login process was interrupted. To ensure the best compatibility with the 360 eTECH portal, please follow these steps:</div>
    <strong>At the bottom of the screen, you should see the following prompt</strong>
    <br />
    <strong>Select "Options for this site" with your mouse and select "Always Allow"</strong>
    <br /><br />
    <img src="<?php echo SITE_PATH; ?>/views/images/iestep2.png" alt="prompt2" style="width:100%" />
    <br /><br />
    <button type='button' class='closeHelp'>Ok, Made the changes!</button>

</div>

<div class="popup loading" style="display: none;">

	<img src="https://innovations.learn.minnstate.edu/shared/u/common/pages/images/ajax-loader-large.gif?cache=none" alt="Loading">

	<div class='loadingMessage'>Logging in to your campuses...</div>

</div>

<form id="whisperForm" class='whisperForm' method="post" style='display:none;'>
    
    <input type="text" name="userName" value="<?php echo $userID; ?>">
    <input type="password" name="password">

</form>

<script type="template" data-template='courses'>
    
    {{#each campuses}}
    <div class='campus'>
        <div class='emblem'>{{#if this.emblem }}<img src='<?php echo SITE_PATH; ?>{{ this.emblem }}' alt='{{ this.name }}' />{{/if}}</div>
        <div class='campusName'><a href='{{ this.src }}' title='Go to {{ this.name }}' target="_new">{{ this.name }}</a></div>
    </div>
    <div class='courses'>
        {{#each this.terms}}
        <div class='term'>{{ this.name }}</div>
            {{#each this.courses}}
            <div class='course-single'>
                <div class='course-info'>
                    <div class='course-title'><a title='Enter {{ this.Name }}' class='course' data-course='{{ this.OrgID }}' data-campus='{{ this.Campus }}'>{{ this.Name }}</a></div>
                    <span class='description'>{{#if this.StartDate}}{{ this.StartDate }} to {{this.EndDate}} {{/if}}{{#if this.Firstname}}Taught by {{ this.Firstname }} {{ this.Lastname }}{{/if}}</span>
                </div>
                <div class='resources'>
                    <a href='/home/resources/{{ this.CourseID }} '><img src='<?php echo BUCKET; ?>/books.png' alt='Resources' title='View Course Resources' class='resourcesbook' /></a>
                </div>
                <div class='library'>
                    {{#if this.LibraryName }}<a href='{{ this.LibraryLink }}' target='_new'><img src='<?php echo SITE_PATH; ?>{{ this.LibraryImage }}' alt='{{ this.LibraryName }}' title='{{ this.LibraryName }}' /></a>{{/if}}
                </div>
            </div>
            {{/each}}
        {{/each}}
    </div>
   {{/each}}
   
</script>


<script type="text/javascript">
var site_path = "<?php echo SITE_PATH; ?>";

$(document).ready(function() {
    $(".mainFrame").draggable({cursor: "move", handle: ".frameTop"});    
    
    autoTemplate.bind("courses", "courses");

     $('.missingCourse').tagger({
            imgSearch: '<?php echo SITE_PATH; ?>/views/images/search.png',
            imgDownArrow: '<?php echo SITE_PATH; ?>/views/images/dropdown.png',
            imgRemove: '<?php echo SITE_PATH; ?>/views/images/remove.png',
            baseURL: '',
            placeholder: 'Find a Course...',
            displayHierarchy: false,
            indentMultiplier: 2,
            characterThreshold: 3,
            fieldWidth: '100%',
            ajaxURL: '<?php echo SITE_PATH; ?>/home/searchCourses/'
      });

     $(".missingCourses").find(".tagger input").css("width", "50%");
    
});

</script>
<script type="text/javascript" src="<?php echo SITE_PATH; ?>/views/js/starone.js"></script>