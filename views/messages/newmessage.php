<script src="//cdn.ckeditor.com/4.4.7/full/ckeditor.js"></script>
<script src="<?php echo SITE_PATH; ?>/views/tagger/tagger.js"></script>
<link rel="stylesheet" href="<?php echo SITE_PATH; ?>/views/tagger/tagger.css">

<?php 
include "navigation.php"; 

$recipientID = isset($recipientID) ? $recipientID : null;
$recipientDisplay = isset($recipientDisplay) ? $recipientDisplay : null;
?>

<div class='conversationArea newMessage' style='margin-bottom:25px;'>
    <div class='conversationHeader'>Send a Message</div>
    <div class="page">
        <div class='reply'>
          <div class='errors' style='display:none;'></div>
          <form method="post" action="<?php echo SITE_PATH; ?>/messages/newMessage/" name="createMessage">
          <select name="recipients" class="recipients" multiple="">
          </select> <button class='openAddressBook' type='button'>Open Address Book</button>
          <input type='text' name='subject' class='subject' placeholder="Subject">
          <br /><br />
          <textarea name="message" id="editor" rows="40" cols="80">
          </textarea><br />
          <button type='submit' class='replyButton'>SEND</button>
          </form>
        </div>

    </div>
</div>

<div class='addressBook' style='display:none;'>
    
    <div class='header'>
        <h2>Address Book</h2>
        <div class='close'>&#x2716;</div>
    </div>
    
    <div style='overflow:hidden;'>
    <div class='search'>
        <input type='text' name='search' placeholder='Search...'> <div class='clear'>&#x2716;</div>
    </div>
    

    <select name="courses" style='color: #B1A9B1;'>
        <option value="0">Filter by Course</option>
        <?php
        foreach($courses AS $course) {
        ?>
            <option value="<?php echo $course->OrgID; ?>"><?php echo $course->Name; ?></option> 
        <?php
        }
        ?>
    </select>
    </div>
    
    <div class='contacts'>
        <table class='addressList'>
        </table>
    </div>
    <?php
    if($user->getAttribute("Role") == "teacher" || $user->getAttribute("Role") == "admin") {
    ?>
    <h2 class='bulkHeader'>Group Send</h2>
        <div class="bulkCourses">
            <?php 
            if($user->getAttribute("Role") == "admin") {
            ?>
                <div class='bulkCourse contact' data-id='allStudents' data-key="All Students">All eTECH Students</div>
                <div class='bulkCourse contact' data-id='allStaff' data-key="All Staff">All eTECH Staff</div>
            <?php
            }
            foreach($courses AS $course) {
            ?>
                <div class='bulkCourse contact' data-id='course-<?php echo $course->OrgID; ?>' data-key="Students in <?php echo $course->Name; ?>">All students taking <?php echo $course->Name; ?></div>
            <?php
            }
            ?>
        </div>
    <?php
    }
    ?>
    
</div>

<div class='addedContact success popup' style='display:none;'>
    Contact Added to Recipients
</div>

<script type="template" data-template='addressList'>      
    <table class='addressList'>
        <tr>
            <th width='10%'></th><th width='40%'>Name</th><th width="20%">Role</th>
        </tr>
        {{ contacts }}
    </table>  
</script>


<script type="template" data-template='contact'>      
     <tr class="contact" data-id="{{ userID }}" data-key="{{ display }}">
        
        <td><img src="<?php echo SITE_PATH; ?>/views/images/arrow.png" class="addPerson hoverOpacity" title="Add to Message" alt="Add Person" /></td><td>{{ display }}</td><td>{{ role }}</td>
        
     </tr>
</script>

<script type="template" data-template='emptyContact'>      
     <tr>
        
        <td colspan='8'>No Results Found. Try changing your filtering options above</td>
        
     </tr>
</script>


<script type="text/javascript">
    
    var courseSearch, searchTerm;
    
    var recipientID = "<?php echo htmlentities($recipientID); ?>";
    var recipientDisplay = "<?php echo htmlentities($recipientDisplay); ?>"; 
    
    String.prototype.ucFirst = function() {
        return this.charAt(0).toUpperCase() + this.slice(1);
    }
    
    var contacts = <?php echo json_encode($contacts); ?>;
    
    CKEDITOR.replace( 'editor', {
        skin : 'bootstrapck,<?php echo SITE_PATH; ?>/views/ckeditor/skins/bootstrapck/',
        height: 400,
        toolbar :   [
                        [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'Cut', 'Copy', 'Paste', 'PasteFromWord', '-', 'Link', '-', 'NumberedList', 'BulletedList', 'TextColor', 'FontSize', 'Undo', 'Redo', 'RemoveFormat' ]
                
                    ]
    });
    
    function parseTemplate(template, vars) {
        
        var content = $("script[data-template='" + template + "']").html();
        
        for(var i in vars) {
        
            content = content.replace(new RegExp("{{ " + i + " }}", 'g'), vars[i]);
        
        }
        
        return content;
        
    }
  
  
    function updateUI() {
  
        var contactTemplate = "";
        
        var hasContacts = false;
        for(var i in contacts) {
            
            if(contacts[i].hasOwnProperty("userID")) {
                contacts[i].role = contacts[i].role.ucFirst();
                contactTemplate += parseTemplate("contact", contacts[i]);
                hasContacts = true;
            }
        
        }
        if(!hasContacts) {
            
            contactTemplate = parseTemplate("emptyContact");
        
        }
 
        var addressList = parseTemplate("addressList", { contacts: contactTemplate });
        
        //Update address list
        $(".addressList").replaceWith(addressList);
        
    
    }
        
    updateUI();    
        
    $(document).ready(function() {
       
           $('.recipients').tagger({
                imgSearch: '<?php echo SITE_PATH; ?>/views/images/search.png',
                imgDownArrow: '<?php echo SITE_PATH; ?>/views/images/dropdown.png',
                imgRemove: '<?php echo SITE_PATH; ?>/views/images/remove.png',
                baseURL: '',
                placeholder: 'To',
                displayHierarchy: false,
                indentMultiplier: 2,
                characterThreshold: 3,
                fieldWidth: '100%',
                ajaxURL: '<?php echo SITE_PATH; ?>/messages/search/'
          });
        
        $(document).on('click', '.openAddressBook', function() {
            
            $(".addressBook").show();
        
        });
        $(document).on('click', '.close', function() {
            
            $(".addressBook").hide();
        
        });
          
        $(document).on('click', '.contact', function() {
            
            $(".popup").fadeIn(200);
            $(".popup").css("top", $(this).offset().top - 50);
            $(".popup").css("left", $(this).offset().left + 180);
            
            window.setTimeout(function() {
                $(".popup").fadeOut(200);
            }, 1000);
            
            var data = { };
            data[$(this).attr("data-id")] =  { id: $(this).attr("data-id"), suggestable:true, key: $(this).attr("data-key") };

            $('.recipients').tagger("addTag", $(this).attr("data-id"),  data);

        });
          
           
        $(document).on('submit', "form[name='createMessage']", function(e) {
            
            e.preventDefault();
            
            var data = { recipients: $("select[name='recipients']").val(), subject: $("input[name='subject']").val(), message: CKEDITOR.instances.editor.getData() };
              
             $.ajax( {
                url: "<?php echo SITE_PATH; ?>/messages/createConversation",
                data: data,
                type: "post",
                dataType: "json"
            }).done(function(data) {
                
                if (data.hasOwnProperty('errors')) {
                    
                    var errorText = "";
                    for(var i in data['errorMessages']) {

                        errorText += data['errorMessages'][i] + "<br />";
                    }
                  
                    $(".errors").html(errorText).show();
                    
                } else {
                    
                    window.location.href = "<?php echo SITE_PATH; ?>/messages/";
                
                }
             
            });
            
            
            return false;
        
        });
        
        function searchContacts(search, orgID) {
              if(!orgID) {
                orgID = courseSearch;
              }
              if(!search) {
                search = searchTerm;
              }
              $.ajax( {
                url: "<?php echo SITE_PATH; ?>/messages/contacts/",
                data: { search: search, courseID: orgID },
                type: "GET",
                dataType: "json"
            }).done(function(data) {
               
                contacts = data;
                updateUI();
               
            });
        
        }
        
        $("input[name='search']").keyup(function(e) {
            
            searchTerm = $(this).val();
            if($(this).val().length> 2) {
                
                searchContacts($(this).val());
            
            }
            
        });
        
        $(document).on('click', '.clear', function() {
            
            searchTerm = "";
            searchContacts("");
            $("input[name='search']").val("");
            
        });
            
        $("select[name='courses']").change(function() {
            
            courseSearch = $(this).val();
            searchContacts($("input[name='search']").val(), $(this).val());
        
        });
        
        $(".addressBook").draggable({cursor: "move", handle: ".header"});
        
        
        //Add recipient from the URL
        if(recipientID) {
            var data = { };
            data[recipientID] = {  id: recipientID, key: recipientDisplay, suggestion: recipientDisplay };
            $('.recipients').tagger("addTag", recipientID, data);
        }
    
    });
</script>