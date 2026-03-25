<?php
/**
 * Plugin Name: Time Window Reservation
 * Description: A plugin for reserving time windows.
 * Version: 1.0
 * Author: eraser25
 */

 // Exit if accessed directly.
 if ( ! defined( 'ABSPATH' ) ) {
     exit;
 }

 // Include necessary files
 require_once plugin_dir_path( __FILE__ ) . 'includes/class-reservation.php';
 require_once plugin_dir_path( __FILE__ ) . 'includes/class-availability.php';
 require_once plugin_dir_path( __FILE__ ) . 'includes/class-settings.php';
