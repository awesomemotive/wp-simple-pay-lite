module.exports = function( grunt ) {

	var pkg = grunt.file.readJSON( 'package.json' ),
		// version = 'vX.Y.Z'
		version = pkg.version,
		// semver = 'X.Y.Z'
		semver = version.substring( 1, version.length ),
		// Files to include in a release
		distFiles =  [
			'stripe/**'
		];

	console.log( pkg.title + ' - ' + semver );

	// Project configuration
	grunt.initConfig( {

		pkg: pkg,
		version : semver,

		clean: {
			main: [ 'build' ]
		},

		copy: {
			main: {
				expand: true,
				src: distFiles,
				dest: 'build'
			}
		},

		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './build/stripe-<%= version %>.zip'
				},
				expand: true,
				src: distFiles,
				dest: ''
			}
		},

		uglify: {
			files: {
				expand: true,
				cwd: 'stripe/assets/js/',
				src: ['admin-*.js', 'public-*.js', '!*.min.js'],
				dest: 'stripe/assets/js/',
				ext: '.min.js'
			}
		},

		cssmin: {
			files: {
				expand: true,
				cwd: 'stripe/assets/css/',
				src: ['admin-*.css', 'public-*.css', 'vendor/toggle-switch.css', '!*.min.css'],
				dest: 'stripe/assets/css/',
				ext: '.min.css'
			}
		},

		wp_deploy: {
			deploy: {
				options: {
					plugin_slug: 'stripe',
					build_dir: 'build/stripe'
				}
			}
		}

	} );

	require('load-grunt-tasks')(grunt);

	grunt.registerTask( 'css',		['cssmin'] );
	grunt.registerTask( 'js',		['uglify'] );
	grunt.registerTask( 'default',  ['css','js'] );
	grunt.registerTask( 'release',	['default', 'clean', 'copy', 'compress'] );
	grunt.registerTask( 'deploy',	['release', 'wp_deploy'] );

	grunt.util.linefeed = '\n';
};
