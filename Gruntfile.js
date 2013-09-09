/*global module:false*/
module.exports = function(grunt) {
	var path = require('path');
	var SOURCE_DIR = 'src/';
	var BUILD_DIR = 'build/';

	// Load tasks.
	require('matchdep').filterDev('grunt-*').forEach( grunt.loadNpmTasks );

	// Project configuration.
	grunt.initConfig({
		clean: {
			all: [BUILD_DIR],
			build: [BUILD_DIR + 'templates/achievements/css/dev/',
							BUILD_DIR + 'includes/admin/css/dev/'],
			dynamic: {
				cwd: BUILD_DIR,
				dot: true,
				expand: true,
				src: [],
			}
		},
		copy: {
			all: {
				files: [
					{
						cwd: SOURCE_DIR,
						dest: BUILD_DIR,
						dot: true,
						expand: true,
						src: ['!**/.{svn,git}/**','**'], // Ignore version control directories.
					}
				]
			},
			dynamic: {
				cwd: SOURCE_DIR,
				dest: BUILD_DIR,
				dot: true,
				expand: true,
				src: [],
			}
		},
		less: {
			core: {
				files: {
					// front-end
					'src/templates/achievements/css/achievements.css':  'src/templates/achievements/css/dev/achievements.less',
					'src/templates/achievements/css/notifications.css': 'src/templates/achievements/css/dev/notifications.less',

					// admin
					'src/includes/admin/css/achievements.css':     'src/includes/admin/css/dev/achievements.less',
					'src/includes/admin/css/admin-editindex.css':  'src/includes/admin/css/dev/admin-editindex.less',
					'src/includes/admin/css/chosen.css':           'src/includes/admin/css/dev/chosen.less',
					'src/includes/admin/css/supportedplugins.css': 'src/includes/admin/css/dev/supportedplugins.less',
					'src/includes/admin/css/users.css':            'src/includes/admin/css/dev/users.less',
				}
			}
		},
		cssmin: {
			ltr: {
				cwd: SOURCE_DIR,
				dest: BUILD_DIR,
				expand: true,
				ext: '.css',
				src: [
					'templates/achievements/css/{achievements,notifications}.css',
					'includes/admin/css/{achievements,admin-editindex,chosen,supportedplugins,users}.css'
				],
				options: {
					banner: '/*! http://wordpress.org/plugins/achievements/ */'
				}
			},
			rtl: {
				cwd: BUILD_DIR,
				dest: BUILD_DIR,
				expand: true,
				ext: '.css',
				src: [
					// RTL from cssjanus
					'templates/achievements/css/{achievements,notifications}-rtl.css',
					'includes/admin/css/{achievements,admin-editindex,chosen,supportedplugins,users}-rtl.css'
				],
				options: {
					banner: '/*! http://wordpress.org/plugins/achievements/ */'
				}
			}
		},
		cssjanus: {
			core: {
				expand: true,
				cwd: SOURCE_DIR,
				dest: BUILD_DIR,
				ext: '-rtl.css',
				src: [
					'templates/achievements/css/{achievements,notifications}.css',
					'includes/admin/css/{achievements,admin-editindex,chosen,supportedplugins,users}.css'
				],
				options: {
					generateExactDuplicates: true
				}
			}
		},
		uglify: {
			core: {
				cwd: SOURCE_DIR,
				dest: BUILD_DIR,
				expand: true,
				ext: '.js',
				src: [
					'includes/admin/js/achievements.js',
					'includes/admin/js/supportedplugins.js',
					'templates/achievements/js/*.js'
				],
			},
			options: {
				banner: '/*! http://wordpress.org/plugins/achievements/ */\n'
			}
		},
		phpunit: {
			all: {
				dir: 'tests/phpunit/'
			}
		}
	});

	// Register tasks.
	grunt.registerTask('build-dev',  ['clean:all', 'less:core']);
	grunt.registerTask('build-prod', ['clean:all', 'less:core', 'copy:all', 'cssjanus:core', 'cssmin:ltr', 'cssmin:rtl', 'uglify:core', 'clean:build', 'phpunit:all']);

	// Default task.
	grunt.registerTask('default', ['build-dev']);
};