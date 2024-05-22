<?php
/**
Plugin Name: oik-b2p
Plugin Requires: oik-batch
Plugin URI: https://www.bobbingwide.com/blog/oik_plugins/oik-b2p
Description: Build to plugin
Version: 0.0.0
Author: bobbingwide
Author URI: https://bobbingwide.com/about-bobbing-wide
Text Domain: oik-b2p
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Copyright 2024 Bobbing Wide (email : herb@bobbingwide.com )

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2,
as published by the Free Software Foundation.

You may NOT assume that you can use any other version of the GPL.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

The license for this software can likely be found here:
http://www.gnu.org/licenses/gpl-2.0.html

 */

oik_b2p_loaded();

/**
 * Copy build files to the target plugin.
 */
function oik_b2p_loaded() {
	$source_plugin=oik_batch_query_value_from_argv( 1, 'sb-starting-block' );
	echo "Copying build files from $source_plugin", PHP_EOL;
	oik_require( "includes/class-b2p.php", 'oik-b2p');
	$b2p = new B2p( $source_plugin );
	$b2p->update_build_files();
}