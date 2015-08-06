module.exports = function( grunt ) {

	var pkg = grunt.file.readJSON( 'package.json' ),
	// version = 'vX.Y.Z'
		version = pkg.version,
	// Files to include in a release
		distFiles =  [
			'stripe/**'
		];

	// Print current version number converted to semantic versioning
	console.log( 'New version: ' + pkg.version );

	// Project configuration
	grunt.initConfig( {

		pkg: pkg,
		version : version,

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

	// Load tasks
	require('load-grunt-tasks')(grunt);

	// Register tasks

	grunt.registerTask( 'release', ['clean', 'uglify', 'cssmin', 'copy', 'compress'] );

	grunt.registerTask( 'deploy', ['release', 'wp_deploy'] );

	grunt.util.linefeed = '\n';
};
