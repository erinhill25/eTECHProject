<div class='page'>
    <a href='http://360etech.org' target='_new'><img src="https://s3.amazonaws.com/rctclearnsite/etech/360etech.png" class='etechLogo' alt="360 eTECH" /></a>
    <h1>Welcome to the 360 eTECH Portal</h1>
    <p>The portal is designed for students enrolled in Minnesota State college campuses to manage their courses taken from multiple colleges. This portal takes care of signing in to multiple D2L Brightspace instances as well as managing communication from teacher to student</p>
    <?php
    if(!$authenticated) {
    ?>
    <h3>Using your StarID, sign in below and begin using your 360 eTECH portal today</h3>
    <br />
    <a href="<?php echo SITE_PATH; ?>/home/doAuth" class='authenticate button'>Authenticate to Begin</a>
    <br /><br />
    <?php
    } else {
    ?>
    <br />
    <a href="<?php echo SITE_PATH; ?>/home/courses/" class='button'>View your Courses</a>
    <br /><br />
    <?php
    }
    ?>

</div>

