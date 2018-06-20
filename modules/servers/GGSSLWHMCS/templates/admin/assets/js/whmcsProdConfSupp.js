jQuery(document).ready(function(){
    jQuery('#MGNextIsWHMCSConfig').next().hide();
    
    var relation = {};
    
    jQuery('#MGNextIsWHMCSConfig').next().find('input').each(function(){
        
        var name = jQuery(this).parent().prev().text();
        
        relation[name] = jQuery(this).attr('name');
        
        jQuery('*[name="'+name+'"]').change(function(){
            var tname = jQuery(this).attr('name');
            jQuery('input[name="'+relation[tname]+'"]').val(jQuery(this).val());
        }).change();
    });
    
    $('*[data-is-form] button[name="mg-action"]').click(function(){
        data = jQuery(this).closest('*[data-is-form]').MGGetForms();
        JSONParser.request(jQuery(this).val(),data,function(result){
            if(result.success){
                jQuery('#MGAlerts').alerts('success',result.success);
            }else{
                jQuery('#MGAlerts').alerts('danger',result.error);
            }
        });
    });
});