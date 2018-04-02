module.exports = function( grunt ) {

	var pkg = grunt.file.readJSON( 'package.json' );

	console.log( pkg.title + ' - ' + pkg.version );

	// Set files to include/exclude in a release.
	var distFiles = [
		'**',
		'!bower_components/**',
		'!build/**',
		'!node_modules/**',
		'!vendor/bin/**',
		'!wordpress_org_assets/**',
		'!.editorconfig',
		'!.gitignore',
		'!.jshintrc',
		'!bower.json',
		'!composer.json',
		'!composer.lock',
		'!contributing.md',
		'!gruntfile.js',
		'!package.json',
		'!package-lock.json',
		'!readme.md',
		'!**/*~'
	];

	grunt.initConfig( {

		pkg: pkg,

		// Set folder variables.
		dirs: {
			css: 'assets/css',
			js: 'assets/js'
		},

		// Create comment banner to add to the top of minified .js and .css files.
		banner: '/*! <%= pkg.title %> - <%= pkg.version %>\n' +
		        ' * <%=pkg.homepage %>\n' +
		        ' * Copyright (c) Moonstone Media Group <%= grunt.template.today("yyyy") %>\n' +
		        ' * Licensed GPLv2+' +
		        ' */\n',

		// Validate i18n text domain slug throughout.
		checktextdomain: {
			options: {
				text_domain: 'stripe',
				correct_domain: false,
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
					'includes/**/*.php',
					'!includes/core/admin/wp-list-table.php', // Included from core for our use. See https://codex.wordpress.org/Class_Reference/WP_List_Table
					'stripe-checkout.php',
					'uninstall.php'
				],
				expand: true
			}
		},

		addtextdomain: {
			options: {
				textdomain: 'stripe'    // Project text domain.
			},
			target: {
				files: {
					src: [
						'includes/**/*.php',
						'!includes/core/admin/wp-list-table.php',
						'stripe-checkout.php',
						'uninstall.php'
				    ]
				}
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
					archive: './build/stripe-<%= pkg.version %>.zip'
				},
				expand: true,
				src: distFiles,
				dest: '/stripe'
			}
		},

		// 'css' & 'js' tasks need to copy vendor-minified assets from bower folder to assets folder (moment, parsley, etc).
		// Pikaday is a special case as it does NOT include minified assets and DOES include CSS.
		// 'main' task is for distributing build files.
		copy: {
			css: {
				expand: true,
				cwd: 'bower_components/',
				flatten: true,
				src: [
					'chosen/chosen.css',
				    'chosen/chosen-sprite.png',
					'chosen/chosen-sprite@2x.png'
				],
				dest: '<%= dirs.css %>'
			},
			js: {
				expand: true,
				cwd: 'bower_components/',
				flatten: true,
				src: [
					'!jquery/**',
					'jquery-validation/dist/jquery.validate.js',
					'jquery-validation/dist/jquery.validate.min.js',
				    'chosen/chosen.jquery.js',
				    'accountingjs/accounting.js',
				    'accountingjs/accounting.min.js'
				],
				dest: '<%= dirs.js %>/vendor/'
			},
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
				cwd: '<%= dirs.css %>',
				src: [ '*.css', '!*.min.css', '!vendor/**' ],
				dest: '<%= dirs.css %>',
				ext: '.min.css'
			}
		},

		// Check JavaScript coding standards.
		jscs: {
			all: [
				'<%= dirs.js %>/*.js',
				'!<%= dirs.js %>/*.min.js',
			    '!<%= dirs.js %>/vendor/**'
			]
		},

		// JavaScript linting with JSHint.
		jshint: {
			options: {
				ignores: [
					'**/*.min.js',
					'<%= dirs.js %>/vendor/*'
				]
			},
			all: [
				'<%= dirs.js %>/*.js',
				'gruntfile.js'
			]
		},

		// Compile all .scss files.
		sass: {
			options: {
				precision: 2,
				sourceMap: false
			},
			all: {
				files: [
					{
						expand: true,
						cwd: '<%= dirs.css %>',
						src: [ '*.scss' ],
						dest: '<%= dirs.css %>',
						ext: '.css'
					}
				]
			}
		},

		// Minify .js files.
		uglify: {
			files: {
				expand: true,
				cwd: '<%= dirs.js %>',
				src: [ '*.js', '!*.min.js', '!vendor/**', 'vendor/chosen.jquery.js' ],
				dest: '<%= dirs.js %>',
				ext: '.min.js',
				extDot: 'last'
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
					src: [ '<%= dirs.js %>/*.min.js' ]
				}
			},
			css: {
				files: {
					src: [ '<%= dirs.css %>/*.min.css' ]
				}
			}
		},

		// .scss to .css file watcher. Run when project is loaded in PhpStorm or other IDE.
		watch: {
			css: {
				files: '**/*.scss',
				tasks: [ 'sass' ]
			}
		}

	} );

	require( 'load-grunt-tasks' )( grunt );

	grunt.registerTask( 'css', [ 'sass', 'copy:css', 'cssmin', 'usebanner:css' ] );
	grunt.registerTask( 'js', [ 'jshint', 'jscs', 'copy:js', 'uglify', 'usebanner:js' ] );
	grunt.registerTask( 'default', [ 'css', 'js' ] );
	grunt.registerTask( 'build', [ 'default', 'addtextdomain', 'checktextdomain', 'clean:build', 'copy:main', 'compress' ] );

	grunt.util.linefeed = '\n';
};
