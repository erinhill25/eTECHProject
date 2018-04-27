<?php
include "adminNavigation.php";
?>
<div class='right'>
    <div class="page">
        <h2>Search for Users</h2>

        <div class="search" style="width:65%;margin-bottom: 15px;">
            <input type="text" name="search" placeholder="Search..."> <div class="clear" style='right:52px;'>&#x2716;</div>
        </div>
        
        <div class='users-template-show'>
        
        </div>

        <button type='button' class='showMore button'>Show More</button>
    </div>
</div>


<script type="template" data-template='users'>
    
    <table class='users'>
    <tr>
        <th>Name</th><th>Role</th><th>Email</th><th>Last Login</th><th>Date Registered</th><th>Impersonate</th>
    </tr>
  {{#each users}}
        <tr>
        <td>{{this.Firstname}} {{this.Lastname}} ({{ this.StarID }})</td>
        <td>{{this.Role}}</td>
        <td>{{this.Email}}</td>
        <td>{{this.LastLogin}}</td>
        <td>{{this.DateRegistered}}</td>
        <td><a href="<?php echo SITE_PATH; ?>/admin/impersonate/{{this.StarID}}">Impersonate User</a></td>
        </tr>
    {{else}}
        <tr>
        <td colspan='20'>No results, utilize the search above</tD>
        </tr>
    {{/each}}
    </table>

</script>


<script type="text/javascript">
var offset = 0;
var searchTerm = "";

$(document).ready(function() {
    
    autoTemplate.bind("users", "users", []);

    function search(searchTerm, searchOffset, replace) {
            if(searchOffset == null) { searchOffset = 0; }
            if(replace == null) { replace = false; }
            $.ajax( {
                    url: "<?php echo SITE_PATH; ?>/admin/searchUsers/" + searchTerm + "/" + searchOffset,
                    type: "GET",
                    dataType: "json"
                }).done(function(data) {
                   
                   offset = data['offset'];

                   if(data['users'].length == 0) {
                    $(".showMore").html("No More Results");
                   }
                   if(replace) {
                       autoTemplate.update("users", data['users']);
                   } else { 
                        autoTemplate.update("users", autoTemplate.getVal("users").concat(data['users']));
                    }
                   
                });
    
    }
    
     $("input[name='search']").keyup(function(e) {
            
            searchTerm = $(this).val();
            $(".showMore").html("Show More");
            if(searchTerm.length> 2) {
                
               autoTemplate.update("users", []);
               search(searchTerm, 0, true);
            
            }
            
     });
    $(document).on('click', '.clear', function() {
    
        autoTemplate.update("users", []);
        $("input[name='search']").val("");
        $(".showMore").html("Show More");
        searchTerm = "";
        search("", 0, true);
    
    });

    $(document).on('click', '.showMore', function() {
        
        search(searchTerm, offset);

    });
    
    search(""); 
});

</script>