<?php
include "adminNavigation.php";
?>
<div class='right'>
    <div class='page course-data'>
    <h1>Course Data</h1>

    <?php
    foreach($courses AS $course) {
    ?>
    <div class='course' data-course="<?php echo $course->OrgID; ?>">
        <div class='title'><?php echo $course->Name; ?> - <span class='course-date'><?php echo $course->Term; ?> <?php if($course->StartDate != "0000-00-00") echo " - " . $course->StartDate . " to " . $course->EndDate; ?></span></div>
        <div class='course-data-info'>
            <div>
                <strong>Instructor:</strong> <?php echo $course->Firstname . " " . $course->Lastname; ?><br />
                 <div class='showEnrollments'><img src='https://s3.amazonaws.com/rctclearnsite/lms/images/sort-arrows-down.png' style='width:14px;' alt='Show Enrollments' title='Show Enrollments' /> <strong>Enrollments:</strong> <?php echo $course->Enrollments; ?></div><br />

            </div>
            <div>
                <strong>Campus:</strong> <?php echo $course->Campus; ?><br />
                <strong>Last Enrollment Update:</strong> <?php if(!empty($course->LastEnrollment)) { echo smartDate(strtotime($course->LastEnrollment)); } else { echo "-"; } ?>
            </div>
            <div class='enrollments-template-show-<?php echo $course->OrgID; ?> enrollmentList' style='display:none;'>
            
             </div>
        </div>
    </div>   
    <?php
    }
    ?>

    </div>
</div>

<script type="template" data-template='enrollments'>
    
    <table class='enrollments'>
    {{#each enrollments}}
        <tr>
            <td>{{ this.Firstname }} {{this.Lastname }}</td>
            <td>{{ this.StarID }}</td>
            <td>{{ this.Time }}</td>
        </tr>
    {{else}}
    <tr>
        <td colspan='20'>No Enrollments</td>
    </tr>
    {{/each}}
    </table>

</script>

<script type='text/javascript'>
    
$(document).ready(function() {

autoTemplate.bind("enrollments", "enrollments", []);


    $(document).on('click', '.course .showEnrollments', function() {

        var courseID = $(this).parents(".course").attr("data-course");
        $(this).parents(".course").find(".enrollmentList").toggle();

        $.ajax( {
            url: "<?php echo SITE_PATH; ?>/admin/getEnrollments/" + courseID,
            type: "GET",
            dataType: "json"
        }).done(function(data) {

           autoTemplate.update("enrollments",data['enrollments'], courseID);    
          
        });
    });

});

</script>