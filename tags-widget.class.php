<?php
/*
Plugin Name: Tags all in one
Plugin URI: http://www.teastudio.pl/produkt/tags-all-in-one/
Description: Displays the tags cloud for selected post type (taxonomies)
Version: 1.0.2
Author: Marcin Gierada
Author URI: http://www.teastudio.pl/
Author Email: m.gierada@teastudio.pl
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/


/*
 * plugin
 */
add_action('init', 'tags_all_in_one_action_init');
function tags_all_in_one_action_init() {
        load_plugin_textdomain('tags-all-in-one', false, dirname(plugin_basename( __FILE__ )) .  '/i18n/languages/');
}


/*
 * widget
 */
class TagsAllInOneWidget extends WP_Widget 
{

        function tagsAllInOneWidget() {
                $widget_ops = array('classname' => 'widget_tags_all_in_one','description' => __('Display tags for checked post types', 'tags-all-in-one'));
                $this->WP_Widget('widget_tags_all_in_one', __('Tags all in one', 'tags-all-in-one'), $widget_ops);
        }

        function widget( $args, $instance ) {
                $title = apply_filters('widget_title', $instance['title']);

                echo $args['before_widget'];

                if ($title) {
                        echo $args['before_title'] . $title . $args['after_title'];
                }

                echo TagsAllInOneGenerator::generate($instance);
                echo $args['after_widget'];
         }

        function update ($new_instance, $old_instance) {
                return $new_instance;        
        }   

/**
 * The configuration form.
 */
function form($instance) { 
        /*
         * load defaults if new
         */
        if(empty($instance)) {
                $instance = TagsAllInOneGenerator::getDefaults();
        }   
?>
    <p>
        <label for="<?php echo $this->get_field_id("title"); ?>"><?php _e('Title'); ?>:</label>        
        <input class="widefat" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr(array_key_exists('title', $instance) ? $instance["title"] : ''); ?>" />
    </p>
    <p>
        <label for="<?php echo $this->get_field_id("number"); ?>"><?php _e('Limit', 'tags-all-in-one'); ?>:</label>
        <br />
        <input id="<?php echo $this->get_field_id("number"); ?>" name="<?php echo $this->get_field_name("number"); ?>" type="text" size="5" value="<?php echo esc_attr(array_key_exists('number', $instance) ? $instance["number"] : ''); ?>" />
    </p>
    <p>
        <label for="<?php echo $this->get_field_id("smallest"); ?>"><?php _e('Smallest font size', 'tags-all-in-one'); ?>:</label>
        <br />
        <input id="<?php echo $this->get_field_id("smallest"); ?>" name="<?php echo $this->get_field_name("smallest"); ?>" type="text" size="5" value="<?php echo esc_attr(array_key_exists('smallest', $instance) ? $instance["smallest"] : ''); ?>" />
    </p>     
    <p>
        <label for="<?php echo $this->get_field_id("largest"); ?>"><?php _e('Largest font size', 'tags-all-in-one'); ?>:</label>
        <br />
        <input id="<?php echo $this->get_field_id("largest"); ?>" name="<?php echo $this->get_field_name("largest"); ?>" type="text" size="5" value="<?php echo esc_attr(array_key_exists('largest', $instance) ? $instance["largest"] : ''); ?>" />
    </p>  
    <p>
        <label for="<?php echo $this->get_field_id("unit"); ?>"><?php _e('Unit of font size', 'tags-all-in-one'); ?>:</label>
        <br />
        <select name="<?php echo $this->get_field_name("unit"); ?>" id="<?php echo $this->get_field_id("unit"); ?>" class="select">
          <?php            
                $unit_list = Tags_All_In_One_Utils::getUnits();
                foreach($unit_list as $key => $list) {
                        echo "<option value=\"". $key ."\" ". (esc_attr($instance["unit"]) == $key ? 'selected="selected"' : null) .">". $list ."</option>";
                }
          ?>
        </select>
    </p>
    <p>
        <label for="<?php echo $this->get_field_id("separator"); ?>"><?php _e('Separator', 'tags-all-in-one'); ?>:</label>
        <br />
        <input id="<?php echo $this->get_field_id("separator"); ?>" name="<?php echo $this->get_field_name("separator"); ?>" type="text" size="5" value="<?php echo esc_attr(array_key_exists('separator', $instance) ? $instance["separator"] : ''); ?>" />
    </p>    
    <p>
        <label for="<?php echo $this->get_field_id("orderby"); ?>"><?php _e('Order by', 'tags-all-in-one'); ?>:</label>
        <br />
        <select name="<?php echo $this->get_field_name("orderby"); ?>" id="<?php echo $this->get_field_id("orderby"); ?>" class="select">
          <?php            
                $orderby_list = Tags_All_In_One_Utils::getOrdersBy();
                foreach($orderby_list as $key => $list) {
                        echo "<option value=\"". $key ."\" ". (esc_attr($instance["orderby"]) == $key ? 'selected="selected"' : null) .">". $list ."</option>";
                }
          ?>
        </select>
    </p>       
    <p>
        <label for="<?php echo $this->get_field_id("order"); ?>"><?php _e('Order', 'tags-all-in-one'); ?>:</label>
        <br />
        <select name="<?php echo $this->get_field_name("order"); ?>" id="<?php echo $this->get_field_id("order"); ?>" class="select">
          <?php            
                $order_list = Tags_All_In_One_Utils::getOrders();
                foreach($order_list as $key => $list) {
                        echo "<option value=\"". $key ."\" ". (esc_attr($instance["order"]) == $key ? 'selected="selected"' : null) .">". $list ."</option>";
                }
          ?>
        </select>
    </p>    
    
    <p>
        <label for="<?php echo $this->get_field_id("taxonomy"); ?>"><?php _e('Tags from', 'tags-all-in-one'); ?>:</label>
        <br />
        <ul>
            <?php   
                $taxanomies_list = Tags_All_In_One_Utils::getTaxonomies();
                foreach($taxanomies_list as $key => $type) {
                        echo "<li>";

                        if(array_key_exists('taxonomy', $instance)) {
                            $checked = ( (is_array($instance['taxonomy']) && array_key_exists($key,$instance['taxonomy'])) || ($key === $instance['taxonomy']) ) ? 'checked="checked"' : null;
                        } else {
                            $checked = null;
                        }

                        echo "<input type=\"checkbox\" value=\"". $key ."\" id=\"tag-". $key ."\" ". $checked ." name=\"". $this->get_field_name('taxonomy') ."[". $key ."]\" />";
                        echo "<label for=\"tag-". $key ."\">". $type ."</label>";
                        echo "</li>";
                }
            ?>
        </ul        
    </p>  
<?php
    }
}
add_action('widgets_init', create_function('', 'return register_widget("TagsAllInOneWidget");'));
?>