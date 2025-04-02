<?php
/**
 * WordPress Theme Installation Administration API
 *
 * @package WordPress
 * @subpackage Administration
 */

$themes_allowedtags = array(
	'a'       => array(
		'href'   => array(),
		'title'  => array(),
		'target' => array(),
	),
	'abbr'    => array( 'title' => array() ),
	'acronym' => array( 'title' => array() ),
	'code'    => array(),
	'pre'     => array(),
	'em'      => array(),
	'strong'  => array(),
	'div'     => array(),
	'p'       => array(),
	'ul'      => array(),
	'ol'      => array(),
	'li'      => array(),
	'h1'      => array(),
	'h2'      => array(),
	'h3'      => array(),
	'h4'      => array(),
	'h5'      => array(),
	'h6'      => array(),
	'img'     => array(
		'src'   => array(),
		'class' => array(),
		'alt'   => array(),
	),
);

$theme_field_defaults = array(
	'description'  => true,
	'sections'     => false,
	'tested'       => true,
	'requires'     => true,
	'rating'       => true,
	'downloaded'   => true,
	'downloadlink' => true,
	'last_updated' => true,
	'homepage'     => true,
	'tags'         => true,
	'num_ratings'  => true,
);

/**
 * Retrieves the list of WordPress theme features (aka theme tags).
 *
 * @since 2.8.0
 *
 * @deprecated 3.1.0 Use get_theme_feature_list() instead.
 *
 * @return array
 */
function install_themes_feature_list() {
	_deprecated_function( __FUNCTION__, '3.1.0', 'get_theme_feature_list()' );

	$cache = get_transient( 'wporg_theme_feature_list' );
	if ( ! $cache ) {
		set_transient( 'wporg_theme_feature_list', array(), 3 * HOUR_IN_SECONDS );
	}

	if ( $cache ) {
		return $cache;
	}

	$feature_list = themes_api( 'feature_list', array() );
	if ( is_wp_error( $feature_list ) ) {
		return array();
	}

	set_transient( 'wporg_theme_feature_list', $feature_list, 3 * HOUR_IN_SECONDS );

	return $feature_list;
}

/**
 * Displays search form for searching themes.
 *
 * @since 2.8.0
 *
 * @param bool $type_selector
 */
function install_theme_search_form( $type_selector = true ) {
	$type = isset( $_REQUEST['type'] ) ? wp_unslash( $_REQUEST['type'] ) : 'term';
	$term = isset( $_REQUEST['s'] ) ? wp_unslash( $_REQUEST['s'] ) : '';
	if ( ! $type_selector ) {
		echo '<p class="install-help">' . __( 'Search for themes by keyword.' ) . '</p>';
	}
	?>
<form id="search-themes" method="get">
	<input type="hidden" name="tab" value="search" />
	<?php if ( $type_selector ) : ?>
	<label class="screen-reader-text" for="typeselector">
		<?php
		/* translators: Hidden accessibility text. */
		_e( 'Type of search' );
		?>
	</label>
	<select	name="type" id="typeselector">
	<option value="term" <?php selected( 'term', $type ); ?>><?php _e( 'Keyword' ); ?></option>
	<option value="author" <?php selected( 'author', $type ); ?>><?php _e( 'Author' ); ?></option>
	<option value="tag" <?php selected( 'tag', $type ); ?>><?php _ex( 'Tag', 'Theme Installer' ); ?></option>
	</select>
	<label class="screen-reader-text" for="s">
		<?php
		switch ( $type ) {
			case 'term':
				/* translators: Hidden accessibility text. */
				_e( 'Search by keyword' );
				break;
			case 'author':
				/* translators: Hidden accessibility text. */
				_e( 'Search by author' );
				break;
			case 'tag':
				/* translators: Hidden accessibility text. */
				_e( 'Search by tag' );
				break;
		}
		?>
	</label>
	<?php else : ?>
	<label class="screen-reader-text" for="s">
		<?php
		/* translators: Hidden accessibility text. */
		_e( 'Search by keyword' );
		?>
	</label>
	<?php endif; ?>
	<input type="search" name="s" id="s" size="30" value="<?php echo esc_attr( $term ); ?>" autofocus="autofocus" />
	<?php submit_button( __( 'Search' ), '', 'search', false ); ?>
</form>
	<?php
}

/**
 * Displays tags filter for themes.
 *
 * @since 2.8.0
 */
function install_themes_dashboard() {
	install_theme_search_form( false );
	?>
<h4><?php _e( 'Feature Filter' ); ?></h4>
<p class="install-help"><?php _e( 'Find a theme based on specific features.' ); ?></p>

<form method="get">
	<input type="hidden" name="tab" value="search" />
	<?php
	$feature_list = get_theme_feature_list();
	echo '<div class="feature-filter">';

	foreach ( (array) $feature_list as $feature_name => $features ) {
		$feature_name = esc_html( $feature_name );
		echo '<div class="feature-name">' . $feature_name . '</div>';

		echo '<ol class="feature-group">';
		foreach ( $features as $feature => $feature_name ) {
			$feature_name = esc_html( $feature_name );
			$feature      = esc_attr( $feature );
			?>

<li>
	<input type="checkbox" name="features[]" id="feature-id-<?php echo $feature; ?>" value="<?php echo $feature; ?>" />
	<label for="feature-id-<?php echo $feature; ?>"><?php echo $feature_name; ?></label>
</li>

<?php	} ?>
</ol>
<br class="clear" />
		<?php
	}
	?>

</div>
<br class="clear" />
	<?php submit_button( __( 'Find Themes' ), '', 'search' ); ?>
</form>
	<?php
}

/**
 * Displays a form to upload themes from zip files.
 *
 * @since 2.8.0
 */
function install_themes_upload() {
	?>
<p class="install-help"><?php _e( 'If you have a theme in a .zip format, you may install or update it by uploading it here.' ); ?></p>
<form method="post" enctype="multipart/form-data" class="wp-upload-form" action="<?php echo esc_url( self_admin_url( 'update.php?action=upload-theme' ) ); ?>">
	<?php wp_nonce_field( 'theme-upload' ); ?>
	<label class="screen-reader-text" for="themezip">
		<?php
		/* translators: Hidden accessibility text. */
		_e( 'Theme zip file' );
		?>
	</label>
	<input type="file" id="themezip" name="themezip" accept=".zip" />
	<?php submit_button( _x( 'Install Now', 'theme' ), '', 'install-theme-submit', false ); ?>
</form>
	<?php
}

/**
 * Prints a theme on the Install Themes pages.
 *
 * @deprecated 3.4.0
 *
 * @global WP_Theme_Install_List_Table $wp_list_table
 *
 * @param object $theme
 */
function display_theme( $theme ) {
	_deprecated_function( __FUNCTION__, '3.4.0' );
	global $wp_list_table;
	if ( ! isset( $wp_list_table ) ) {
		$wp_list_table = _get_list_table( 'WP_Theme_Install_List_Table' );
	}
	$wp_list_table->prepare_items();
	$wp_list_table->single_row( $theme );
}

/**
 * Displays theme content based on theme list.
 *
 * @since 2.8.0
 *
 * @global WP_Theme_Install_List_Table $wp_list_table
 */
function display_themes() {
	global $wp_list_table;

	if ( ! isset( $wp_list_table ) ) {
		$wp_list_table = _get_list_table( 'WP_Theme_Install_List_Table' );
	}
	$wp_list_table->prepare_items();
	$wp_list_table->display();
}


/**
 * Determines the status we can perform on a plugin.
 *
 * @since 3.0.0
 *
 * @param array|object $api  Data about the plugin retrieved from the API.
 * @param bool         $loop Optional. Disable further loops. Default false.
 * @return array {
 *     Plugin installation status data.
 *
 *     @type string $status  Status of a plugin. Could be one of 'install', 'update_available', 'latest_installed' or 'newer_installed'.
 *     @type string $url     Plugin installation URL.
 *     @type string $version The most recent version of the plugin.
 *     @type string $file    Plugin filename relative to the plugins directory.
 * }
 */
function install_theme_install_status( $api, $loop = false ) {
	// This function is called recursively, $loop prevents further loops.
	if ( is_array( $api ) ) {
		$api = (object) $api;
	}

	// Default to a "new" plugin.
	$status      = 'install';
	$url         = false;
	$update_file = false;
	$version     = '';

	/*
	 * Check to see if this plugin is known to be installed,
	 * and has an update awaiting it.
	 */
	$update_plugins = get_site_transient( 'update_plugins' );
	if ( isset( $update_plugins->response ) ) {
		foreach ( (array) $update_plugins->response as $file => $plugin ) {
			if ( $plugin->slug === $api->slug ) {
				$status      = 'update_available';
				$update_file = $file;
				$version     = $plugin->new_version;
				if ( current_user_can( 'update_plugins' ) ) {
					$url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . $update_file ), 'upgrade-plugin_' . $update_file );
				}
				break;
			}
		}
	}

	if ( 'install' === $status ) {
		if ( is_dir( WP_PLUGIN_DIR . '/' . $api->slug ) ) {
			$installed_plugin = get_plugins( '/' . $api->slug );
			if ( empty( $installed_plugin ) ) {
				if ( current_user_can( 'install_plugins' ) ) {
					$url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $api->slug ), 'install-plugin_' . $api->slug );
				}
			} else {
				$key = array_keys( $installed_plugin );
				/*
				 * Use the first plugin regardless of the name.
				 * Could have issues for multiple plugins in one directory if they share different version numbers.
				 */
				$key = reset( $key );

				$update_file = $api->slug . '/' . $key;
				if ( version_compare( $api->version, $installed_plugin[ $key ]['Version'], '=' ) ) {
					$status = 'latest_installed';
				} elseif ( version_compare( $api->version, $installed_plugin[ $key ]['Version'], '<' ) ) {
					$status  = 'newer_installed';
					$version = $installed_plugin[ $key ]['Version'];
				} else {
					// If the above update check failed, then that probably means that the update checker has out-of-date information, force a refresh.
					if ( ! $loop ) {
						delete_site_transient( 'update_plugins' );
						wp_update_plugins();
						return install_plugin_install_status( $api, true );
					}
				}
			}
		} else {
			// "install" & no directory with that slug.
			if ( current_user_can( 'install_plugins' ) ) {
				$url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $api->slug ), 'install-plugin_' . $api->slug );
			}
		}
	}
	if ( isset( $_GET['from'] ) ) {
		$url .= '&amp;from=' . urlencode( wp_unslash( $_GET['from'] ) );
	}

	$file = $update_file;
	return compact( 'status', 'url', 'version', 'file' );
}

/**
 * Displays theme information in dialog box form.
 *
 * @since 2.8.0
 *
 * @global WP_Theme_Install_List_Table $wp_list_table
 */
function install_theme_information() {
	global $wp_list_table, $tab;

	$api = themes_api(
		'theme_information',
		array(
			'slug' => wp_unslash( $_REQUEST['theme'] )
		)
	);

	if ( is_wp_error( $api ) ) {
		wp_die( $api );
	}

	$plugins_allowedtags = array(
		'a'          => array(
			'href'   => array(),
			'title'  => array(),
			'target' => array(),
		),
		'abbr'       => array( 'title' => array() ),
		'acronym'    => array( 'title' => array() ),
		'code'       => array(),
		'pre'        => array(),
		'em'         => array(),
		'strong'     => array(),
		'div'        => array( 'class' => array() ),
		'span'       => array( 'class' => array() ),
		'p'          => array(),
		'br'         => array(),
		'ul'         => array(),
		'ol'         => array(),
		'li'         => array(),
		'h1'         => array(),
		'h2'         => array(),
		'h3'         => array(),
		'h4'         => array(),
		'h5'         => array(),
		'h6'         => array(),
		'img'        => array(
			'src'   => array(),
			'class' => array(),
			'alt'   => array(),
		),
		'blockquote' => array( 'cite' => true ),
	);

	$plugins_section_titles = array(
		'description'  => _x( 'Description', 'Plugin installer section title' ),
		'installation' => _x( 'Installation', 'Plugin installer section title' ),
		'faq'          => _x( 'FAQ', 'Plugin installer section title' ),
		'screenshots'  => _x( 'Screenshots', 'Plugin installer section title' ),
		'changelog'    => _x( 'Changelog', 'Plugin installer section title' ),
		'reviews'      => _x( 'Reviews', 'Plugin installer section title' ),
		'other_notes'  => _x( 'Other Notes', 'Plugin installer section title' ),
	);

	// Sanitize HTML.
	foreach ( (array) $api->sections as $section_name => $content ) {
		$api->sections[ $section_name ] = wp_kses( $content, $plugins_allowedtags );
	}

	foreach ( array( 'version', 'requires', 'tested', 'homepage', 'downloaded', 'slug' ) as $key ) {
		if ( isset( $api->$key ) ) {
			$api->$key = wp_kses( $api->$key, $plugins_allowedtags );
		}
	}

	// Default to the Description tab, Do not translate, API returns English.
	$section = isset( $_REQUEST['section'] ) ? wp_unslash( $_REQUEST['section'] ) : 'description';
	if ( empty( $section ) || ! isset( $api->sections[ $section ] ) ) {
		$section_titles = array_keys( (array) $api->sections );
		$section        = reset( $section_titles );
	}

	$_tab = 'plugin-information'; // esc_attr( $tab );

	iframe_header( __( 'Theme Installation' ) );

	echo '<div id="plugin-information-scrollable">';
	echo "<div id='{$_tab}-title'><div class='vignette'></div><h2>{$api->name}</h2></div>";
	echo "<div id='{$_tab}-tabs'>\n";

	foreach ( (array) $api->sections as $section_name => $content ) {
		if ( 'reviews' === $section_name && ( empty( $api->ratings ) || 0 === array_sum( (array) $api->ratings ) ) ) {
			continue;
		}

		if ( isset( $plugins_section_titles[ $section_name ] ) ) {
			$title = $plugins_section_titles[ $section_name ];
		} else {
			$title = ucwords( str_replace( '_', ' ', $section_name ) );
		}

		$class       = ( $section_name === $section ) ? ' class="current"' : '';
		$href        = add_query_arg(
			array(
				'tab'     => $_tab,
				'section' => $section_name,
			)
		);
		$href        = esc_url( $href );
		$san_section = esc_attr( $section_name );
		echo "\t<a name='$san_section' href='$href' $class>$title</a>\n";
	}

	echo "</div>\n";

	$author_link = sprintf(
		'<a href="%s" target="_blank">%s</a>',
		esc_url( $api->author['author_url'] ),
		esc_html( $api->author['author'] )
	);

	?>
<div id="<?php echo $_tab; ?>-content">
	<div class="fyi">
		<ul>
			<?php if ( ! empty( $api->version ) ) { ?>
				<li><strong><?php _e( 'Version:' ); ?></strong> <?php echo $api->version; ?></li>
			<?php } if ( ! empty( $api->author ) ) { ?>
				<li><strong><?php _e( 'Author:' ); ?></strong> <?php echo $author_link; ?></li>
			<?php } if ( ! empty( $api->last_updated ) ) { ?>
				<li><strong><?php _e( 'Last Updated:' ); ?></strong>
					<?php
					/* translators: %s: Human-readable time difference. */
					printf( __( '%s ago' ), human_time_diff( strtotime( $api->last_updated ) ) );
					?>
				</li>
			<?php } if ( ! empty( $api->requires ) ) { ?>
				<li>
					<strong><?php _e( 'Requires WordPress Version:' ); ?></strong>
					<?php
					/* translators: %s: Version number. */
					printf( __( '%s or higher' ), $api->requires );
					?>
				</li>
			<?php } if ( ! empty( $api->tested ) ) { ?>
				<li><strong><?php _e( 'Compatible up to:' ); ?></strong> <?php echo $api->tested; ?></li>
			<?php } if ( ! empty( $api->requires_php ) ) { ?>
				<li>
					<strong><?php _e( 'Requires PHP Version:' ); ?></strong>
					<?php
					/* translators: %s: Version number. */
					printf( __( '%s or higher' ), $api->requires_php );
					?>
				</li>
			<?php } if ( isset( $api->active_installs ) ) { ?>
				<li><strong><?php _e( 'Active Installations:' ); ?></strong>
				<?php
				if ( $api->active_installs >= 1000000 ) {
					$active_installs_millions = floor( $api->active_installs / 1000000 );
					printf(
						/* translators: %s: Number of millions. */
						_nx( '%s+ Million', '%s+ Million', $active_installs_millions, 'Active plugin installations' ),
						number_format_i18n( $active_installs_millions )
					);
				} elseif ( $api->active_installs < 10 ) {
					_ex( 'Less Than 10', 'Active plugin installations' );
				} else {
					echo number_format_i18n( $api->active_installs ) . '+';
				}
				?>
				</li>
			<?php } if ( ! empty( $api->slug ) && empty( $api->external ) ) { ?>
				<li><a target="_blank" href="<?php echo esc_url( __( 'https://wordpress.org/themes/' ) . $api->slug ); ?>/"><?php _e( 'WordPress.org Theme Page &#187;' ); ?></a></li>
			<?php } if ( ! empty( $api->homepage ) ) { ?>
				<li><a target="_blank" href="<?php echo esc_url( $api->homepage ); ?>"><?php _e( 'Theme Homepage &#187;' ); ?></a></li>
			<?php } if ( ! empty( $api->donate_link ) && empty( $api->contributors ) ) { ?>
				<li><a target="_blank" href="<?php echo esc_url( $api->donate_link ); ?>"><?php _e( 'Donate to this theme &#187;' ); ?></a></li>
			<?php } ?>
		</ul>
		<?php if ( ! empty( $api->rating ) ) { ?>
			<h3><?php _e( 'Average Rating' ); ?></h3>
			<?php
			wp_star_rating(
				array(
					'rating' => $api->rating,
					'type'   => 'percent',
					'number' => $api->num_ratings,
				)
			);
			?>
			<p aria-hidden="true" class="fyi-description">
				<?php
				printf(
					/* translators: %s: Number of ratings. */
					_n( '(based on %s rating)', '(based on %s ratings)', $api->num_ratings ),
					number_format_i18n( $api->num_ratings )
				);
				?>
			</p>
			<?php
		}

		if ( ! empty( $api->ratings ) && array_sum( (array) $api->ratings ) > 0 ) {
			?>
			<h3><?php _e( 'Reviews' ); ?></h3>
			<p class="fyi-description"><?php _e( 'Read all reviews on WordPress.org or write your own!' ); ?></p>
			<?php
			foreach ( $api->ratings as $key => $ratecount ) {
				// Avoid div-by-zero.
				$_rating    = $api->num_ratings ? ( $ratecount / $api->num_ratings ) : 0;
				$aria_label = esc_attr(
					sprintf(
						/* translators: 1: Number of stars (used to determine singular/plural), 2: Number of reviews. */
						_n(
							'Reviews with %1$d star: %2$s. Opens in a new tab.',
							'Reviews with %1$d stars: %2$s. Opens in a new tab.',
							$key
						),
						$key,
						number_format_i18n( $ratecount )
					)
				);
				?>
				<div class="counter-container">
						<span class="counter-label">
							<?php
							printf(
								'<a href="%s" target="_blank" aria-label="%s">%s</a>',
								"https://wordpress.org/support/plugin/{$api->slug}/reviews/?filter={$key}",
								$aria_label,
								/* translators: %s: Number of stars. */
								sprintf( _n( '%d star', '%d stars', $key ), $key )
							);
							?>
						</span>
						<span class="counter-back">
							<span class="counter-bar" style="width: <?php echo 92 * $_rating; ?>px;"></span>
						</span>
					<span class="counter-count" aria-hidden="true"><?php echo number_format_i18n( $ratecount ); ?></span>
				</div>
				<?php
			}
		}
		if ( ! empty( $api->contributors ) ) {
			?>
			<h3><?php _e( 'Contributors' ); ?></h3>
			<ul class="contributors">
				<?php
				foreach ( (array) $api->contributors as $contrib_username => $contrib_details ) {
					$contrib_name = $contrib_details['display_name'];
					if ( ! $contrib_name ) {
						$contrib_name = $contrib_username;
					}
					$contrib_name = esc_html( $contrib_name );

					$contrib_profile = esc_url( $contrib_details['profile'] );
					$contrib_avatar  = esc_url( add_query_arg( 's', '36', $contrib_details['avatar'] ) );

					echo "<li><a href='{$contrib_profile}' target='_blank'><img src='{$contrib_avatar}' width='18' height='18' alt='' />{$contrib_name}</a></li>";
				}
				?>
			</ul>
					<?php if ( ! empty( $api->donate_link ) ) { ?>
				<a target="_blank" href="<?php echo esc_url( $api->donate_link ); ?>"><?php _e( 'Donate to this plugin &#187;' ); ?></a>
			<?php } ?>
				<?php } ?>
	</div>
	<div id="section-holder">
	<?php
	$requires_php = isset( $api->requires_php ) ? $api->requires_php : null;
	$requires_wp  = isset( $api->requires ) ? $api->requires : null;

	$compatible_php = is_php_version_compatible( $requires_php );
	$compatible_wp  = is_wp_version_compatible( $requires_wp );
	$tested_wp      = ( empty( $api->tested ) || version_compare( get_bloginfo( 'version' ), $api->tested, '<=' ) );

	if ( ! $compatible_php ) {
		$compatible_php_notice_message  = '<p>';
		$compatible_php_notice_message .= __( '<strong>Error:</strong> This plugin <strong>requires a newer version of PHP</strong>.' );

		if ( current_user_can( 'update_php' ) ) {
			$compatible_php_notice_message .= sprintf(
				/* translators: %s: URL to Update PHP page. */
				' ' . __( '<a href="%s" target="_blank">Click here to learn more about updating PHP</a>.' ),
				esc_url( wp_get_update_php_url() )
			) . wp_update_php_annotation( '</p><p><em>', '</em>', false );
		} else {
			$compatible_php_notice_message .= '</p>';
		}

		wp_admin_notice(
			$compatible_php_notice_message,
			array(
				'type'               => 'error',
				'additional_classes' => array( 'notice-alt' ),
				'paragraph_wrap'     => false,
			)
		);
	}

	if ( ! $tested_wp ) {
		wp_admin_notice(
			__( '<strong>Warning:</strong> This plugin <strong>has not been tested</strong> with your current version of WordPress.' ),
			array(
				'type'               => 'warning',
				'additional_classes' => array( 'notice-alt' ),
			)
		);
	} elseif ( ! $compatible_wp ) {
		$compatible_wp_notice_message = __( '<strong>Error:</strong> This plugin <strong>requires a newer version of WordPress</strong>.' );
		if ( current_user_can( 'update_core' ) ) {
			$compatible_wp_notice_message .= sprintf(
				/* translators: %s: URL to WordPress Updates screen. */
				' ' . __( '<a href="%s" target="_parent">Click here to update WordPress</a>.' ),
				esc_url( self_admin_url( 'update-core.php' ) )
			);
		}

		wp_admin_notice(
			$compatible_wp_notice_message,
			array(
				'type'               => 'error',
				'additional_classes' => array( 'notice-alt' ),
			)
		);
	}

	foreach ( (array) $api->sections as $section_name => $content ) {
		$content = links_add_base_url( $content, 'https://wordpress.org/themes/' . $api->slug . '/' );
		$content = links_add_target( $content, '_blank' );

		$san_section = esc_attr( $section_name );

		$display = ( $section_name === $section ) ? 'block' : 'none';

		echo "\t<div id='section-{$san_section}' class='section' style='display: {$display};'>\n";
		echo $content;
		echo "\t</div>\n";
	}

	echo "</div>\n";
	echo "</div>\n";
	echo "</div>\n"; // #plugin-information-scrollable
	echo "<div id='$_tab-footer'>\n";
	if ( ! empty( $api->download_link ) && ( current_user_can( 'install_themes' ) || current_user_can( 'update_themes' ) ) ) {
		$button = wp_get_theme_action_button( $api->name, $api, $compatible_php, $compatible_wp );
		$button = str_replace( 'class="', 'class="right ', $button );

		if ( ! str_contains( $button, _x( 'Activate', 'plugin' ) ) ) {
			$button = str_replace( 'class="', 'id="plugin_install_from_iframe" class="', $button );
		}

		echo wp_kses_post( $button );
	}
	echo "</div>\n";

	wp_print_request_filesystem_credentials_modal();
	wp_print_admin_notice_templates();

	iframe_footer();
	exit;
}

/**
 * Gets the markup for the plugin install action button.
 *
 * @since 6.5.0
 *
 * @param string       $name           Plugin name.
 * @param array|object $data           {
 *     An array or object of plugin data. Can be retrieved from the API.
 *
 *     @type string   $slug             The plugin slug.
 *     @type string[] $requires_plugins An array of plugin dependency slugs.
 *     @type string   $version          The plugin's version string. Used when getting the install status.
 * }
 * @param bool         $compatible_php   The result of a PHP compatibility check.
 * @param bool         $compatible_wp    The result of a WP compatibility check.
 * @return string The markup for the dependency row button. An empty string if the user does not have capabilities.
 */
function wp_get_theme_action_button( $name, $data, $compatible_php, $compatible_wp ) {
	$button = '';
	$data   = (object) $data;
	//$status = install_theme_install_status( $data );
	$status = [
		'status' => 'update_available',
		'url'    => 'https://example.com',
		'file'   => 'file.zip',
	];

	return $button = sprintf(
		'<a class="update-now button aria-button-if-js" data-plugin="%s" data-slug="%s" href="%s" aria-label="%s" data-name="%s" role="button">%s</a>',
		esc_attr( $status['file'] ),
		esc_attr( $data->slug ),
		esc_url( $status['url'] ),
		/* translators: %s: Plugin name and version. */
		esc_attr( sprintf( _x( 'Update %s now', 'plugin' ), $name ) ),
		esc_attr( $name ),
		_x( 'Update Now', 'plugin' )
	);

	if ( current_user_can( 'install_themes' ) || current_user_can( 'update_themes' ) ) {
		switch ( $status['status'] ) {
			case 'install':
				if ( $status['url'] ) {
					if ( $compatible_php && $compatible_wp && ! empty( $data->download_link ) ) {
						$button = sprintf(
							'<a class="install-now button" data-slug="%s" href="%s" aria-label="%s" data-name="%s" role="button">%s</a>',
							esc_attr( $data->slug ),
							esc_url( $status['url'] ),
							/* translators: %s: Plugin name and version. */
							esc_attr( sprintf( _x( 'Install %s now', 'plugin' ), $name ) ),
							esc_attr( $name ),
							_x( 'Install Now', 'plugin' )
						);
					} else {
						$button = sprintf(
							'<button type="button" class="install-now button button-disabled" disabled="disabled">%s</button>',
							_x( 'Install Now', 'plugin' )
						);
					}
				}
				break;

			case 'update_available':
				if ( $status['url'] ) {
					if ( $compatible_php && $compatible_wp ) {
						$button = sprintf(
							'<a class="update-now button aria-button-if-js" data-plugin="%s" data-slug="%s" href="%s" aria-label="%s" data-name="%s" role="button">%s</a>',
							esc_attr( $status['file'] ),
							esc_attr( $data->slug ),
							esc_url( $status['url'] ),
							/* translators: %s: Plugin name and version. */
							esc_attr( sprintf( _x( 'Update %s now', 'plugin' ), $name ) ),
							esc_attr( $name ),
							_x( 'Update Now', 'plugin' )
						);
					} else {
						$button = sprintf(
							'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
							_x( 'Update Now', 'plugin' )
						);
					}
				}
				break;

			case 'latest_installed':
			case 'newer_installed':
				if ( is_plugin_active( $status['file'] ) ) {
					$button = sprintf(
						'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
						_x( 'Active', 'plugin' )
					);
				} elseif ( current_user_can( 'activate_plugin', $status['file'] ) ) {
					if ( $compatible_php && $compatible_wp ) {
						$button_text = _x( 'Activate', 'plugin' );
						/* translators: %s: Plugin name. */
						$button_label = _x( 'Activate %s', 'plugin' );
						$activate_url = add_query_arg(
							array(
								'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $status['file'] ),
								'action'   => 'activate',
								'plugin'   => $status['file'],
							),
							network_admin_url( 'plugins.php' )
						);

						if ( is_network_admin() ) {
							$button_text = _x( 'Network Activate', 'plugin' );
							/* translators: %s: Plugin name. */
							$button_label = _x( 'Network Activate %s', 'plugin' );
							$activate_url = add_query_arg( array( 'networkwide' => 1 ), $activate_url );
						}

						$button = sprintf(
							'<a href="%1$s" data-name="%2$s" data-slug="%3$s" data-plugin="%4$s" class="button button-primary activate-now" aria-label="%5$s" role="button">%6$s</a>',
							esc_url( $activate_url ),
							esc_attr( $name ),
							esc_attr( $data->slug ),
							esc_attr( $status['file'] ),
							esc_attr( sprintf( $button_label, $name ) ),
							$button_text
						);
					} else {
						$button = sprintf(
							'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
							is_network_admin() ? _x( 'Network Activate', 'plugin' ) : _x( 'Activate', 'plugin' )
						);
					}
				} else {
					$button = sprintf(
						'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
						_x( 'Installed', 'plugin' )
					);
				}
				break;
		}
	}

	return $button;
}
