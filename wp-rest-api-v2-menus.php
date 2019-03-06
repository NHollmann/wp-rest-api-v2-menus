<?php 
/*
Plugin Name: WP-REST-API V2 Menus
Version: 0.3.2
Description: Adding menus endpoints on WP REST API v2
Author: Claudio La Barbera
Author URI: https://thebatclaud.io
*/

/**
 * Get all registered menus
 * @return array List of menus with slug and description
 */
function wp_api_v2_menus_get_all_menus () {
    $menus = [];
    foreach (get_registered_nav_menus() as $slug => $description) {
        $obj = new stdClass;
        $obj->slug = $slug;
        $obj->description = $description;
        $menus[] = $obj;
    }

    return $menus;
}

/**
 * Get menu's data from his id
 * @param  array $data WP REST API data variable
 * @return object Menu's data with his items
 */
function wp_api_v2_menus_get_menu_data ( $data ) {
    $menu = new stdClass;
	$menu->items = [];
    if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ $data['id'] ] ) ) {
        $menu = get_term( $locations[ $data['id'] ] );
        $menu_items = wp_get_nav_menu_items($menu->term_id);
        // wordpress does not group child menu items with parent menu items
        $child_items = [];
        // pull all child menu items into separate object
        foreach ($menu_items as $key => $item) {
            $slug = get_post_field( 'post_name', $menu_item->object_id );
            $menu_item->slug = $slug;
            if ($item->menu_item_parent) {
                array_push($child_items, $item);
                unset($menu_items[$key]);
            }
        }

        // push child items into their parent item in the original object
        foreach ($menu_items as $item) {
            foreach ($child_items as $key => $child) {
                if ($child->menu_item_parent == $item->post_name) {
                    if (!$item->child_items) $item->child_items = [];
                    array_push($item->child_items, $child);
                    unset($child_items[$key]);
                }
            }
        }

        $menu->items = $menu_items;
    }

    return $menu;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'menus/v1', '/menus', array(
        'methods' => 'GET',
        'callback' => 'wp_api_v2_menus_get_all_menus',
    ) );

    register_rest_route( 'menus/v1', '/menus/(?P<id>[a-zA-Z0-9_-]+)', array(
        'methods' => 'GET',
        'callback' => 'wp_api_v2_menus_get_menu_data',
    ) );
} );
