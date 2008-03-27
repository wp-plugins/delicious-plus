<?php
/*
Plugin Name: del.icio.us-plus widget
Description: Adds a sidebar widget to display del.icio.us links
Author: David Lynch (based on the Automattic widget)
Version: 1.0
Author URI: http://davidlynch.org
*/
/*  Copyright 2008  David Lynch (kemayo@gmail.com)

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

function widget_deliciousplus_init() {
	// Check for the required API functions
	if (!function_exists('register_sidebar_widget') || !function_exists('register_widget_control')) {
		return;
	}

	// This saves options and prints the widget's config form.
	function widget_deliciousplus_control() {
		$options = $newoptions = get_option('widget_deliciousplus');
		if ($_POST['deliciousplus-submit']) {
			$newoptions['title'] = strip_tags(stripslashes($_POST['deliciousplus-title']));
			$newoptions['username'] = strip_tags(stripslashes($_POST['deliciousplus-username']));
			$newoptions['count'] = (int) $_POST['deliciousplus-count'];
			$newoptions['showtags'] = $_POST['deliciousplus-showtags'] == 'y';
			$newoptions['favicon'] = $_POST['deliciousplus-favicon'] == 'y';
			$newoptions['description'] = $_POST['deliciousplus-description'] == 'y';
			$newoptions['tags'] = explode(' ', trim(strip_tags(stripslashes($_POST['deliciousplus-tags']))));
		}
		if ($options != $newoptions) {
			$options = $newoptions;
			update_option('widget_deliciousplus', $options);
		}
		?>
			<div style="text-align:right">
				<label for="deliciousplus-title" style="line-height:35px;display:block;"><?php _e('Widget title:', 'widgets'); ?> <input type="text" id="deliciousplus-title" name="deliciousplus-title" value="<?php echo wp_specialchars($options['title'], true); ?>" /></label>
				<label for="deliciousplus-username" style="line-height:35px;display:block;"><?php _e('del.icio.us login:', 'widgets'); ?> <input type="text" id="deliciousplus-username" name="deliciousplus-username" value="<?php echo wp_specialchars($options['username'], true); ?>" /></label>
				<label for="deliciousplus-count" style="line-height:35px;display:block;"><?php _e('Number of links:', 'widgets'); ?> <input type="text" id="deliciousplus-count" name="deliciousplus-count" value="<?php echo $options['count']; ?>" /></label>
				<label for="deliciousplus-description" style="line-height:35px;display:block;"><?php _e('Show description:', 'widgets'); ?> <input type="checkbox" id="deliciousplus-description" name="deliciousplus-description" value="y" <?php if($options['description']) { echo 'checked="checked"'; } ?> /></label>
				<label for="deliciousplus-showtags" style="line-height:35px;display:block;"><?php _e('Show tags:', 'widgets'); ?> <input type="checkbox" id="deliciousplus-showtags" name="deliciousplus-showtags" value="y" <?php if($options['showtags']) { echo 'checked="checked"'; } ?> /></label>
				<label for="deliciousplus-favicon" style="line-height:35px;display:block;"><?php _e('Show favicon:', 'widgets'); ?> <input type="checkbox" id="deliciousplus-favicon" name="deliciousplus-favicon" value="y" <?php if($options['favicon']) { echo 'checked="checked"'; } ?> /></label>
				<label for="deliciousplus-tags" style="line-height:35px;display:block;"><?php _e('Show only these tags (separated by spaces):', 'widgets'); ?> <textarea id="deliciousplus-tags" name="deliciousplus-tags" style="width:290px;height:20px;"><?php echo wp_specialchars(implode(' ', (array) $options['tags']), true); ?></textarea></label>
				<input type="hidden" name="deliciousplus-submit" id="deliciousplus-submit" value="1" />
			</div>
		<?php
	}

	// This prints the widget
	function widget_deliciousplus($args) {
		extract($args);
		$defaults = array('count' => 10, 'username' => 'wordpress', 'title' => 'del.icio.us',);
		$options = (array) get_option('widget_deliciousplus');

		foreach ($defaults as $key => $value) {
			if (!isset($options[$key]) or $options[$key] == '') {
				$options[$key] = $defaults[$key];
			}
		}

		$json_url = 'http://del.icio.us/feeds/json/' . rawurlencode($options['username']);
		$json_url.= count($options['tags']) ? '/' . rawurlencode(implode('+', $options['tags'])) : '';
		$json_url.= '?count=' . ((int) $options['count']) . ';';
		?>
		<?php echo $before_widget; ?>
			<?php echo $before_title . "<a href='http://del.icio.us/{$options['username']}'>{$options['title']}</a>" . $after_title; ?><div id="deliciousplus-box" style="margin:0;padding:0;border:none;"> </div>
			<script type="text/javascript" src="<?php echo $json_url; ?>"></script>
			<script type="text/javascript">
			function showImage(img){ return (function(){ img.style.display='inline'; }) }
			var ul = document.createElement('ul');
			for (var i=0, post; post = Delicious.posts[i]; i++) {
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
						ta.setAttribute('href', 'http://del.icio.us/<?php echo $options['username'];?>/'+encodeURIComponent(tag));
						ta.appendChild(document.createTextNode(tag));
						tags.appendChild(ta);
						tags.appendChild(document.createTextNode(' '));
					}
					li.appendChild(tags);
				}
				<?php }?>
				ul.appendChild(li);
			}
			document.getElementById('deliciousplus-box').appendChild(ul);
			</script>
			<noscript><a href="http://del.icio.us/kemayo">my del.icio.us</a></noscript>
		<?php echo $after_widget; ?>
		<?php
	}

	// Tell Dynamic Sidebar about our new widget and its control
	register_sidebar_widget(array('del.icio.usplus', 'widgets'), 'widget_deliciousplus');
	register_widget_control(array('del.icio.usplus', 'widgets'), 'widget_deliciousplus_control');
	
}

add_action('widgets_init', 'widget_deliciousplus_init');

?>