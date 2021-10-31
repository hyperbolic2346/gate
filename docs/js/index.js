Last_cam_update = $.now();
Refresh_time = 5000;
Refresh_reset_count = 0;

function clear_info() {
	$('#info').html('&nbsp');
}

function update_quicker_for_a_while() {
	Refresh_time = 1000;
	Refresh_reset_count = 30;
}

function delete_entry(item, event_time) {
	$(item).parent().remove();
	$.post('/ajax_delete.php', { delete: event_time }, function (data) { $('#info').html(data).slideDown(); window.setTimeout(clear_info, 5000); });
	update_quicker_for_a_while();
}

function release_gate() {
	$(this).attr("disabled", true);
	$.get('ajax_change_gate.php', { release: true, id: $(this).attr("form") }, function (data) { $('#info').html(data).slideDown(); window.setTimeout(clear_info, 5000); update_gate_status(); });
}

function open_gate() {
	$(this).attr("disabled", true);
	$.get('ajax_change_gate.php', { open: true, id: $(this).attr("form") }, function (data) { $('#info').html(data).slideDown(); window.setTimeout(clear_info, 5000); update_gate_status(); });
	update_quicker_for_a_while();
}

function hold_gate() {
	$(this).attr("disabled", true);
	$.get('ajax_change_gate.php', { hold: true, id: $(this).attr("form") }, function (data) { $('#info').html(data).slideDown(); window.setTimeout(clear_info, 5000); update_gate_status(); });
	update_quicker_for_a_while();
}

function handle_gate_status_response($data) {
	$("#gate_control_div").html('');
	$.each( $data, function( gate_num, gate_data) {
		var $status = $('<div class="gate_status">').append($('<div class="gate_name">').html(gate_data.name));

		if (gate_data.state == "MOVING") {
			$status.append($('<div class="gate_state">').html("Currently Moving"));
			$status.append($('<button disabled>Open Gate</button>'));
		} else if (gate_data.state == "CLOSED") {
			$status.append($('<div class="gate_state">').html("Closed"));
			$status.append($('<button>Open Gate</button>').attr("form", String(gate_num)).click(open_gate));
		} else if (gate_data.state == "OPEN") {
			$status.append($('<div class="gate_state">').html("Open"));
			$status.append($('<button disabled>Close Gate</button>'));
		} else {
			$status.append($('<div class="gate_state">').html("Unknown!"));
		}

		if (gate_data.hold_state == "HELD BY US") {
			$status.append($('<div class="gate_hold_state">').html("Held By Website"));
			$status.append($('<button>Release Gate</button>').attr("form", String(gate_num)).click(release_gate));
		} else if (gate_data.hold_state == "HELD BY REMOTE") {
			$status.append($('<div class="gate_hold_state">').html("Held by Remote Control"));
			$status.append($('<button>Hold Gate Open</button>').attr("form", String(gate_num)).click(hold_gate));
		} else if (gate_data.hold_state == "NOT HELD") {
			$status.append($('<div class="gate_hold_state">').html("Not Held"));
			$status.append($('<button>Hold Gate Open</button>').attr("form", String(gate_num)).click(hold_gate));
		}

		if (gate_data.info) {
			$status.append($('<div class="gate_info">').html(gate_data.info));
		}

		$("#gate_control_div").append($status);
		
	});
	if (Refresh_reset_count > 0) {
		Refresh_reset_count--;
		if (Refresh_reset_count == 0) {
			Refresh_time = 5000;
		}
	}
	window.setTimeout(update_gate_status, Refresh_time);
}

function update_gate_status() {
	$.getJSON('ajax_gate_status.php').done(handle_gate_status_response);
}

function expand() {
	width = $(window).width() - 30;
	height = $(window).height() - 30;

	width_ratio = 16 / 8 * height;
	if (width_ratio > width) {
		// need to use width as limiting factor
		height = 8 / 16 * width;
	} else {
		width = width_ratio;
	}

	imgleft = $(window).width() / 2 - width / 2;
	imgtop = $(window).height() / 2 - height / 2;;

	$("#live_background").css({display:'block',height:'100%'}).click(function(e) { $("#live_video").unbind(e); collapse(); });

	item = $("#live_video_img");
	item.height(height).width(width);

	item.css({position:"absolute", left: imgleft, top: imgtop, "z-index":999});
	item.click(function(e) { $(this).unbind(e); collapse(); });
}

function collapse() {
	item = $("#live_video_img");
	size_live_feed();
	item.css({position:"relative",left:"0px",top:"0px"});

	$("#live_background").css({display:'none'}).unbind('click');
	item.click(function(e) { $(this).unbind(e); expand(); });
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
	$.post('ajax_list.php', { last_update: Last_cam_update }, function(data) { if (data) { $("#camera_events").html(data); } }).fail(function() { alert("FAILED TO UPDATE!"); window.location.reload(); });

	// reload html5lightbox
	jQuery(".html5lightbox").html5lightbox();
}

function size_live_feed() {
	if ($(window).width() > 480 && $(window).height() > 400) {
		div_height = $(window).height() - 240;	// 240 for one row of thumbnails
		div_width = 16 / 8 * div_height;
		if (div_width > $(window).width() - 500) {
			div_width = $(window).width() - 500;
			div_height = 8 / 16 * div_width;
		}
		$("#live_video_div").css("width", div_width).css("height", div_height);
		$("#live_video").css("width", div_width - 5).css("height", div_height - 5 - $("#live_label").outerHeight(true));
		$("#live_video_img").css("width", div_width - 5).css("height", div_height - 5 - $("#live_label").outerHeight(true)); 
	}
}

$(function() {
	if ($("#live_video_div").length > 0 ) {
		if ($("#live_video_toggle_div").css('display') != 'none') { 
			$("#live_video_toggle_div").append($('<input type="button" value="Show Live Feed" id="live_video_toggle_button" />'));
			$("#live_video_toggle_button").click(
				function(e) {
					$("#live_video_div").toggle();
					if ($("#live_video_div").css('display') == 'none') { 
						$("#live_video_toggle_button").attr('value', 'Show Live Feed');
						$("#live_video_img").attr('src', '');
					} else { 
						$("#live_video_toggle_button").attr('value', 'Hide Live Feed');
						$("#live_video_img").attr('src', $("#live_video_img").data("feed"));
					}
				});

		} else {
			$("#live_video_img").attr('src', $("#live_video_img").data("feed"));
		}
		$("body").append('<div id="live_background" style="display:none;position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:998;opacity:0.6;filter:alpha(opacity=60);background-color:#000000;"></div>');
		$("#live_video_img").click(function(e) { $(this).unbind(e); expand(); });
		size_live_feed();
	}
	if ($("#camera_events_toggle_div").length && $("#camera_events_toggle_div").css('display') != 'none') { 
		$("#camera_events_toggle_div").append($('<input type="button" value="Show Videos" id="camera_events_toggle_button" />'));
		$("#camera_events_toggle_button").click(
			function(e) {
				$("#camera_events").toggle();
				if ($("#camera_events").css('display') == 'none') { 
					$("#camera_events_toggle_button").attr('value', 'Show Videos');
				} else { 
					$("#camera_events_toggle_button").attr('value', 'Hide Videos');
				}
			});
	} else {
		$("#camera_events").css({display:'block'});
	}

	if ($("#gate_control_div")) {
		update_gate_status();
	}
});
