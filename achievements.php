<?php
/**
 * Welcome to Achievements for WordPress.
 *
 * Plugin structure is based on bbPress and BuddyPress, because they're awesome. Borrowed with love.
 *
 * @author Paul Gibbs <paul@byotos.com>
 * @package Achievements
 * @subpackage Loader
 */

/*
Plugin Name: Achievements
Plugin URI: http://achievementsapp.com/
Description: Achievements gamifies your WordPress site with challenges, badges, and points.
Version: 3.5.1
Requires at least: 3.8
Tested up to: 3.8.20
License: GPLv3
Author: Paul Gibbs
Author URI: http://byotos.com/
Domain Path: ../../languages/plugins/
Text Domain: achievements

"Achievements"
Copyright (C) 2009-14 Paul Gibbs

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License version 3 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses/.
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load from source if no build exists
$dpa_loader = __DIR__ . '/build/achievements.php';
if ( ! file_exists( $dpa_loader ) || defined( 'DPA_LOAD_SOURCE' ) ) {
	$dpa_loader = __DIR__ . '/src/achievements.php';
}

// Load Achievements
include( $dpa_loader );
unset( $dpa_loader );