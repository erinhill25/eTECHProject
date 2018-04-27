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
<div class='widgetContent'>

    <h2>360 eTECH Portal</h2>

    <p>The 360 eTECH Portal facilitates communication with your <?php echo $target; ?> and provides a convenient way to access multiple courses across several campuses.</p>

    <h3>Welcome, <?php echo $name; ?></h3>

    <ul class='actionItems'>

        <li><div><a href='https://360etech.starpro.me/messages/newMessage/<?php echo $messageTarget; ?>' target='_new'>Message your <?php echo $target; ?></a></div></li>

        <li><div><a href='https://360etech.starpro.me/messages/' target='_new'>View your Inbox <div class='messages<?php if($newMessages > 0) echo ' new'; ?>'><?php echo $newMessages; ?></div></a></div></li>

        <li><div><a href='https://360etech.starpro.me/profile/' target='_new'>Edit your Profile</a></div></li>

    </ul>

    <a class='button' href='https://360etech.starpro.me/' target='_new'>View the 360 eTECH Portal</a>

</div>
<?php
}
?>