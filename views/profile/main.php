<?php
use Etech\Classes\Flasher AS Flasher;
?>

<div class='page' style='margin-bottom:75px;'>
    <?php
    if(Flasher::contains("message")) {
        $message = Flasher::get("message");
    ?>
        <div class='<?php echo $message['class']; ?>' style='display:inline-block;'><?php echo $message['message']; ?></div>
    <?php
        Flasher::remove("message");
    }
    ?>
    <h2>Edit your Profile</h2>
    <div class='formMessages' style='display:none;'></div>
    <form name='editProfile'>
        <input type='hidden' name='csrftoken' value='<?php echo $csrf; ?>'>
        <label>Preferred Email Address: <span class='itemDescription'>All eTECH communication will be sent to this address</span> </label> 
        <input type='text' name='email' value='<?php echo $email; ?>'>
        
        <label>Degree Seeking Campus: <span class='itemDescription'>Your primary college where you are seeking your degree</span></label> 
        <select name='campus'>
            <option value='0'>Please Select</option>
            <?php 
            foreach($campuses AS $campus) {
            ?>
            <option value='<?php echo $campus->Code; ?>'<?php if($campus->Code == $campusCode) echo " selected=selected"; ?>><?php echo $campus->Campus; ?></option>
            <?php
            }
            ?>
        </select>
        
        <button type='submit' name='submit'>Update Profile</button>
        
    </form>


</div>


<div class='page' style='margin-top:0;'>
    
    <h2 class='avatarHeader'>Set an Avatar (Optional)</h2>

    <div class='currentAvatar'><?php echo $user->getAvatar(); ?></div>

    <form name="changeAvatar" action='<?php echo SITE_PATH; ?>/profile/changeAvatar' method="post" target="uploadTarget" enctype="multipart/form-data">
        
        <div class='dropFiles'>
            <div class='preUpload'>
                <div class='drop'>DROP IMAGE HERE</div>
                
                <div class='or'>OR</div>
                <div class='button selectFile'>
                    <span>Upload from Computer</span>
                    <input type="file" id="upload" name="upload" /> 
                 </div>
            </div>
            <div class='postUpload' style='display:none;'>
                <br /><button class='reUpload' type='button'>Upload Another</button>
            </div>
 
        </div>
        <div style='overflow:auto;'>
            <div class='fileDescriptor'><strong>File:</strong> <span class='filename'>None Selected</span></div>
            <div class='maxSize'>Max Size: 2MB</div>
        </div>
    </form>
</div>

<iframe id="uploadTarget" name="uploadTarget" src="" style="display:none;"></iframe>

<script type='text/javascript'>
    
    $(document).ready(function() {
        
        $("form[name='editProfile']").submit(function(e) {
        
            e.preventDefault();
                
              $(".formMessages").removeClass("errors").removeClass("success");
              
              var data = { campus: $("select[name='campus']").val(), email: $("input[name='email']").val(), csrftoken: $("input[name='csrftoken']").val() };
              
              $.ajax( {
                url: "/profile/update",
                data: data,
                type: "post",
                dataType: "json"
            }).done(function(data) {
                
                if (data.hasOwnProperty('errors')) {
  
                    $(".formMessages").addClass("errors").html(data['message']).show();
                    
                } else {  
                
                    $(".formMessages").addClass("success").html(data['message']).show();
                }
             
            });
            
            return false;
        });
        
        
        var canDrop = (window.File && window.FileReader);
        
        if (!canDrop){
            
            $(".drop").hide();
            $(".or").hide();
        
        }

        $("#upload").change(function(){
            $(".uploadMessage").remove(); 
     
            var filename = $(this).val().substring($(this).val().lastIndexOf('\\') + 1);
            $(".filename").html(filename); 
       
            $("form[name='changeAvatar']").submit();
        
        });
        
        $(".reUpload").on("click", function() {
        
            $(".postUpload").hide();
            $(".preUpload").show();
        
        });
        
        function parseResult(response) {
            if(response.hasOwnProperty("errors")) {
                
                var messages = "<strong>Avatar not uploaded:</strong><br />";
                for(var i in response.errors) {
                    
                    messages += response.errors[i] + "<br />";
                    
                }
                messages = messages.substring(0, messages.length-2);
           
                $("<div class='uploadMessage failure'>" + messages + "</div>").insertAfter(".avatarHeader");
                console.log("avaheader");
                return;
            }

            var img = $("<img class='avatar' />");
            $(img).attr("src", response.image);
            
            var img2 = $(img).clone();
            $(".currentAvatar").empty().append(img2);
            
             $("<div class='uploadMessage success'>" + response.message + "</div>").insertAfter(".avatarHeader");
            $(".preUpload").hide();
            $(".postUpload").show().find(".avatar").remove();
            $(".postUpload").prepend(img); 
        }
        
        $("#uploadTarget").bind('load', function() {
            var response = $.parseJSON($("#uploadTarget").contents().text());
            
            parseResult(response);
        
        });
        
        $(document).on('dragenter', function (e) 
        {
            e.stopPropagation();
            e.preventDefault();
        });
        $(document).on('dragover', function (e) 
        {
          e.stopPropagation();
          e.preventDefault();
        });
        $(document).on('drop', function (e) 
        {
            e.stopPropagation();
            e.preventDefault();
        });
 
        $(".dropFiles").on('dragenter', function (e) 
        {   
            $(this).css("border", "3px dashed #3F85AF");
            e.stopPropagation();
            e.preventDefault();
        });
        $(".dropFiles").on('dragleave', function (e) 
        {   
            $(this).css("border", "3px dashed #B4E3FF");
            e.stopPropagation();
            e.preventDefault();
        });
        $(".dropFiles").on('dragover', function (e) 
        {   
             e.stopPropagation();
             e.preventDefault();
        });
        $(".dropFiles").on("drop", function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            if(!canDrop) {
                return false;
            }
            
            $(".uploadMessage").remove(); 
            
            if(!e.originalEvent.dataTransfer.files[0]) {
                
                return false;
                
            }
            
            var file = e.originalEvent.dataTransfer.files[0];
            $(".filename").html(file.name); 
  
            var data = new FormData();
            data.append("upload", file);
  
            $.ajax({
                url: '<?php echo SITE_PATH; ?>/profile/changeAvatar/',
                dataType: 'json',
                contentType: false,
                data: data,
                cache: false,
                processData: false,
                type: 'POST'
            }).done(function(data) {
                
                parseResult(data);
            
            });
  
            return false;
        });
    });
        
    
</script>
