Last_cam_update = $.now();

function clear_info() {
	$('#info').html('&nbsp');
}

function delete_entry(item, event_time) {
	$(item).parent().remove();
	$.post('/ajax_delete.php', { delete: event_time }, function (data) { $('#info').html(data).slideDown(); window.setTimeout(clear_info, 5000); });
}

function release_gate() {
	$(this).attr("disabled", true);
	$.get('ajax_change_gate.php', { release: true, id: $(this).attr("form") }, function (data) { $('#info').html(data).slideDown(); window.setTimeout(clear_info, 5000); update_gate_status(); });
}

function open_gate() {
	$(this).attr("disabled", true);
	$.get('ajax_change_gate.php', { open: true, id: $(this).attr("form") }, function (data) { $('#info').html(data).slideDown(); window.setTimeout(clear_info, 5000); update_gate_status(); });
}

function hold_gate() {
	$(this).attr("disabled", true);
	$.get('ajax_change_gate.php', { hold: true, id: $(this).attr("form") }, function (data) { $('#info').html(data).slideDown(); window.setTimeout(clear_info, 5000); update_gate_status(); });
}

function handle_gate_status_response($data) {
	$("#gate_control_div").html('');
	$.each( $data, function( gate_num, gate_data) {
		var $status = $('<div class="gate_status">').append($('<div class="gate_name">').html(gate_data.name));

		if (gate_data.state == "MOVING") {
			$status.append($('<div class="gate_state">').html("Currently Moving"));
			$status.append($('<input type="button" value="Open Gate" disabled />'));
		} else if (gate_data.state == "CLOSED") {
			$status.append($('<div class="gate_state">').html("Closed"));
			$status.append($('<input type="button" value="Open Gate" />').attr("form", String(gate_num)).click(open_gate));
		} else if (gate_data.state == "OPEN") {
			$status.append($('<div class="gate_state">').html("Open"));
			$status.append($('<input type="button" value="Close Gate" disabled />'));
		} else {
			$status.append($('<div class="gate_state">').html("Unknown!"));
		}

		$status.append($('<div class="gate_hold_state">').html(gate_data.hold_state));

		if (gate_data.hold_state == "HELD BY US") {
			$status.append($('<input type="button" value="Release Gate" />').attr("form", String(gate_num)).click(release_gate));
		} else {
			$status.append($('<input type="button" value="Hold Gate Open" />').attr("form", String(gate_num)).click(hold_gate));
		}

		$("#gate_control_div").append($status);
		
	});
//	window.setTimeout(update_gate_status, 1000);
}

function update_gate_status() {
	$.getJSON('ajax_gate_status.php').done(handle_gate_status_response);
}

function expand() {
	width = $(document).width();
	height = $(window).height();

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

	$("#live_background").css({display:'block',height:'100%'}).click(function(e) { $("#live_video").unbind(e); collapse(); });

	
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

function refresh_entries() {
	// first look for anything with the class refresh and update it
	$(".refresh").each(
		function () { 
			$.post('ajax_update.php', { id: $(this).data('refresh') }, 
				jQuery.proxy(function(data) { 
					if (data) {
						$(this).attr("src", data);
						$(this).removeClass("refresh"); 
					}
				}, $(this)));
		}
	);
}

function update_entries() {
	// just in case .post blocks, don't want to miss an update
//	update_time = $.now();
//	$.post('ajax_list.php', { last_update: Last_cam_update }, function(data) { if (data) { $("#camera_events").prepend(data); } }).fail(function() { alert("FAILED TO UPDATE!"); window.location.reload(); });
//	Last_cam_update = update_time;
	$.post('ajax_list.php', { last_update: Last_cam_update }, function(data) { if (data) { $("#camera_events").html(data); } }).fail(function() { alert("FAILED TO UPDATE!"); window.location.reload(); });

	// reload html5lightbox
//	html5Lightbox.elemArray = new Array();
//	html5Lightbox.readData();
	jQuery(".html5lightbox").html5lightbox();

//	html5Lightbox.unbind('click').click(html5Lightbox.clickHandler);
}

$(function() {
	if ($("#live_video_div").length > 0 ) {
		$("body").append('<div id="live_background" style="display:none;position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:998;opacity:0.6;filter:alpha(opacity=60);background-color:#000000;"></div>');
		$("#live_video").click(function(e) { $(this).unbind(e); expand(); });
		//window.setInterval(update_entries, 60000);
		//window.setInterval(refresh_entries, 5000);
//		window.setTimeout(update_entries, 1000);
		if ($(window).width() > 480) {
			$("#live_video_div").css("width", "auto").css("height", $(window).height() - 240);
//		$("#live_label).css("width", "100%");
			$("#live_video").css("width", "auto").css("height", $(window).height() - 240 - 5 - $("#live_label").outerHeight(true));
			$("#live_video_img").css("width", "auto").css("height", $(window).height() - 240 - 5 - $("#live_label").outerHeight(true)); 
		}
	}
	update_gate_status();
});
