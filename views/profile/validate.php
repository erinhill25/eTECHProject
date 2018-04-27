<?php
use Etech\Classes\Flasher as Flasher;
?>
<div class='page'>

<h2>Email Validation</h2>

 <?php
    if(Flasher::contains("message")) {
        $message = Flasher::get("message");
    ?>
        <div class='<?php echo $message['class']; ?>' style='display:inline-block;'><?php echo $message['message']; ?></div>
    <?php
        Flasher::remove("message");
    }
    ?>

</div>

<?php
if($success) 
{
?>
<script type="text/javascript">
    
    window.setTimeout(function() {
        
        window.location = "/etech/profile/";
    
    }, 5000);
    
</script>
<?php
}
?>