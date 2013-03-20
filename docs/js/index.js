function delete_entry(item, event_time) {
	$(item).parent().remove();
	$.post('/ajax_delete.php', { delete: event_time }, function (data) { $('#info').html(data); });
}

function expand() {
	width = $(document).width();
	height = $(document).height();

	imgwidth = 640;
	imgheight = 480;

	if (width < 640 || height < 480) {
		// something is not correct, resize to smallest dimension
		diffw = 640 - width;
		diffh = 480 - height;
		if (diffw > diffh) {
			imgwidth = width;
			imgheight = (480 / 640) * width;
			imgleft = 0;
			imgtop = height/2 - imgheight/2;
		} else {
			imgheight = height;
			imgwidth = (640 / 480) * height;
			imgleft = width/2 - imgwidth/2;
			imgtop = 0;
		}
	} else {
		imgleft = width/2 - imgwidth/2;
		imgtop = height/2 - imgheight/2;
	}

	$("#live_background").css({display:'block'}).click(function(e) { $("#live_video").unbind(e); collapse(); });

	
	$("#live_video").height(imgheight).width(imgwidth);

	$("#live_video").css({position:"absolute", left: imgleft, top: imgtop, "z-index":999});
	$("#live_video").click(function(e) { $(this).unbind(e); collapse(); });
}

function collapse() {
	item = $("#live_video");
	$("#live_video").height(120);
	$("#live_video").width(160);
	$("#live_video").css({position:"relative",left:"0px",top:"0px"});

	$("#live_background").css({display:'none'}).unbind('click');
	$("#live_video").click(function(e) { $(this).unbind(e); expand(); });
}

$(function() {
$("body").append('<div id="live_background" style="display:none;position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:998;opacity:0.6;filter:alpha(opacity=60);background-color:#000000;"></div>');
$("#live_video").click(function(e) { $(this).unbind(e); expand(); });
});
