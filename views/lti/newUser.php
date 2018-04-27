<div class='widgetContent'>
<h2>Activate your Portal Account</h2>

<div class='content'>
<p><strong>Welcome</strong>, we have detected that you do not currently have a portal account.</p>
<p>The 360 eTECH Portal facilitates communication with your <?php echo $target; ?> and provides a convenient way to access multiple courses across several campuses.</p>
<a class='button activateAccount' href='https://360etech.starpro.me/profile/' target='_new' style='margin-top: 40px;'>Activate your Account</a>
</div>
<div class='newUserContent' style="display:none;">
<p>Thank you, please refresh this page after you have signed in at the portal to complete the process.</p>
<a class='button refresh' href='javascript://' style='margin-top: 40px;'>Refresh the Page</a>
</div>

</div>

<script type="text/javascript">
    
    $(".activateAccount").click(function() {

        $(".content").hide();
        $(".newUserContent").show();

    });

    $(".refresh").click(function() {

        window.location.reload();

    });

</script>