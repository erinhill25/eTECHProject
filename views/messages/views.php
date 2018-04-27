<?php
    use Etech\Classes\Flasher AS Flasher;
?>

<div class="conversationHeader" style="width:75%;margin:0 auto;"><a href="<?php echo SITE_PATH; ?>/messages/"><< Return to Inbox</a></div>
<div class="page" style="margin-top:0;border-top:0;">
    <ul class='viewedNavigation'>
        <li<?php echo !$unread ? " class='active'" : ""; ?>><a href='<?php echo SITE_PATH; ?>/messages/views/<?php echo $conversationID; ?>/yes'>Viewed</a></li>
        <li<?php echo $unread ? " class='active'" : ""; ?>><a href='<?php echo SITE_PATH; ?>/messages/views/<?php echo $conversationID; ?>/no'>Have Not Viewed</a></li>
    </ul>
    
    <?php
    if(Flasher::contains("message")) {
        $message = Flasher::get("message");
    ?>
        <div class='<?php echo $message['class']; ?>' style='display:inline-block;'><?php echo $message['message']; ?></div>
    <?php
        Flasher::remove("message");
    }
    else {
    ?>
    
    <h2><?php echo $message; ?></h2>
    <br />
    <table class='readTable'>
        <tr>
            
            <th>User</th>

        </tr>
        <?php
        if(count($users) == 0) {
        ?>
            <td>No Recipients Found Matching Criteria</td>
        <?php
        }
        foreach($users AS $user) {
        ?>
        <tr>
            
            <td><?php echo $user['Firstname'] . " " . $user['Lastname']; ?></td>

        </tr>
        <?php
        }
        ?>

        </tr>
    </table>
    <?php
    }
    ?>
</div>