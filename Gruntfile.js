/* jshint node:true */
/* global module */
module.exports = function( grunt ) {
	var path   = require( 'path' ),
	SOURCE_DIR = 'src/',
	BUILD_DIR  = 'build/',

	DPA_LESS = {
		// front-end
		'src/templates/default/css/achievements.css':   'src/templates/default/css/dev/achievements.less',

		// admin
		'src/includes/admin/css/achievements.css':     'src/includes/admin/css/dev/achievements.less',
		'src/includes/admin/css/admin-editindex.css':  'src/includes/admin/css/dev/admin-editindex.less',
		'src/includes/admin/css/chosen.css':           'src/includes/admin/css/dev/chosen.less',
		'src/includes/admin/css/supportedplugins.css': 'src/includes/admin/css/dev/supportedplugins.less',
		'src/includes/admin/css/users.css':            'src/includes/admin/css/dev/users.less'
	},

	DPA_CSS = [
		'templates/default/css/*.css',
		'includes/admin/css/*.css'
	],

	DPA_JS = [
		'templates/default/js/*.js',
		'includes/admin/js/*.js',
		'!includes/admin/js/*-min.js'  // Exclude 3rd party minified libraries
	];

	// Load tasks.
	require( 'matchdep' ).filterDev( 'grunt-*' ).forEach( grunt.loadNpmTasks );

	// Project configuration.
	grunt.initConfig( {
		clean: {
			all: [ BUILD_DIR ],
			dynamic: {
				cwd: BUILD_DIR,
				dot: true,
				expand: true,
				src: []
			}
		},
		less: {
			core: { files: DPA_LESS }
		},
		copy: {
			files: {
				files: [
					{
						cwd: SOURCE_DIR,
						dest: BUILD_DIR,
						dot: true,
						expand: true,
						src: ['**','!**/.{svn,git}/**', '!**/dev/**']  // Ignore version control directories and CSS LESS folders.
					}
				]
			},
			dynamic: {
				cwd: SOURCE_DIR,
				dest: BUILD_DIR,
				dot: true,
				expand: true,
				src: []
			}
		},
		imagemin: {
			core: {
				expand: true,
				cwd: SOURCE_DIR,
				src: ['**/*.{png,jpg,gif,jpeg}'],
				dest: SOURCE_DIR
			}
		},
		cssjanus: {
			core: {
				expand: true,
				cwd: SOURCE_DIR,
				dest: BUILD_DIR,
				ext: '-rtl.css',
				src: DPA_CSS,
				options: { generateExactDuplicates: true }
			}
		},
		cssmin: {
			ltr: {
				cwd: BUILD_DIR,
				dest: BUILD_DIR,
				expand: true,
				ext: '.css',
				src: DPA_CSS,
				options: { banner: '/*! https://wordpress.org/plugins/achievements/ */' }
			},
			rtl: {
				cwd: BUILD_DIR,
				dest: BUILD_DIR,
				expand: true,
				ext: '.css',
				src: DPA_CSS.map( function( filename ) {
					return filename.replace( '.css', '-rtl.css' );
				}),
				options: { banner: '\n' }
			}
		},
		jshint: {
			options: grunt.file.readJSON( '.jshintrc' ),
			grunt: {
				src: ['Gruntfile.js']
			},
			core: {
				expand: true,
				cwd: SOURCE_DIR,
				src: DPA_JS,

				/**
				 * Limit JSHint's run to a single specified file: grunt jshint:core --file=filename.js
				 *
				 * @param {String} filepath
				 * @returns {Bool}
				 */
				filter: function( filepath ) {
					var index, file = grunt.option( 'file' );

					// Don't filter when no target file is specified
					if ( ! file ) {
						return true;
					}

					// Normalise filepath for Windows
					filepath = filepath.replace( /\\/g, '/' );
					index = filepath.lastIndexOf( '/' + file );

					// Match only the filename passed from cli
					if ( filepath === file || ( -1 !== index && index === filepath.length - ( file.length + 1 ) ) ) {
						return true;
					}

					return false;
				}
			}
		},
		checktextdomain: {
			options: {
				correct_domain: false,
				text_domain: 'achievements',
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'_n:1,2,4d',
					'_ex:1,2c,3d',
					'_nx:1,2,4c,5d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src: SOURCE_DIR + '**/*.php',
				expand: true
			}
		},
		makepot: {
			target: {
				options: {
					cwd: SOURCE_DIR,
					domainPath: '.',
					mainFile: 'achievements.php',
					potFilename: 'achievements.pot',
					type: 'wp-plugin'
				}
			}
		},
		uglify: {
			core: {
				cwd: BUILD_DIR,
				dest: BUILD_DIR,
				expand: true,
				ext: '.js',
				src: DPA_JS
			},
			options: { banner: '/*! https://wordpress.org/plugins/achievements/ */' }
		},
		phpunit: {
			'default': {
				cmd: 'phpunit',
				args: ['-c', 'phpunit.xml.dist']
			},
			multisite: {
				cmd: 'phpunit',
				args: ['-c', 'tests/phpunit/multisite.xml']
			}
		},
		watch: {
			css: {
				files: DPA_LESS,
				tasks: ['less:core'],
				options: {
					spawn: false
				}
			}
		}
	});

	// Register tasks.
	grunt.registerTask( 'build-dev',    ['clean:all', 'less:core'] );
	grunt.registerTask( 'build-commit', ['clean:all', 'less:core', 'jshint:core', 'checktextdomain', 'makepot', 'imagemin'] );
	grunt.registerTask( 'build-prod',   ['clean:all', 'less:core', 'jshint:core', 'checktextdomain', 'makepot', 'imagemin', 'copy:files', 'uglify:core', 'cssjanus:core', 'cssmin:ltr', 'cssmin:rtl'] );

	// Testing tasks.
	grunt.registerMultiTask( 'phpunit', 'Runs PHPUnit tests, including the ajax and multisite tests.', function() {
		grunt.util.spawn( {
			cmd:  this.data.cmd,
			args: this.data.args,
			opts: { stdio: 'inherit' }
		}, this.async() );
	});
	grunt.registerTask( 'test', 'Runs all unit tasks.', ['phpunit'] );

	// Default task.
	grunt.registerTask( 'default', ['build-dev'] );

	/**
	 * Add a listener to the watch task.
	 *
	 * On `watch:all`, automatically updates the `copy:dynamic` and `clean:dynamic` configurations so that only the changed files are updated.
	 * On `watch:rtl`, automatically updates the `cssjanus:dynamic` configuration.
	 */
	grunt.event.on( 'watch', function( action, filepath, target ) {
		if ( target !== 'all' && target !== 'rtl' ) {
			return;
		}

		var relativePath = path.relative( SOURCE_DIR, filepath ),
		cleanSrc = ( action === 'deleted' ) ? [ relativePath ] : [],
		copySrc  = ( action === 'deleted' ) ? [] : [ relativePath ];

		grunt.config( ['clean', 'dynamic', 'src'], cleanSrc );
		grunt.config( ['copy', 'dynamic', 'src'], copySrc );
		grunt.config( ['cssjanus', 'dynamic', 'src'], copySrc );
	});
};