<?php
use Etech\Classes\Flasher AS Flasher;

if(Flasher::contains("errorMessage")) {
        $message = Flasher::get("errorMessage");
?>
    <div class='<?php echo $message['class']; ?>' style='display:inline-block;'><?php echo $message['message']; ?></div>
<?php
    Flasher::remove("errorMessage");
} else {
?>
<div class='page'>
    <h1>Enrollments Updated</h1>
    Successfully updated the enrollments for this course. You may now return to your course.
    <br />
    <button class='button closeWindow'>Return to Course</button>
</div>

<script type="text/javascript">
    
    $(".closeWindow").click(function() {
        window.close();
    });

</script>
<?php 
}
?>
