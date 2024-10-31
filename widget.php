<?php

class R34OnThisDay extends WP_Widget {


	public $version = '';
	public $default_title = 'On This Day';
	public $default_no_posts_message = 'Nothing has ever happened on this day. <em>Ever.</em>';
	public $default_see_all_link_text = 'See all...';
	public $default_posts_per_page = 10;
	public $displayed_lists = array();


	protected $shortcode_defaults = array(
		'after_title' => '</h3>',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'before_widget' => '<aside class="widget widget_r34otd">',
		'categories' => null,
		'day' => null,
		'month' => null,
		'no_posts_message' => 'Nothing has ever happened on this day. Ever.',
		'posts_per_page' => 10,
		'see_all_link_text' => 'See all...',
		'show_archive_link' => false,
		'show_post_date' => false,
		'show_post_dates' => null, // Alias
		'show_post_excerpt' => false,
		'show_post_excerpts' => null, // Alias
		'show_post_thumbnail' => false,
		'show_post_thumbnails' => null, // Alias
		'title' => 'On This Day',
		'use_post_date' => false,
	);


	public function __construct() {
		parent::__construct('r34otd', 'On This Day');
		
		// Set version
		$this->version = $this->_get_version();

		// Default text strings with translations
		$this->default_title = __('On This Day', 'r34otd');
		$this->default_no_posts_message = __('Nothing has ever happened on this day. Ever.', 'r34otd');
		$this->default_see_all_link_text = __('See all...', 'r34otd');
		$this->shortcode_defaults['default_title'] = $this->default_title;
		$this->shortcode_defaults['default_no_posts_message'] = $this->default_no_posts_message;
		$this->shortcode_defaults['default_see_all_link_text'] = $this->default_see_all_link_text;
		
		// Enqueue admin CSS
		add_action('admin_enqueue_scripts', function() {
			wp_enqueue_style('r34otd-admin-css', plugin_dir_url(__FILE__) . 'r34otd-admin.css', array(), $this->version);
		}, 10);

		// Enqueue front-end CSS
		add_action('wp_enqueue_scripts', function() {
			wp_enqueue_style('r34otd-css', plugin_dir_url(__FILE__) . 'r34otd-style.css', array(), $this->version);
		}, 10);

		// Add OTD shortcode
		add_shortcode('on_this_day', array(&$this, 'shortcode'));
		
		// Change excerpt parameters for OTD widget
		add_filter('excerpt_length', array(&$this, 'excerpt_length'));
		add_filter('excerpt_more', array(&$this, 'excerpt_more'));
		
	}
	
	
	public function excerpt_length($number) {
		global $r34otd_loop, $r34otd_excerpt_length;
		if (!empty($r34otd_loop) && !empty($r34otd_excerpt_length)) {
			$number = $r34otd_excerpt_length;
		}
		return $number;
	}


	public function excerpt_more($more_string) {
		global $r34otd_loop;
		if (!empty($r34otd_loop)) {
			$more_string = '&hellip;';
		}
		return $more_string;
	}


	public function form($instance) {
		?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
				<strong><?php _e('Title', 'r34otd'); ?>:</strong>
				<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($instance['title'] ? $instance['title'] : $this->default_title); ?>" /><br />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('no_posts_message')); ?>">
				<strong><?php _e('Message to display if no posts are found', 'r34otd'); ?>:</strong>
				<input class="widefat" id="<?php echo esc_attr($this->get_field_id('no_posts_message')); ?>" name="<?php echo esc_attr($this->get_field_name('no_posts_message')); ?>" type="text" value="<?php echo esc_attr($instance['no_posts_message'] ? $instance['no_posts_message'] : $this->default_no_posts_message); ?>" /><br />
				<small class="r34otd-small"><?php _e('Leave blank to hide widget altogether if no posts are found.', 'r34otd'); ?></small>
			</label>
		</p>
		<hr />
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('posts_per_page')); ?>">
				<strong><?php _e('Maximum posts to display', 'r34otd'); ?>:</strong>
				<input class="widefat" id="<?php echo esc_attr($this->get_field_id('posts_per_page')); ?>" name="<?php echo esc_attr($this->get_field_name('posts_per_page')); ?>" type="number" max="999" min="1" size="3" step="1" value="<?php echo intval($instance['posts_per_page'] ? $instance['posts_per_page'] : $this->default_posts_per_page); ?>" /><br />
			</label>
		</p>
		<details style="margin: 1em 0;">
			<summary><strong><?php _e('Additional Options', 'r34otd'); ?></strong></summary>
			<?php
			// Walker for category checkboxes
			if (!class_exists('R34OnThisDay_Walker_Category_Checklist')) {
				r34otd_walker_category_checklist_init();
			}
			// Function wp_category_checklist() is defined in wp-admin/includes/template.php,
			// which gets loaded by r34otd_walker_category_checklist_init()
			if (function_exists('wp_category_checklist')) {
				$walker = new R34OnThisDay_Walker_Category_Checklist($this->get_field_name('categories'), $this->get_field_id('categories'));
				?>
				<p>
					<label for="<?php echo esc_attr($this->get_field_id('categories')); ?>">
						<strong><?php _e('Category (optional)', 'r34otd'); ?>:</strong>
						<ul class="r34otd-scrolling"><?php wp_category_checklist(0, 0, $instance['categories'], false, $walker, false); ?></ul>
						<small class="r34otd-small"><?php _e('If none are selected, results will include all categories.', 'r34otd'); ?></small>
					</label>
				</p>
				<?php
			}
			?>
			<p>
				<label for="<?php echo esc_attr($this->get_field_id('show_archive_link')); ?>">
					<input class="widefat" id="<?php echo esc_attr($this->get_field_id('show_archive_link')); ?>" name="<?php echo esc_attr($this->get_field_name('show_archive_link')); ?>" type="checkbox"<?php if (!empty($instance['show_archive_link'])) { echo ' checked="checked"'; } ?>" /> <strong><?php _e('Show On This Day archive link', 'r34otd'); ?></strong><br />
					<small class="r34otd-small"><?php _e('Adds a "See all..." link at the bottom of the widget, which takes the user to an archive page listing all posts for the current date.', 'r34otd'); ?></small>
				</label>
			</p>
			<p>
				<label for="<?php echo esc_attr($this->get_field_id('see_all_link_text')); ?>">
					<strong><?php _e('Text to display for "See all..." link', 'r34otd'); ?>:</strong>
					<input class="widefat" id="<?php echo esc_attr($this->get_field_id('see_all_link_text')); ?>" name="<?php echo esc_attr($this->get_field_name('see_all_link_text')); ?>" type="text" value="<?php echo esc_attr($instance['see_all_link_text'] ? $instance['see_all_link_text'] : $this->default_see_all_link_text); ?>" />
				</label>
			</p>
			<hr />
			<p>
				<label for="<?php echo esc_attr($this->get_field_id('show_post_thumbnail')); ?>">
					<input class="widefat" id="<?php echo esc_attr($this->get_field_id('show_post_thumbnail')); ?>" name="<?php echo esc_attr($this->get_field_name('show_post_thumbnail')); ?>" type="checkbox"<?php if (!empty($instance['show_post_thumbnail'])) { echo ' checked="checked"'; } ?>" /> <strong><?php _e('Show post featured images (if available)', 'r34otd'); ?></strong><br />
				</label>
			</p>
			<p>
				<label for="<?php echo esc_attr($this->get_field_id('show_post_date')); ?>">
					<input class="widefat" id="<?php echo esc_attr($this->get_field_id('show_post_date')); ?>" name="<?php echo esc_attr($this->get_field_name('show_post_date')); ?>" type="checkbox"<?php if (!empty($instance['show_post_date'])) { echo ' checked="checked"'; } ?>" /> <strong><?php _e('Show full post dates', 'r34otd'); ?></strong><br />
					<small class="r34otd-small"><?php _e('<strong>Note:</strong> The year will always be displayed.', 'r34otd'); ?></small>
				</label>
			</p>
			<p>
				<label for="<?php echo esc_attr($this->get_field_id('show_post_excerpt')); ?>">
					<input class="widefat" id="<?php echo esc_attr($this->get_field_id('show_post_excerpt')); ?>" name="<?php echo esc_attr($this->get_field_name('show_post_excerpt')); ?>" type="checkbox"<?php if (!empty($instance['show_post_excerpt'])) { echo ' checked="checked"'; } ?>" /> <strong><?php _e('Show post excerpts', 'r34otd'); ?></strong><br />
				</label>
			</p>
			<?php
			if ($post_types = get_post_types(array('public' => true), 'object')) {
				// Don't include pages or attachments
				unset($post_types['page'], $post_types['attachment']);
				if (count($post_types) > 1) {
					?>
					<hr />
					<p>
						<label for="<?php echo esc_attr($this->get_field_id('include_post_types')); ?>">
							<strong><?php _e('Include post types:', 'r34otd'); ?></strong><br />
							<?php
							foreach ((array)$post_types as $post_type => $pt) {
								?>
								<input class="widefat" id="<?php echo esc_attr($this->get_field_id('include_post_types')); ?>" name="<?php echo esc_attr($this->get_field_name('include_post_types')) . '[' . esc_attr($post_type) . ']'; ?>" type="checkbox"<?php if ((empty($instance['include_post_types']) && $post_type == 'post') || !empty($instance['include_post_types'][$post_type])) { echo ' checked="checked"'; } ?>" />&nbsp;<?php echo wp_kses_post($pt->label); ?>&nbsp;&nbsp;
								<?php
							}
							?>
						</label>
					</p>
					<?php
				}
			}
			?>
			<hr />
			<p>
				<label for="<?php echo esc_attr($this->get_field_id('use_post_date')); ?>">
					<input class="widefat" id="<?php echo esc_attr($this->get_field_id('use_post_date')); ?>" name="<?php echo esc_attr($this->get_field_name('use_post_date')); ?>" type="checkbox"<?php if (!empty($instance['use_post_date'])) { echo ' checked="checked"'; } ?>" /> <strong><?php _e('Use post date', 'r34otd'); ?></strong><br />
					<small class="r34otd-small"><?php _e('When viewing an individual post, the widget will show posts from the same date as the current post, not today&#39;s date. On main blog or archive pages, widget will still show posts from today&#39;s date.', 'r34otd'); ?></small>
				</label>
			</p>
			<p>
				<small class="r34otd-small"><strong><?php _e('Tip: ', 'r34otd'); ?></strong><?php _e('Add multiple widgets, one with "Use post date" checked and one without, to display both today&rsquo;s historical posts and those for the current post&rsquo;s date. If the lists are the same (e.g. the current post was published on today&rsquo;s date), only one will display.', 'r34otd'); ?></small>
			</p>
		</details>
		<hr />
		<p>
			<small class="r34otd-small"><?php printf(__('You can also insert On This Day anywhere using the %1$s shortcode. %2$sLearn More...%3$s', 'r34otd'), '<code>[on_this_day]</code>', '<a href="' . admin_url('options-general.php?page=r34otd') . '">', '</a>'); ?></small>
		</p>
		<?php
	}


	public function shortcode($atts) {

		// Don't do anything in admin
		if (is_admin()) { return; }

		// Extract attributes
		extract(shortcode_atts($this->shortcode_defaults, $atts, 'on_this_day'));
		
		// Handle alias attribute names
		if ($show_post_dates !== null) { $show_post_date = $show_post_dates; }
		if ($show_post_excerpts !== null) { $show_post_excerpt = $show_post_excerpts; }
		if ($show_post_thumbnails !== null) { $show_post_thumbnail = $show_post_thumbnails; }
		
		// Assemble arguments
		$args = array(
			'after_title' => $after_title,
			'after_widget' => $after_widget,
			'before_title' => $before_title,
			'before_widget' => $before_widget,
		);
		
		// Assemble "instance" so we can use the widget() method
		$instance = array(
			'categories' => (!empty($categories) ? explode(',', $categories) : null),
			'day' => $day,
			'month' => $month,
			'no_posts_message' => $no_posts_message,
			'posts_per_page' => $posts_per_page,
			'see_all_link_text' => $see_all_link_text,
			'show_archive_link' => r34otd_boolean_check($show_archive_link),
			'show_post_date' => r34otd_boolean_check($show_post_date),
			'show_post_excerpt' => r34otd_boolean_check($show_post_excerpt),
			'show_post_thumbnail' => r34otd_boolean_check($show_post_thumbnail),
			'title' => $title,
			'use_post_date' => r34otd_boolean_check($use_post_date),
		);
		
		// Convert category slugs to term IDs
		if (!empty($instance['categories'])) {
			foreach ((array)$instance['categories'] as $key => $value) {
				if (!intval($value) && $cat = get_category_by_slug(trim($value))) {
					$instance['categories'][$key] = $cat->term_id;
				}
			}
		}
		
		ob_start();
		$this->widget($args, $instance);
		return ob_get_clean();
		
	}


	public function update($new_instance, $old_instance) {
		return $new_instance;
	}
	
	
	public function widget($args, $instance) {
		extract($args);
		
		// Bail out now if we're on an archive page and this instance is using post date
		if (!is_single() && !empty($instance['use_post_date'])) { return false; }

		// Set title
		if (empty($instance['title'])) {
			$instance['title'] = $this->default_title;
		}

		// Set no posts message
		if (empty($instance['no_posts_message'])) {
			$instance['no_posts_message'] = $this->default_no_posts_message;
		}
		
		// Set see all link text
		if (empty($instance['see_all_link_text'])) {
			$instance['see_all_link_text'] = $this->default_see_all_link_text;
		}

		// Get historical posts
		// Current post's date
		if (is_singular() && !empty($instance['use_post_date'])) {
			$date_query = null;
			$monthnum = get_the_date('n');
			$day = get_the_date('j');
		}
		// Arbitrary date
		elseif (!empty($instance['month']) && !empty($instance['day'])) {
			$date_query = null;
			$monthnum = intval($instance['month']);
			$day = intval($instance['day']);
		}
		// Today
		else {
			$date_query = array(array('before' => array('year' => wp_date('Y'))));
			$monthnum = wp_date('n');
			$day = wp_date('j');
		}
		$args = array(
			'date_query' => $date_query,
			'monthnum' => $monthnum,
			'day' => $day,
			'category' => (!empty($instance['categories']) ? implode(',',$instance['categories']) : null),
			'posts_per_page' => intval($instance['posts_per_page']),
		);
		if (isset($instance['include_post_types']) && count((array)$instance['include_post_types']) > 0) {
			$args['post_type'] = array_keys($instance['include_post_types']);
		}
		$historic_posts = get_posts($args);
		
		// Skip display if list is empty and no_posts_message is blank
		if (empty($historic_posts) && empty(trim($instance['no_posts_message']))) { return false; }
		
		// Add hash of this list to displayed, to prevent duplicates if widget is used more than once on a page
		// Check for empty posts ensures no_posts_message will still display
		// @todo Add an option for when a site owner DOES want redundant lists to display
		if (!empty($historic_posts)) {
			$serialized = sha1(serialize($historic_posts));
			if (in_array($serialized, $this->displayed_lists)) { return false; }
			$this->displayed_lists[] = $serialized;
		}
		
		// Build widget display
		echo wp_kses_post($before_widget);

		// Widget title
		echo wp_kses_post($before_title . $instance["title"] . $after_title);
		?>

		<ul class="r34otd">
			<?php
			if (!empty($historic_posts)) {
				global $r34otd_loop, $r34otd_excerpt_length;
				$r34otd_loop = true;
				if (!empty($instance['show_post_excerpt']) && intval($instance['show_post_excerpt']) > 1) {
					$r34otd_excerpt_length = intval($instance['show_post_excerpt']);
				}
				else {
					$r34otd_excerpt_length = 25;
				}
				foreach ($historic_posts as $hpost) {
					// Get the permalink (to avoid running this function multiple times)
					$hpost_permalink = get_permalink($hpost->ID);
					?>
					<li>
						<?php
						if (!empty($instance['show_post_thumbnail']) && $hpost_thumbnail = get_the_post_thumbnail($hpost->ID)) {
							echo '<a href="' . esc_url($hpost_permalink) . '">' . wp_kses_post($hpost_thumbnail) . '</a>';
						}
						?>
						<div class="r34otd-headline"><a href="<?php echo esc_url($hpost_permalink); ?>"><?php echo get_the_title($hpost->ID); ?></a></div>
						<?php
						$r34otd_date_format = !empty($instance['show_post_date']) ? get_option('date_format') : 'Y';
						echo '<div class="r34otd-dateline post-date">' . get_the_date($r34otd_date_format, $hpost) . '</div>';
						if (!empty($instance['show_post_excerpt']) && $hpost_excerpt = get_the_excerpt($hpost->ID)) {
							echo '<div class="r34otd-excerpt post-excerpt">' . wp_kses_post($hpost_excerpt) . '</div>';
						}
						?>
					</li>
					<?php
				}
				$r34otd_loop = false;
			}

			else {
				echo '<li>' . wp_kses_post($instance['no_posts_message']) . '</li>';
			}
			?>
		</ul>

		<?php
		if (!empty($instance['show_archive_link']) && !empty($historic_posts)) {
			// Current post's date
			if (!empty($instance['use_post_date'])) {
				$archive_link = home_url('/archives/otd/' . wp_date('md', strtotime($monthnum . '/' . $day . '/' . wp_date('Y') . ' 12:00 PM')) . '/');
			}
			// Arbitrary date
			elseif (!empty($instance['month']) && !empty($instance['day'])) {
				$archive_link = home_url('/archives/otd/' . wp_date('md', strtotime(intval($instance['month']) . '/' . intval($instance['day']) . '/' . wp_date('Y') . ' 12:00 PM')) . '/');
			}
			// Today
			else {
				$archive_link = home_url('/archives/otd/');
			}
			?>
			<p><a href="<?php echo esc_url($archive_link); ?>"><?php echo wp_kses_post($instance['see_all_link_text']); ?></a></p>
			<?php
		}
		
		echo wp_kses_post($after_widget);
	}


	private function _get_version() {
		if (!function_exists('get_plugin_data')) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}
		$plugin_data = get_plugin_data(dirname(__FILE__) . '/r34-on-this-day.php');
		return $plugin_data['Version'];
	}


}


// Register widget
function r34otd_widgets_init() {
	return register_widget("R34OnThisDay");
}
add_action('widgets_init', 'r34otd_widgets_init', 10, 0);
