module.exports = function( grunt ) {

	var pkg = grunt.file.readJSON( 'package.json' );

	console.log( pkg.title + ' - ' + pkg.version );

	// Files to include/exclude in a release.
	// Stop distributing composer/autoload files with build as they won't commit to SVN for v1.5.1. 6/18/16 PD
	var distFiles = [
		'**',
		'!assets/images/wp/**',
		'!build/**',
		'!node_modules/**',
		'!vendor/autoload.php',
		'!vendor/composer/**',
		'!.editorconfig',
		'!.gitignore',
		'!.jshintrc',
		'!composer.json',
		'!composer.lock',
		'!contributing.md',
		'!readme.md',
		'!gruntfile.js',
		'!package.json',
		'!**/*~'
	];

	// Project configuration
	grunt.initConfig( {

		pkg: pkg,

		// Create comment banner to add to the top of minified .js and .css files.
		banner: '/*! <%= pkg.title %> - <%= pkg.version %>\n' +
		        ' * <%=pkg.homepage %>\n' +
		        ' * Copyright (c) Moonstone Media <%= grunt.template.today("yyyy") %>\n' +
		        ' * Licensed GPLv2+' +
		        ' */\n',

		// Validate i18n text domain slug throughout.
		checktextdomain: {
			options: {
				text_domain: 'stripe',
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src: [
					'classes/**/*.php',
					'includes/**/*.php',
					'views/**/*.php',
					'stripe-checkout.php',
					'stripe-checkout-requirements.php',
					'uninstall.php'
				],
				expand: true
			}
		},

		// Wipe out build folder.
		clean: {
			build: [ 'build' ]
		},

		// Build the plugin zip file and place in build folder.
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './build/wp-simple-pay-lite-for-stripe-<%= pkg.version %>.zip'
				},
				expand: true,
				src: distFiles,
				dest: '/stripe'
			}
		},

		// Distribute build files.
		copy: {
			main: {
				expand: true,
				src: distFiles,
				dest: 'build/stripe'
			}
		},

		// Minify .css files.
		cssmin: {
			files: {
				expand: true,
				cwd: 'assets/css/',
				src: [ '*.css', '!*.min.css', '!vendor/**' ],
				dest: 'assets/css/',
				ext: '.min.css'
			}
		},

		// JavaScript linting with JSHint.
		jshint: {
			options: {
				ignores: [
					'**/*.min.js'
				]
			},
			all: [
				'assets/js/*.js',
				'gruntfile.js'
			]
		},

		// Minify .js files.
		uglify: {
			files: {
				expand: true,
				cwd: 'assets/js/',
				src: [ '*.js', '!*.min.js', '!vendor/**' ],
				dest: 'assets/js/',
				ext: '.min.js'
			}
		},

		// Add comment banner to each minified .js and .css file.
		usebanner: {
			options: {
				position: 'top',
				banner: '<%= banner %>',
				linebreak: true
			},
			js: {
				files: {
					src: [ 'assets/js/*.min.js' ]
				}
			},
			css: {
				files: {
					src: [ 'assets/css/*.min.css' ]
				}
			}
		}

	} );

	require( 'load-grunt-tasks' )( grunt );

	grunt.registerTask( 'css', [ 'copy', 'cssmin', 'usebanner:css' ] );
	grunt.registerTask( 'js', [ 'jshint', 'uglify', 'usebanner:js' ] );
	grunt.registerTask( 'default', [ 'css', 'js' ] );
	grunt.registerTask( 'build', [ 'default', 'checktextdomain', 'clean:build', 'copy:main', 'compress' ] );

	// TODO Add deploy task
	//grunt.registerTask( 'deploy',	['build'] );

	grunt.util.linefeed = '\n';
};
