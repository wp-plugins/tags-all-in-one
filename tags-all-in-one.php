<?php
/*
Plugin Name: Tags All in One
Plugin URI: http://www.teastudio.pl/produkt/tags-all-in-one/
Description: Display tags for selected post types
Version: 1.0.0
Author: Marcin Gierada
Author URI: http://www.teastudio.pl/
Author Email: info@teastudio.pl
License: MIT License
License URI: http://opensource.org/licenses/MIT

This program is free software; you can redistribute it and/or modify
it under the terms of the MIT License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

global $wpdb;

/*
 * plugin
 */
add_action('init', 'tags_all_in_one_action_init');
function tags_all_in_one_action_init()
{
    load_plugin_textdomain('tags-all-in-one', false, dirname(plugin_basename( __FILE__ )) .  '/i18n/languages/');
}


/*
 * widget
 */
class TagsAllInOneWidget extends WP_Widget 
{

    function tagsAllInOneWidget() {
        $widget_ops = array('classname' => 'widget_tags_all_in_one','description' => __('Display tags for checked post types', 'tags-all-in-one'));
        $this->WP_Widget('widget_tags_all_in_one', __('Tags All in One', 'tags-all-in-one'), $widget_ops);
    }

    function widget( $args, $instance ) {
        global $wpdb, $table_prefix;

        extract( $args );

        $title = apply_filters('widget_title', $instance['title']);

        echo $before_widget;

        if ( $title )
            echo $before_title . $title . $after_title;

        echo $this->generate($instance);
        echo $after_widget;
     }

    function update ($new_instance, $old_instance) {
        return $new_instance;        
    }   
       
    function generate($instance)
    {
        $expected = array('smallest', 'largest', 'unit', 'number', 'order', 'taxonomy');
        $args = array();
        
        foreach($instance as $k => $v)
        {
            /*
             * check if this value can be used in wp_tag_cloud
             */
            if(in_array($k, $expected) && $v != "")
            {
                $args[$k] = $v;
            }            
        }

        echo "<div class=\"tagcloud\">";
        wp_tag_cloud($args);
        echo "</div>";
    }
    
/**
 * The configuration form.
 */
function form($instance) { 
    $taxanomies = get_taxonomies(array(
                                        'show_tagcloud' => true
                                        ), 'objects');
?>
    <p>
        <label for="<?php echo $this->get_field_id("title"); ?>"><?php _e('Title'); ?>:</label>        
        <input class="widefat" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
    </p>
    <p>
        <label for="<?php echo $this->get_field_id("number"); ?>"><?php _e('Limit', 'tags-all-in-one'); ?>:</label>
        <br />
        <input id="<?php echo $this->get_field_id("number"); ?>" name="<?php echo $this->get_field_name("number"); ?>" type="text" size="5" value="<?php echo esc_attr($instance["number"]); ?>" />
    </p>
    <p>
        <label for="<?php echo $this->get_field_id("largest"); ?>"><?php _e('Largest font size', 'tags-all-in-one'); ?>:</label>
        <br />
        <input id="<?php echo $this->get_field_id("largest"); ?>" name="<?php echo $this->get_field_name("largest"); ?>" type="text" size="5" value="<?php echo esc_attr($instance["largest"]); ?>" />
    </p> 
    <p>
        <label for="<?php echo $this->get_field_id("smallest"); ?>"><?php _e('Smallest font size', 'tags-all-in-one'); ?>:</label>
        <br />
        <input id="<?php echo $this->get_field_id("smallest"); ?>" name="<?php echo $this->get_field_name("smallest"); ?>" type="text" size="5" value="<?php echo esc_attr($instance["smallest"]); ?>" />
    </p>  
    <p>
        <label for="<?php echo $this->get_field_id("unit"); ?>"><?php _e('Unit of font size', 'tags-all-in-one'); ?>:</label>
        <br />
        <select name="<?php echo $this->get_field_name("unit"); ?>" id="<?php echo $this->get_field_id("unit"); ?>" class="select">
          <?php            
            $unit_list = array('px' => 'px',
                               'pt' => 'pt'
                              );
            foreach($unit_list as $key => $list) {
              echo "<option value=\"".$key."\" ". (esc_attr($instance["unit"]) == $key ? 'selected="selected"' : null) .">".$list."</option>";
            }
          ?>
        </select>
    </p>
    <p>
        <label for="<?php echo $this->get_field_id("order"); ?>"><?php _e('Ordering', 'tags-all-in-one'); ?>:</label>
        <br />
        <select name="<?php echo $this->get_field_name("order"); ?>" id="<?php echo $this->get_field_id("order"); ?>" class="select">
          <?php            
            $ordering_list = array('asc' => __("Ascending", 'tags-all-in-one'),
                                   'desc' => __("Descending", 'tags-all-in-one')
                                  );
            foreach($ordering_list as $key => $list) {
              echo "<option value=\"".$key."\" ". (esc_attr($instance["order"]) == $key ? 'selected="selected"' : null) .">".$list."</option>";
            }
          ?>
        </select>
    </p>      
    <p>
        <label for="<?php echo $this->get_field_id("taxonomy"); ?>"><?php _e('Taxonomy', 'tags-all-in-one'); ?>:</label>
        <br />
        <ul>
            <?php   
           
            foreach($taxanomies as $key => $type) {
                echo "<li>";
                
                if(array_key_exists('taxonomy', $instance))
                    $checked = array_key_exists($key, $instance["taxonomy"]) ? 'checked="checked"' : null;
                else
                    $checked = null;
                
                echo "<input type=\"checkbox\" value=\"".$key."\" id=\"tag-".$key."\" ".$checked." name=\"".$this->get_field_name('taxonomy')."[".$key."]\" />";
                echo "<label for=\"tag-".$key."\">".$type->label."</label>";
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