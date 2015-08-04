module.exports = function( grunt ) {

	var pkg = grunt.file.readJSON( 'package.json' ),
		// version = 'X.Y.Z'
		version = pkg.version,
		// Files to include in a release
		distFiles =  [
			'stripe/**'
		];

	// Print current version number
	console.log( pkg.version );

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

	grunt.registerTask( 'release', ['clean', 'copy', 'compress'] );

	grunt.registerTask( 'deploy', ['release', 'wp_deploy'] );

	grunt.util.linefeed = '\n';
};