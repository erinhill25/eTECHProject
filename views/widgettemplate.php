<html>
    
    <head>
        <title>360 eTECH Portal Widget<?php echo isset($title) ? " - " . $title : ""; ?></title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, minimumscale=
1.0, maximum-scale=1.0" />
        <link href='https://fonts.googleapis.com/css?family=Martel+Sans:200' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" type="text/css" href="<?php echo SITE_PATH; ?>/views/css/widget.css">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js" type="text/javascript"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        <script src="<?php echo SITE_PATH; ?>/views/js/placeholder.js"></script>
        <script src="<?php echo SITE_PATH; ?>/views/js/handlebars.js"></script>
    </head>
 <body>

<div class='widget'>
 <?php echo $content; ?>
</div>
      

<script type="text/javascript" src="<?php echo SITE_PATH; ?>/views/js/template.js"></script>

 
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
