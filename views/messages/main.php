<?php
    use Etech\Classes\Flasher AS Flasher;
    include "navigation.php"; 
?>

<?php
    if(Flasher::contains("message")) {
        $message = Flasher::get("message");
    ?>
        <div class='<?php echo $message['class']; ?>' style='display:inline-block;'><?php echo $message['message']; ?></div>
    <?php
    }
    if(Flasher::contains("errorMessage")) {
        $message = Flasher::get("errorMessage");
    ?>
        <div class='<?php echo $message['class']; ?>' style='display:inline-block;'><?php echo $message['message']; ?></div>
    <?php
        Flasher::remove("errorMessage");
    } else {
    ?>
<div class='conversationArea'>
    <button type='button' class='createMessage'>NEW MESSAGE</button>
    <div style='clear:both;'></div>
    <br />
    <div class='conversationHeader'>Your Conversations</div>
    <div class='conversations'>
        
        <?php 
        
        $titleLength = 70;
        
        if(count($messages) == 0) {
        ?>
           <div style='padding:20px;'>
               <strong>No conversations found.</strong> 
               <br />Send a message using the button above
           </div>
        <?php
        }
        
        foreach($messages AS $message) {
            
            $message->Latest = strip_tags($message->Latest);

            $recipients = "";

              foreach($message->Recipients AS $recipient) {
                
                //Is a course recipient
                if($recipient->Type == "Course") {
                
                    $recipients .= "Students in " . $recipient->CourseName . ", ";
                    continue;
                }
                if($recipient->Type == "Instructor") {
                
                    $recipients .= "Instructor of " . $recipient->CourseName . ", ";
                    continue;
                }
                if($recipient->UserID == "allStudents") {
                    
                    $recipients .= "All Students, ";
                    continue;
                }
                if($recipient->UserID == "allStaff") {
                    
                    $recipients .= "All Staff, ";
                    continue;
                }
                
                 $recipients .= ($userID == $recipient->UserID ? "Me" : $recipient->Firstname . " " . $recipient->Lastname) . ", ";

              }
              
              $recipients = substr($recipients, 0, -2);
        
        ?>
        <div class='conversation' data-conversation='<?php echo $message->ConversationID; ?>'>
          
            <div class='starter'><div class='marker <?php echo $message->Read ? 'read' : 'unread'; ?>' title='<?php echo !$message->Read ? 'New Message' : 'No new Messages'; ?>'></div> <?php echo $message->Firstname . " " . $message->Lastname; ?> (<span style='font-size:0.8em;'><?php echo ucwords($message->Role); ?></span>)</div>
            
            <div class='to'><?php echo $recipients; ?></div>

            <div class='title'><?php echo (!$message->Read ? "<strong>" . $message->Title . "</strong>" : $message->Title); ?><div class='message-lead'> - <?php echo (strlen($message->Latest) > $titleLength) ? substr($message->Latest, 0, $titleLength) . '...' : $message->Latest; ?></div></div>
            
            <?php if($message->viewAccess) { ?><div class='whoViewed'><a href='<?php echo SITE_PATH; ?>/messages/views/<?php echo $message->ConversationID; ?>/' title='Recipients That Viewed'>Recipient Activity</a></div><?php } ?>
            
            <div class='date'> <span class='alternate' data-original="<?php echo smartdate(strtotime($message->LastMessageDate)); ?>" data-alternate="<?php echo date("F j, Y, g:i a", strtotime($message->LastMessageDate)); ?>"><?php echo smartdate(strtotime($message->LastMessageDate)); ?></span></div>
            
            <div class='favorite<?php echo $message->isFavorite ? ' isFavorite' : ''; ?>'><img src="<?php echo SITE_PATH; ?>/views/images/<?php echo $message->isFavorite ? 'star' : 'starinactive'; ?>.png" alt="Favorite" /></div>
        
        </div>
        
        <?php
        }
        ?>

        
    </div>
</div>
<?php 
}
?>

<script type='text/javascript'>
$(document).ready(function() {    
    $(document).on('click', '.conversation', function() {
    
        window.location.href = "<?php echo SITE_PATH; ?>/messages/view/" + $(this).attr("data-conversation");
    
    });
    $(document).on('click', '.createMessage', function() {
    
        window.location.href = "<?php echo SITE_PATH; ?>/messages/newMessage/";
    
    });
    
    $(document).on('click', '.favorite', function(e) {
        
        e.preventDefault();
        
        var action = $(this).hasClass("isFavorite") ? "unfavorite" : "favorite";
        var that = $(this);
        
         $.ajax({
                url: "/messages/" + action + "/" + $(this).parents(".conversation").attr("data-conversation"),
                data: { },
                type: "get",
                dataType: "json"
        }).done(function() {
            if(!$(that).hasClass("isFavorite")) {
                $(that).find("img").attr("src", "<?php echo SITE_PATH; ?>/views/images/star.png");
                $(that).addClass("isFavorite");
            }
            else {
                $(that).find("img").attr("src", "<?php echo SITE_PATH; ?>/views/images/starinactive.png");
                $(that).removeClass("isFavorite");
            }
        });
        
        return false;
        
    });
    
     $(".alternate").hover(function() {
        
        $(this).html($(this).attr("data-alternate"));
        
    }, function() {
        $(this).html($(this).attr("data-original"));
    });
   
    
});
    
</script>