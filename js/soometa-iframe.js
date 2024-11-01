var OnMessage=function(msg)
{
    var data = null;
    data=msg.data;
    if( typeof(data) == 'string' && data.charAt(0) == '{' ) {
	data = jQuery.parseJSON(data);
    }
    console.log('msg recv');
    console.log(data);
    if(typeof(data)=="object") {
	if(typeof(data.action)=="string") {
	    if(data.action == "insert") {
		insert_to_post(data.id,data.width,data.height);
	    }
	}
    }
}

function insert_to_post(id,w,h) {
    var win = window.dialogArguments || opener || parent || top;
    win.send_to_editor('[soometa id="'+id+'" width="'+w+'" height="'+h+'"]');
}

if (window.addEventListener) {  // all browsers except IE before version 9
    window.addEventListener ("message", OnMessage, false);
}
else {
    if (window.attachEvent) {   // IE before version 9
	window.attachEvent("onmessage", OnMessage);
    }
}

jQuery(document).ready(function() {
    if(parent && parent.document)
	jQuery(parent.document).find('#TB_window').addClass('soometa-popup');
    

    jQuery(window).resize(function() {
	var cont = jQuery('.soometa-iframe');
	cont.height(jQuery(window).height() - cont.offset().top);
    }).resize();
    
});

