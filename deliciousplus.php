<?php
/*
Plugin Name: delicious-plus widget
Description: Adds a sidebar widget to display delicious links
Author: David Lynch
Version: 1.2.1
Author URI: http://davidlynch.org
*/
/*
	Copyright 2008  David Lynch (kemayo@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Using the multi-widget pattern from wp-includes/widgets.php

// This saves options and prints the widget's config form.
function widget_deliciousplus_control($widget_args = 1) {
	global $wp_registered_widgets;
	static $updated = false; // Whether or not we have already updated the data after a POST submit
	
	if(is_numeric($widget_args)) {
		$widget_args = array('number' => $widget_args);
	}
	$widget_args = wp_parse_args($widget_args, array('number' => -1));
	extract($widget_args, EXTR_SKIP);
	
	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$options = get_option('widget_deliciousplus');
	if(!is_array($options)) {
		$options = array();
	}
	
	if(!$updated && !empty($_POST['sidebar'])) {
		// Tells us what sidebar to put the data in
		$sidebar = (string) $_POST['sidebar'];
		$sidebars_widgets = wp_get_sidebars_widgets();
		if(isset($sidebars_widgets[$sidebar])) {
			$this_sidebar =& $sidebars_widgets[$sidebar];
		} else {
			$this_sidebar = array();
		}
		
		foreach($this_sidebar as $_widget_id) {
			// Remove all widgets of this type from the sidebar.  We'll add the new data in a second.  This makes sure we don't get any duplicate data
			// since widget ids aren't necessarily persistent across multiple updates
			if('widget_deliciousplus' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number'])) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				if(!in_array("deliciousplus-$widget_number", $_POST['widget-id'])) { 
					// the widget has been removed. "deliciousplus-$widget_number" is "{id_base}-{widget_number}
					unset($options[$widget_number]);
				}
			}
		}
		
		foreach((array)$_POST['widget-deliciousplus'] as $widget_number => $widget_deliciousplus_instance) {
			// compile data from $widget_many_instance
			$options[$widget_number] = array(
				'title' => strip_tags(stripslashes(wp_specialchars($widget_deliciousplus_instance['title']))),
				'username' => strip_tags(stripslashes(wp_specialchars($widget_deliciousplus_instance['username']))),
				'count' => (int)$widget_deliciousplus_instance['count'],
				'showtags' => $widget_deliciousplus_instance['showtags'] == 'y',
				'favicon' => $widget_deliciousplus_instance['favicon'] == 'y',
				'description' => $widget_deliciousplus_instance['description'] == 'y',
				'tags' => explode(' ', trim(strip_tags(stripslashes(wp_specialchars($widget_deliciousplus_instance['tags']))))),
			);
		}
		
		update_option('widget_deliciousplus', $options);
		$updated = true; // So that we don't go through this more than once
	}
	
	// Here we echo out the form
	if(-1 == $number) { // We echo out a template for a form which can be converted to a specific form later via JS
		$count = 10;
		$username = 'wordpress';
		$title = 'delicious';
		$showtags = false;
		$favicon = false;
		$description = false;
		$tags = '';
		$number = '%i%';
	} else {
		$title = attribute_escape($options[$number]['title']);
		$count = attribute_escape($options[$number]['count']);
		$username = attribute_escape($options[$number]['username']);
		$title = attribute_escape($options[$number]['title']);
		$showtags = $options[$number]['showtags'];
		$favicon = $options[$number]['favicon'];
		$description = $options[$number]['description'];
		$tags = attribute_escape(implode(' ', $options[$number]['tags']));
	}
	
	// The form has inputs with names like widget-many[$number][something] so that all data for that instance of
	// the widget are stored in one $_POST variable: $_POST['widget-many'][$number]
	
	?>
		<p>
			<label for="deliciousplus-title-<?php echo $number; ?>">
				<?php _e('Widget title:', 'widgets'); ?>
				<input type="text" class="widefat" id="deliciousplus-title-<?php echo $number; ?>" name="widget-deliciousplus[<?php echo $number; ?>][title]" value="<?php echo $title; ?>" />
			</label><br />
			<label for="deliciousplus-username-<?php echo $number; ?>">
				<?php _e('delicious login:', 'widgets'); ?>
				<input type="text" class="widefat" id="deliciousplus-username-<?php echo $number; ?>" name="widget-deliciousplus[<?php echo $number; ?>][username]" value="<?php echo $username; ?>" />
			</label><br />
			<label for="deliciousplus-count-<?php echo $number; ?>">
				<?php _e('Number of links:', 'widgets'); ?>
				<input type="text" class="widefat" id="deliciousplus-count-<?php echo $number; ?>" name="widget-deliciousplus[<?php echo $number; ?>][count]" value="<?php echo $count; ?>" />
			</label><br />
			<label for="deliciousplus-tags-<?php echo $number; ?>">
				<?php _e('Show only these tags (separated by spaces):', 'widgets'); ?>
				<textarea id="deliciousplus-tags-<?php echo $number; ?>" name="widget-deliciousplus[<?php echo $number; ?>][tags]" class="widefat" cols="15", rows="2"><?php echo $tags; ?></textarea>
			</label><br />
		</p>
		<p>
			<label for="deliciousplus-description-<?php echo $number; ?>">
				<input type="checkbox" class="checkbox" id="deliciousplus-description-<?php echo $number; ?>" name="widget-deliciousplus[<?php echo $number; ?>][description]" value="y" <?php if($description) { echo 'checked="checked"'; } ?> />
				<?php _e('Show description', 'widgets'); ?>
			</label><br />
			<label for="deliciousplus-showtags-<?php echo $number; ?>">
				<input type="checkbox" class="checkbox" id="deliciousplus-showtags-<?php echo $number; ?>" name="widget-deliciousplus[<?php echo $number; ?>][showtags]" value="y" <?php if($showtags) { echo 'checked="checked"'; } ?> />
				<?php _e('Show tags', 'widgets'); ?>
			</label><br />
			<label for="deliciousplus-favicon-<?php echo $number; ?>">
				<input type="checkbox" class="checkbox" id="deliciousplus-favicon-<?php echo $number; ?>" name="widget-deliciousplus[<?php echo $number; ?>][favicon]" value="y" <?php if($favicon) { echo 'checked="checked"'; } ?> />
				<?php _e('Show favicon', 'widgets'); ?>
			</label><br />
			<input type="hidden" name="widget-deliciousplus[<?php echo $number; ?>][submit]" id="deliciousplus-submit-<?php echo $number; ?>" value="1" />
		</p>
	<?php
}

// This prints the widget
function widget_deliciousplus($args, $widget_args = 1) {
	extract($args, EXTR_SKIP);
	if(is_numeric($widget_args)) {
		$widget_args = array( 'number' => $widget_args );
	}
	$widget_args = wp_parse_args($widget_args, array('number' => -1));
	extract($widget_args, EXTR_SKIP);
	
	$defaults = array('count' => 10, 'username' => 'wordpress', 'title' => 'delicious',);
	$options = get_option('widget_deliciousplus');
	if(!isset($options[$number])) { return; }
	$options = $options[$number];

	foreach ($defaults as $key => $value) {
		if (!isset($options[$key]) or $options[$key] == '') {
			$options[$key] = $defaults[$key];
		}
	}

	$tags = false;
	if($options['tags'] && ((count($options['tags']) > 1) || ($options['tags'][0] != ''))) {
		$tags = $options['tags'];
	}

	$json_url = 'http://feeds.delicious.com/v2/json/' . rawurlencode($options['username']);
	$json_url.= $tags ? '/' . rawurlencode(implode('+', $tags)) : '';
	$json_url.= '?count=' . ((int) $options['count']) . '&callback=makeItDelicious';
	
	echo $before_widget;
	echo $before_title . "<a href='http://delicious.com/{$options['username']}'>{$options['title']}</a>" . $after_title;
	?>
	<div id="deliciousplus-box-<?php echo $number; ?>" style="margin:0;padding:0;border:none;"> </div>
	<script type="text/javascript">
	var Delicious;
	function makeItDelicious(data) {
		Delicious = data;
	}
	</script>
	<script type="text/javascript" src="<?php echo $json_url; ?>"></script>
	<script type="text/javascript">
	function showImage(img){ return (function(){ img.style.display='inline'; }) }
	var ul = document.createElement('ul');
	for (var i=0, post; post = Delicious[i]; i++) {
		var li = document.createElement('li');
		var a = document.createElement('a');
		a.setAttribute('href', post.u);
		a.setAttribute('class', 'deliciousplus-post');
		a.innerHTML = post.d;
		<?php if($options['favicon']) {?>
		var img = document.createElement('img');
		img.style.display = 'none';
		img.height = img.width = 16;
		img.src = post.u.split('/').splice(0,3).join('/')+'/favicon.ico';
		img.onload = showImage(img);
		li.appendChild(img);
		<?php }?>
		li.appendChild(a);
		<?php if($options['description']) {?>if (post.n) { li.innerHTML += ': <span class="deliciousplus-description">'+unescape(post.n)+'</span>' }<?php }?>
		<?php if($options['showtags']) {?>
		if (post.t.length > 0) {
			li.appendChild(document.createTextNode(' / '));
			var tags = document.createElement('span');
			tags.setAttribute('class', 'deliciousplus-tags');
			for(var j=0, tag; tag = post.t[j]; j++) {
				var ta = document.createElement('a');
				ta.setAttribute('href', 'http://delicious.com/<?php echo $options['username'];?>/'+encodeURIComponent(tag));
				ta.appendChild(document.createTextNode(tag));
				tags.appendChild(ta);
				tags.appendChild(document.createTextNode(' '));
			}
			li.appendChild(tags);
		}
		<?php }?>
		ul.appendChild(li);
	}
	document.getElementById('deliciousplus-box-<?php echo $number; ?>').appendChild(ul);
	</script>
	<noscript><a href="http://delicious.com/<?php echo $options['username']; ?>">my delicious</a></noscript>
<?php
	echo $after_widget;
}

function widget_deliciousplus_init() {
	if(!$options = get_option('widget_deliciousplus')) {
		$options = array();
	}
	
	$widget_ops = array('classname' => 'widget_deliciousplus', 'description' => __('A widget to display delicious links'));
	$control_ops = array('width' => 400, 'height' => 350, 'id_base' => 'deliciousplus');
	$name = __('delicious plus');

	if(isset($options['username'])) {
		// Upgrading from an old version, pre-multi.
		$options = array(
			1 => array(
				'title' => $options['title'],
				'username' => $options['username'],
				'count' => $options['count'],
				'showtags' => $options['showtags'],
				'favicon' => $options['favicon'],
				'description' => $options['description'],
				'tags' => $options['tags'],
			),
		);
		update_option('widget_deliciousplus', $options);
	}

	$registered = false;
	foreach(array_keys($options) as $o) {
		// Old widgets can have null values for some reason
		if(!isset($options[$o]['username'])) {
			continue;
		}

		// $id should look like {$id_base}-{$o}
		$id = "deliciousplus-$o"; // Never never never translate an id
		$registered = true;
		wp_register_sidebar_widget($id, $name, 'widget_deliciousplus', $widget_ops, array('number' => $o));
		wp_register_widget_control($id, $name, 'widget_deliciousplus_control', $control_ops, array('number' => $o));
	}

	// If there are none, we register the widget's existance with a generic template
	if(!$registered) {
		wp_register_sidebar_widget('deliciousplus-1', $name, 'widget_deliciousplus', $widget_ops, array('number' => -1));
		wp_register_widget_control('deliciousplus-1', $name, 'widget_deliciousplus_control', $control_ops, array('number' => -1));
	}
}

add_action('widgets_init', 'widget_deliciousplus_init');

?>
