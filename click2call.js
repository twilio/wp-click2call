jQuery(document).ready(function() {
    jQuery('.click2call').each(function() {
        swfobject.embedSWF(click2callL10n.plugin_url + '/dialer.swf', jQuery(this).attr('id'), '150px', '25px', '9.0.0');
    });
});

var StartCall = function(caller) {
    jQuery.ajax({
        url : click2callL10n.plugin_url + '/dialer.php',
        data : {
            caller : caller
        }
    });
};