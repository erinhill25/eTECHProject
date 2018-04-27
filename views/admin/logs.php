<?php
include "adminNavigation.php";
?>
<div class='right'>
    <div class="page">
        <h2>Search for Logs</h2>

        <div class="search" style="width:65%;margin-bottom: 15px;">
            <input type="text" name="search" placeholder="Search..."> <div class="clear" style='right:52px;'>&#x2716;</div>
        </div>
        
        <div class='logs-template-show'>
        
        </div>

        <button type='button' class='showMore button'>Show More</button>
    </div>
</div>


<script type="template" data-template='logs'>
    
    <table class='logs'>
    <tr>
        <th>Name</th><th>Event</th><th>Time</th><th>Details</th>
    </tr>
     {{#each logs}}
        <tr>
        <td>{{this.Firstname}} {{ this.Lastname }} ({{ this.StarID }})</td>
        <td>{{this.Event}}</td>
        <td>{{this.Time}}</td>
        <td>{{this.Details}}</td>
        </tr>
    {{else}}
        <tr>
        <td colspan='20'>No Logs Found</tD>
        </tr>
    {{/each}}
    </table>

</script>


<script type="text/javascript">
var offset = 0;

$(document).ready(function() {
    
    var searchTerm = "";

    autoTemplate.bind("logs", "logs", []);

    function search(searchTerm, searchOffset, replace) {
            if(searchOffset == null) { searchOffset = 0; }
            if(replace == null) { replace = false; }
            $.ajax( {
                    url: "<?php echo SITE_PATH; ?>/admin/getLogs/" + searchTerm + "/" + searchOffset,
                    type: "GET",
                    dataType: "json"
                }).done(function(data) {
                   
                   offset = data['offset'];

                   if(data['logs'].length == 0) {
                    $(".showMore").html("No More Results");
                   }

                   if(replace) {
                       autoTemplate.update("logs", data['logs']);
                   } else { 
                        autoTemplate.update("logs", autoTemplate.getVal("logs").concat(data['logs']));
                    }
                   
                });
    
    }

     $("input[name='search']").keyup(function(e) {
            
            searchTerm = $(this).val();
            $(".showMore").html("Show More");
            if(searchTerm.length> 2) {
               autoTemplate.update("logs", []);
               search(searchTerm, 0, true);
            
            }
            
     });
    $(document).on('click', '.clear', function() {
    
        autoTemplate.update("logs", []);
        $("input[name='search']").val("");
        $(".showMore").html("Show More");
        searchTerm = "";
        search("", 0, true);
    
    });

    $(document).on('click', '.showMore', function() {

        search(searchTerm, offset);

    });

    //Start with an empty search to initlize log table
    search("");
});

</script>