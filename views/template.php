<html>
    
    <head>
        <title>360 eTECH Portal<?php echo isset($title) ? " - " . $title : ""; ?></title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, minimumscale=
1.0, maximum-scale=1.0" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link href='https://fonts.googleapis.com/css?family=Martel+Sans:200' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" type="text/css" href="<?php echo SITE_PATH; ?>/views/portalCSS.css">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js" type="text/javascript"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        <script src="<?php echo SITE_PATH; ?>/views/js/placeholder.js"></script>
        <script src="<?php echo SITE_PATH; ?>/views/js/handlebars.js"></script>
    </head>
 <body>
 <?php 
 $navItems = array( 
                array("model" => "Home", "method" => "main", "Title" => "Home", "URL" => SITE_PATH . "/home/"),
                array("model" => "Home", "method" => "courses", "Title" => "Courses", "URL" => SITE_PATH . "/home/courses/"),
                array("model" => "Messages", "Title" => "Messages <div class='messageIndicator" . ($unreadMessages > 0 ? ' new' : '') . " menu'>" . $unreadMessages . "</div>", "URL" => SITE_PATH . "/messages/"),
                array("model" => "Profile", "Title" => "Your Profile", "URL" => SITE_PATH . "/profile/"),
                array("model" => "Support", "Title" => "Support", "URL" => SITE_PATH . "/messages/newMessage/9:Support (Dennis Kronebusch)")
              );
    if($user->getAttribute("Role") == "admin") {
        $navItems[] = array("model" => "Admin", "Title" => "Admin", "URL" => SITE_PATH . "/admin/");
    }
 ?>

 <div class='page-wrap'>
     <div class='header'>
            <div class='logo'>
                <a href='/'><img src='https://s3.amazonaws.com/rctclearnsite/starpro/images/starpro.png' class='star' alt="Star" /></a>
                <div class='titleDescription'>
                    <a href='/'><div class='siteTitle'>Star<span class='proText'>Pro</span></div>
                    <div class='slogan'>360 eTECH Portal</div></a>
                </div>  
            </div>
            <div class='right'>
                <div class='welcome'>Hi <?php echo $firstname . " " . $lastname; ?> <?php if($authenticated) { ?>(<a href="<?php echo SITE_PATH; ?>/home/logout">Logout</a>)<?php } ?></div>
                <ul class='nav'>
                    <?php 
                    if(!$authenticated) {
                        echo "<li class='selected'><a href='" . SITE_PATH . "' title='Home'>Home</a></li>";
                    } 
                    else {
                        foreach($navItems AS $navItem) {
                            echo "<li" . (ucfirst($model) == $navItem['model'] && ((isset($navItem['method']) && $navItem['method'] == $method) || !isset($navItem['method']))  ? " class='selected'" : "") . "><a href='" . $navItem['URL'] . "'>" .  $navItem['Title'] . "</a></li>\n";
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
     <?php 
     if(!empty($impersonateID)) {
     ?>
     <div class='stickTop impersonation'>
        You are impersonating <?php echo $firstname . " " . $lastname; ?> (<a href="<?php echo SITE_PATH; ?>/admin/stopImpersonation/">Quit Impersonation</a>)
     </div>
     <?php
     }
    ?>
     
    <div class='flex'>
        <div class="sidebar">
                <h3>HELPFUL LINKS</h3>
                <ul class="links">
                    <li><a href="/">Home</a></li>
                    <li><a href="https://360etech.org" target="_blank">360 eTech Main Site</a></li>
                    <li><a href="https://360etech.org/app/ask" target="_blank">360 eTech Official Support</a></li>
                    <li><a href="<?php echo SITE_PATH; ?>/home/forwarding">Set Up Email Forwarding</a></li>
                    <li><a href="https://youtu.be/dxweADBwhQo" target="_blank">Introduction for New Students</a></li>
                </ul>
        </div>

         <div class='main-content'>
        <?php 
        if(isset($validated) && $validated  == 0 && $authenticated == true && !isset($hideEmailNotification)) {
        ?>
          <div class='warning'>
          Your email address is not yet verified. Please update your email in the profile tab and you will receive instructions on how to validate your email address<br />
            <a href='<?php echo SITE_PATH; ?>/profile/sendValidation'>Resend Validation Link</a>
          </div>
        <?php
        }
        ?>
         <noscript>
         <div class='warning'>
         For full functionality of this site it is necessary to enable JavaScript.
         Here are the <a href="http://www.enable-javascript.com/" target="_blank">
         instructions how to enable JavaScript in your web browser</a>.
         </div>
        </noscript>

         <?php echo $content; ?>
         </div>
    </div>

</div>
<ul class='mobilenav'>
  <li><a class='open-links'>Links</a></li>
  <li><a href='<?php echo SITE_PATH; ?>/home/courses/'>Courses</a></li>
  <li><a href='<?php echo SITE_PATH; ?>/messages/'>Messages</a> <span class='messageIndicator <?php if($unreadMessages > 0) echo 'new'; ?>'><?php echo $unreadMessages; ?></span></li>
  <li><a href='<?php echo SITE_PATH; ?>/profile/'>Profile</a></li>
  <li><a href='<?php echo SITE_PATH; ?>/messages/newMessage/9:Support (Dennis Kronebusch)'>Support</a></li>
</ul>
<div class='bottom'>
    <div class='version'>1.0.0</div>
    <div class="copyright">Provided by RCTCLEARN.NET</div>
</div>


<script type="text/javascript" src="<?php echo SITE_PATH; ?>/views/js/template.js"></script>

<script type="text/javascript">
    var stickTop = $(".stickTop").css("top");
    
    $(".open-links").click(function() { 

      $(".sidebar").fadeToggle();

    });

    function moveStick() {
        if($( document ).scrollTop() > 15) {
            $(".stickTop").css("top", 0);
        } else {
            $(".stickTop").css("top", stickTop);
        }
    }

    $( document ).scroll(moveStick);
    moveStick();

    var clickouts = [$(".sidebar"), $(".message-navigation")];

    $( document ).on('mouseup', function(e) {

        $(clickouts).each(function() {

          if($(this).css("position") != "fixed") {
            return true;
          }

          if(!$(this).is(e.target) && $(this).has(e.target).length == 0 ) {
             $(this).fadeOut();
          }
        });


    });
    
</script>    
  <!-- Google Analytics -->
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("UA-1135918-2");
pageTracker._initData();
pageTracker._trackPageview();
</script>
<!-- /Google Analytics -->

</body>
</html>
