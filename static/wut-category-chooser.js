( function ( $, window) {
	window.wut_category_chooser = function() {
		var target_field_id;
		$('.category-chooser-link').each( function() {
			var target_id = $(this).data('target-field');
			if ( ! target_id.includes('__i__') ) {
				target_field_id = target_id;
			}
		});
		var field_val = $("#" + target_field_id).val();

		var exclude_cat_ids = field_val.split(',');
		var mark_selected = function( level ) {
			for ( k in level) {
				var obj = level[k];
				if (exclude_cat_ids.includes( obj.id.toString() )) {
					level[k].state = { 'selected': true };
				}
				if ( obj.children ) {
					mark_selected( obj.children );
				}
			}
			return level;
		};
		wut_tree_data = mark_selected( wut_tree_data );

		// resize ajax content size.
		tb_position();
		var url;
		$('.category-chooser-link').each(function(){
			url = $(this).attr('href');
		});
		var queryString = url.replace(/^[^\?]+\??/,'');
		var params = tb_parseQuery( queryString );
		TB_WIDTH = (params['width']*1) + 30 || 630; //defaults to 630 if no parameters were added to URL
		TB_HEIGHT = (params['height']*1) + 40 || 440; //defaults to 440 if no parameters were added to UR
		ajaxContentW = TB_WIDTH - 30;
		ajaxContentH = TB_HEIGHT - 45;
		$("#TB_ajaxContent")[0].style.width = ajaxContentW +"px";
		$("#TB_ajaxContent")[0].style.height = ajaxContentH +"px"
		// END: resize ajax content size.

		var $jstree = $('#jstree_category');
		$jstree.jstree({
			'core': {
				'themes' : { 'stripes' : true },
				'data': wut_tree_data
			},
			'plugins': ['checkbox', 'sort'],
			'checkbox': {
				'three_state': false
			}
		});

		$('#submit').click(function(){
			var target_field = $('#' + target_field_id);
			var checked = $jstree.jstree().get_checked();
			target_field.attr('value', checked.join(','));
			target_field.trigger('input');
			tb_remove();
		});
	}
} )( jQuery, window );