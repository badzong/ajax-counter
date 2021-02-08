# ajax-counter

Most simplistic view counter plugin for Wordpress installations with high
traffic volume. Created as an alternative to the view counter in Yoast.

USAGE:

Just activate the plugin and call get_post_meta within your template:

  get_post_meta( get_the_ID(), 'ajax_counter_views', true );

