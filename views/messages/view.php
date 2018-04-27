<script src="//cdn.ckeditor.com/4.4.7/full/ckeditor.js"></script>

<?php 
use Etech\Classes\Flasher AS Flasher;
use Etech\Classes\User AS User;

include "navigation.php"; 
    
$arrowClass = "";
if($order == "ASC") {
    $arrowClass = " flipped";
    $link = "DESC";
    $title = "Sort in Descending Order";
  } else {
    $link = "ASC";
    $title = "Sort in Ascending Order";
  }
  
  $recipients = "";
  if(isset($conversation)) {
      foreach($conversation->Recipients AS $recipient) {
        
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
  }
 
?>



<div class='conversationArea'>

<?php
    if(Flasher::contains("message")) {
        $message = Flasher::get("message");
    ?>
        <div class='<?php echo $message['class']; ?>'><?php echo $message['message']; ?></div>
<?php
    }
    
    if(Flasher::contains("errorMessage")) {
        $message = Flasher::get("errorMessage");
    ?>
        <div class='<?php echo $message['class']; ?>' style='display:inline-block;'><?php echo $message['message']; ?></div>
    <?php
        Flasher::remove("errorMessage");
    }
    else {
    ?>

<div class='reply' style='display:none;'>
  <form method="post" action="<?php echo SITE_PATH; ?>/messages/reply/<?php echo $conversation->ConversationID; ?>">
  <textarea name="message" id="editor" rows="10" cols="80">
  </textarea>
  <button type='submit' class='replyButton'>REPLY</button>
  </form>
</div>

<div class='conversationHeader'><a href='<?php echo SITE_PATH; ?>/messages/'><< Return to Inbox</a></div>
<div class='messages'>
    <div style='overflow:auto;'>
        <div style='float:left;overflow:auto;'><h1 style='float:left;'><?php echo $conversation->Title; ?></h1><span class='toList'>to <?php echo $recipients; ?></span></div>
        <div style='float:right;'><span class='openReply'><img src='<?php echo SITE_PATH; ?>/views/images/replyarrow.png' title='Reply' /> <span class='messageOptionText'>Reply</span></span> <a title='<?php echo $title; ?>' href='<?php echo SITE_PATH; ?>/messages/view/<?php echo $conversation->ConversationID; ?>/<?php echo $link; ?>' class='sorting'><img src='<?php echo SITE_PATH; ?>/views/images/arrow.png' class='sort-arrow<?php echo $arrowClass; ?>' /> <span style='margin-left:7px;' class='messageOptionText'>Sort</span></a> </div>
    </div>
    <br />
    <?php 
    foreach($messages AS $message) {

    ?>
    <div class='message' data-message='<?php echo $message->MessageID; ?>'>
        
        <div class='user-avatar inline-avatar'>
            <?php echo $user->getAvatar($message->StarID); ?>
        </div>
        <div class='post'>
            <div class='author'><strong><?php echo $message->Firstname . " " . $message->Lastname; ?></strong> <span class='date'>said <span class='alternate' data-original="<?php echo smartdate(strtotime($message->Date)); ?>" data-alternate="<?php echo date("F j, Y, g:i a", strtotime($message->Date)); ?>"><?php echo smartdate(strtotime($message->Date)); ?></span>:</span></div>
            <div class='content'>
            <?php echo nl2br($message->Content); ?>
            </div>
        </div>
    </div>
    <?php
    }
    ?>
</div>
 <?php
    }
   ?>
</div>

<script type="text/javascript">
    CKEDITOR.replace( 'editor', {
        skin : 'bootstrapck,<?php echo SITE_PATH; ?>/views/ckeditor/skins/bootstrapck/',
        toolbar :   [
                        [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'Cut', 'Copy', 'Paste', 'PasteFromWord', '-', 'Link', '-', 'NumberedList', 'BulletedList', 'TextColor', 'FontSize', 'Undo', 'Redo', 'RemoveFormat' ]
                
                    ]
    });
    $(document).on('click', '.openReply', function() {
    
        $(".reply").toggle();
    
    });
    
    $(".alternate").hover(function() {
        
        $(this).html($(this).attr("data-alternate"));
        
    }, function() {
        $(this).html($(this).attr("data-original"));
    });
        
</script>