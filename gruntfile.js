module.exports = function( grunt ) {

	var pkg = grunt.file.readJSON( 'package.json' ),
		// Files to include in a release
		distFiles =  [
			'stripe/**',
			'!stripe/assets/img/wp/**'
		];

	console.log( pkg.title + ' - ' + pkg.version );

	// Project configuration
	grunt.initConfig( {

		pkg: pkg,

		checktextdomain: {
			options:{
				text_domain: 'sc',
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
				src:  ['stripe/**/*.php'],
				expand: true
			}
		},

		makepot: {
			target: {
				options: {
					cwd: 'stripe',
					domainPath: '/languages',
					exclude: [],
					include: [],
					mainFile: 'stripe-checkout.php',
					potComments: '',
					potFilename: 'sc.pot',
					potHeaders: {
						poedit: true,
						'report-msgid-bugs-to': 'https://github.com/moonstonemedia/WP-Simple-Pay-Lite-for-Stripe/issues',
						'last-translator' : 'Phil Derksen <pderksen@gmail.com>',
						'language-Team' : 'Phil Derksen <pderksen@gmail.com>',
						'x-poedit-keywordslist': true
					},
					type: 'wp-plugin',
					updateTimestamp: true,
					updatePoFiles: true
				}
			}
		},

		po2mo: {
			files: {
				src: 'stripe/languages/*.po',
				expand: true
			}
		},

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
					archive: './build/wp-simple-pay-lite-for-stripe-<%= pkg.version %>.zip'
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
					plugin_slug: 'stripe-checkout',
					build_dir: 'build/stripe'
				}
			}
		}

	} );

	require('load-grunt-tasks')(grunt);

	grunt.registerTask( 'localize', ['checktextdomain', 'makepot', 'po2mo'] );
	grunt.registerTask( 'css',		['cssmin'] );
	grunt.registerTask( 'js',		['uglify'] );
	grunt.registerTask( 'default',  ['css','js'] );
	grunt.registerTask( 'build',	['default', 'clean', 'copy', 'compress'] );
	grunt.registerTask( 'release',	['build'] );
	grunt.registerTask( 'deploy',	['release', 'wp_deploy'] );

	grunt.util.linefeed = '\n';
};
