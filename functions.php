<?php

// Check for boolean values in shortcode
function r34otd_boolean_check($val) {
	$check = strtolower(trim(strip_tags((string)$val)));
	if ($check === '1' || $check === 'true' || $check === 'on') { return true; }
	if ($check === '0' || $check === 'false' || $check === 'off' || $check === 'none') { return false; }
	if ($check === 'null' || $check === '') { return null; }
	return (bool)$val;
}


// Add category checklist capability for widget configuration
// Based on: http://wordpress.stackexchange.com/questions/124772/using-wp-category-checklist-in-a-widget
// Wrapped in a function to load on demand, because I couldn't find a hook that worked with the Widget Block Editor!
function r34otd_walker_category_checklist_init() {
	if (!class_exists('Walker_Category_Checklist')) {
		require_once(ABSPATH . 'wp-admin/includes/template.php');
	}
	class R34OnThisDay_Walker_Category_Checklist extends Walker_Category_Checklist {
	
		private $name;
		private $id;
	
		function __construct($name = '', $id = '') {
			$this->name = $name;
			$this->id = $id;
		}
	
		function start_el(&$output, $cat, $depth=0, $args=array(), $id=0) {
			extract($args);
			if (empty($taxonomy)) { $taxonomy = 'category'; }
			$output .= "\n" .
				'<li id="' . esc_attr($taxonomy . '-' . $cat->term_id) . '" ' .
				(in_array($cat->term_id, $popular_cats) ? ' class="popular-category"' : '') .
				'>' .
				'<label class="selectit"><input value="' .
				esc_attr($cat->term_id) . '" type="checkbox" name="' . esc_attr($this->name) . '[]" ' .
				'id="in-'. esc_attr($this->id . '-' . $cat->term_id) . '"' .
				checked(in_array($cat->term_id, $selected_cats), true, false) .
				disabled(empty($args['disabled']), false, false) . ' /> ' .
				esc_html(apply_filters('the_category', $cat->name)) .
				'</label>';
		}
	
	}
}