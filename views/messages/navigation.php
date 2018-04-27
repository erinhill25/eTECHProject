<?php
$page = isset($page) ? $page : null;
?>
<div class='open-navigation'></div>

<div class='message-navigation'>
  <ul>
      <li<?php if($page == "inbox") echo " class='selected'"; ?>><div><a href='<?php echo SITE_PATH; ?>/messages/'>Inbox</a> <div class='messageIndicator<?php echo $unreadMessages > 0 ? ' new' : ''; ?>' title='<?php echo $unreadMessages; ?> Unread'><?php echo $unreadMessages; ?></div></div></li>
      <li<?php if($page == "favorites") echo " class='selected'"; ?>><div><a href='<?php echo SITE_PATH; ?>/messages/main/favorites'>Favorites </a> <img src="<?php echo SITE_PATH; ?>/views/images/star.png" style="width: 28px;" alt="View your Favorites" title='View your Favorites' /></div></li>
  </ul>
  <div class='section-header'><strong>Class Messages</strong></div>
  <ul class='classMessages'>
    <?php 
        if(count($courses) == 0) {
            echo "<div style='margin-left: 18%;margin-top:20px;margin-bottom:20px;color: #9E9E9E;font-style: italic;'>No courses found</div>";
        }
        foreach($terms AS $term) {
        ?>
         <div class='section-header date-header term'><strong><?php echo $term['Term']; ?></strong></div>
        <?php
            foreach($term['Courses'] AS $course) {
                $unread = $course->unread->unread;
            ?>
                <li<?php if($page == $course->OrgID) echo " class='selected'"; ?>><div><a href="<?php echo SITE_PATH;?>/messages/main/all/<?php echo $course->OrgID; ?>"><?php echo $course->Name; ?></a> <div class='messageIndicator<?php echo $unread > 0 ? ' new' : ''; ?>' title='<?php echo $unread; ?> Unread'><?php echo $unread; ?></div></div></li>
            <?php
            }
        }
     ?>

  </ul>
</div>

<script type='text/javascript'>

  $(".open-navigation").click(function() {

    $(".message-navigation").fadeToggle();

  });

</script>