module.exports = function( grunt ) {

	var pkg = grunt.file.readJSON( 'package.json' );

	console.log( pkg.title + ' - ' + pkg.version );

	// Files to include in a release
	var distFiles = [
		'**',
		'!.git/**',
		'!assets/img/wp/**',
		'!build/**',
		'!node_modules/**',
		'!.editorconfig',
		'!.gitignore',
		'!.gitmodules',
		'!gruntfile.js',
		'!package.json',
		'!**/*~'
	];

	// Project configuration
	grunt.initConfig( {

		pkg: pkg,

		banner: '/*! <%= pkg.title %> - <%= pkg.version %>\n' +
		        ' * <%=pkg.homepage %>\n' +
		        ' * Copyright (c) Moonstone Media <%= grunt.template.today("yyyy") %>\n' +
		        ' * Licensed GPLv2+' +
		        ' */\n',

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
				src: [ '**/*.php' ],
				expand: true
			}
		},

		clean: {
			main: [ 'build' ]
		},

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

		copy: {
			main: {
				expand: true,
				src: distFiles,
				dest: 'build/stripe'
			}
		},

		cssmin: {
			files: {
				expand: true,
				cwd: 'assets/css/',
				src: [ 'admin-*.css', 'public-*.css', 'vendor/toggle-switch.css', '!*.min.css' ],
				dest: 'assets/css/',
				ext: '.min.css'
			}
		},

		uglify: {
			files: {
				expand: true,
				cwd: 'assets/js/',
				src: [ 'admin-*.js', 'public-*.js', '!*.min.js' ],
				dest: 'assets/js/',
				ext: '.min.js'
			}
		}

	} );

	require( 'load-grunt-tasks' )( grunt );

	grunt.registerTask( 'css', [ 'cssmin' ] );
	grunt.registerTask( 'js', [ 'uglify' ] );
	grunt.registerTask( 'default', [ 'css', 'js' ] );

	// TODO Add checktextdomain to build task
	grunt.registerTask( 'build', [ 'default', 'clean', 'copy', 'compress' ] );

	// TODO Add deploy task
	//grunt.registerTask( 'deploy',	['build'] );

	grunt.util.linefeed = '\n';
};
