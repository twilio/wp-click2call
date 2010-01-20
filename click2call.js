jQuery(document).ready(function() {
    jQuery('.click2call').each(function() {
        swfobject.embedSWF(click2callL10n.plugin_url + '/dialer.swf', jQuery(this).attr('id'), '230px', '55px', '9.0.0');
    });
});

var StartCall = function(caller) {
    if(caller && caller.length >= 9) {
        jQuery.ajax({
            url : click2callL10n.plugin_url + '/dialer.php',
            data : {
                caller : caller
            }
        });
    }
};