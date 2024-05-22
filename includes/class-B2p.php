<?php

class B2p {

	private $source_plugin = null; // eg sb-starting-block
	private $source_build_dir;
	private $files = [];
	private $json; // block.json being processed.
	private $block; // The decoded block.json object.

	function __construct( $source_plugin ) {
		$this->source_plugin = $source_plugin;
		$this->files = [];
		$this->json = null;
	}

	/**
	 *
	 * - List all the build files
	 * - Check for target files
	 * - Copy any build files that are newer than the target files
	 *
	 * @return void
	 */
	function update_build_files() {
		$files=$this->list_build_files();
		$this->process_build_files();
	}

	/**
	 * Lists the build files.
	 *
	 * This lists the files and directories in the build dir.
	 * For each file which is a directory we need to list the files within
	 */
	function list_build_files() {
		$source_build_dir = WP_PLUGIN_DIR . '/' . $this->source_plugin . '/build/';
		echo $source_build_dir;
		$this->files = glob( "$source_build_dir*" );
		print_r( $this->files );
		$this->source_build_dir = $source_build_dir;
	}

	/**
Copying build files from sb-starting-block
C:\apache\htdocs\wordpress/wp-content/plugins/sb-starting-block/buildArray
(
[0] => C:\apache\htdocs\wordpress/wp-content/plugins/sb-starting-block/build/oik-nivo-slider
[1] => C:\apache\htdocs\wordpress/wp-content/plugins/sb-starting-block/build/oik-nivo-slider.asset.php
[2] => C:\apache\htdocs\wordpress/wp-content/plugins/sb-starting-block/build/oik-nivo-slider.js
[3] => C:\apache\htdocs\wordpress/wp-content/plugins/sb-starting-block/build/sb-starting-block.asset.php
[4] => C:\apache\htdocs\wordpress/wp-content/plugins/sb-starting-block/build/sb-starting-block.css
[5] => C:\apache\htdocs\wordpress/wp-content/plugins/sb-starting-block/build/sb-starting-block.js
[6] => C:\apache\htdocs\wordpress/wp-content/plugins/sb-starting-block/build/second-block
[7] => C:\apache\htdocs\wordpress/wp-content/plugins/sb-starting-block/build/second-block.asset.php
[8] => C:\apache\htdocs\wordpress/wp-content/plugins/sb-starting-block/build/second-block.css
[9] => C:\apache\htdocs\wordpress/wp-content/plugins/sb-starting-block/build/second-block.js
[10] => C:\apache\htdocs\wordpress/wp-content/plugins/sb-starting-block/build/starting-block
[11] => C:\apache\htdocs\wordpress/wp-content/plugins/sb-starting-block/build/style-oik-nivo-slider.css
[12] => C:\apache\htdocs\wordpress/wp-content/plugins/sb-starting-block/build/style-sb-starting-block.css
[13] => C:\apache\htdocs\wordpress/wp-content/plugins/sb-starting-block/build/style-second-block.css
)
	 */
	function process_build_files() {
		foreach ( $this->files as $file ) {
			if ( is_dir( $file ) ) {
				$target_plugin = $this->get_target_plugin( $file );
				$target_build_subdir = $this->get_target_build_subdir( $file );
				$target_plugin_build_dir = $this->check_target_plugin_build_subdir( $target_plugin, $target_build_subdir );
				if( $target_plugin_build_dir ) {
					$this->maybe_copy_files( $target_plugin, $target_build_subdir, $target_plugin_build_dir);
				}
			}
		}
	}

	function get_target_plugin( $file ) {
		$target_json = $file . '/block.json';
		$this->json = file_get_contents( $target_json );
		$this->block = json_decode( $this->json );
		//print_r( $json );
		$target_plugin = $this->block->textdomain;
		return $target_plugin;
	}

	function get_target_build_subdir( $file ) {
		$target_build_subdir = basename( $file );
		return $target_build_subdir;
	}

	/**
	 * Checks if the target plugin build subdir exists for the block.
	 *
	 * @param $target_plugin
	 * @param $target_build_subdir
	 *
	 * @return string|null
	 */

	function check_target_plugin_build_subdir( $target_plugin, $target_build_subdir ) {
		echo "$target_plugin, $target_build_subdir", PHP_EOL;
		$target_plugin_build_dir = WP_PLUGIN_DIR . '/' . $target_plugin . '/build/';
		$target_plugin_build_subdir = $target_plugin_build_dir .  $target_build_subdir;
		if ( file_exists( $target_plugin_build_subdir) && is_dir( $target_plugin_build_subdir ) ) {
			return $target_plugin_build_dir;
		}
		return null;
	}

	/**
	 * Maybe copy files
	 *
	 * Copies file from the source plugins build directory to the target plugin's build directory.
	 *
	 * @param $target_plugin
	 * @param $target_build_subdir
	 * @param $target_plugin_build_dir
	 *
	 * @return void
	 */
	function maybe_copy_files( $target_plugin, $target_build_subdir, $target_plugin_build_dir) {
		$sources = [ "$target_build_subdir.asset.php",
					"$target_build_subdir/block.json",
					"$target_build_subdir.css",
					"$target_build_subdir.js",
					"style-$target_build_subdir.css"

			];
		echo "copy $target_plugin $target_build_subdir $target_plugin_build_dir", PHP_EOL;
		foreach ( $sources as $source ) {
			$this->maybe_copy_file( $this->source_build_dir, $source, $target_plugin_build_dir );
		}
	}


	/**
	 * Copy a file if necessary.
	 *
	 * Copy the source file to the target file if newer
	 * returning the timestamp of the most recent file
	 *
	 * We always expect both files to be present, so we should be happy with warning.
	 *
	 */
	function maybe_copy_file( $source_build_dir, $source_file, $target_plugin_build_dir ) {
		$target_file = $source_file;
		$source_time = file_exists( $source_build_dir . $source_file ) ? filemtime( $source_build_dir . $source_file ) : 0;
		$target_time = file_exists( $target_plugin_build_dir . $target_file ) ? filemtime( $target_plugin_build_dir . $target_file ) : 0;
		echo "$source_build_dir $source_file $source_time $target_time", PHP_EOL;
		if ( $source_time && ( $source_time > $target_time ) ) {
			$copied = copy( $source_build_dir . $source_file, $target_plugin_build_dir . $target_file );
			if ( $copied ) {
				p( "File refreshed from source:" );
				p( "$source_build_dir $source_file $source_time $target_time" );
				echo "$target_plugin_build_dir$target_file", PHP_EOL;
			} else {
				gob();
			}
		}
		return( $source_time );
	}




}