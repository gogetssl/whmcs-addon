<p>Click button to load HTML content via AJAX </p>

<div id="jquery-load-content"></div>

<a href="#" class="btn btn-primary jquery-load">jQuery Load</a>
<a href="#" class="btn btn-danger" data-act="ajaxContent" data-act-target="#jquery-load-content">DataAct Load</a>

<script>
    {literal}
        jQuery(function(){
            jQuery(".jquery-load").click(function(){
                JSONParser.request('ajaxContent', {}, function(data){
                    $("#jquery-load-content").html(data.html);
                });
            });
            
            jQuery(".getErrorArray").click(function(){
                JSONParser.request('getErrorArray');
            }); 
        });
    {/literal}
</script>