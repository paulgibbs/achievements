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
					'build/templates/achievements/css/achievements.css':   'src/templates/achievements/css/dev/achievements.less',
					'build/templates/achievements/css/notifications.css': 'src/templates/achievements/css/dev/notifications.less',

					// admin
					'build/includes/admin/css/achievements.css':     'src/includes/admin/css/dev/achievements.less',
					'build/includes/admin/css/admin-editindex.css':  'src/includes/admin/css/dev/admin-editindex.less',
					'build/includes/admin/css/chosen.css':           'src/includes/admin/css/dev/chosen.less',
					'build/includes/admin/css/supportedplugins.css': 'src/includes/admin/css/dev/supportedplugins.less',
					'build/includes/admin/css/users.css':            'src/includes/admin/css/dev/users.less',
				},
			}
		},
		cssmin: {
			core: {
				cwd: BUILD_DIR, // Overwrite the LESS-generated CSS files caused by the previous step
				dest: BUILD_DIR,
				expand: true,
				ext: '.css',
				src: [
					'templates/achievements/css/{achievements|notifications}.css',
					'includes/admin/css/{achievements,admin-editindex,chosen,supportedplugins,users}.css',
				],
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
			}
		},
	});

	// Register tasks.
	grunt.registerTask('build', ['copy:all', 'less:core', 'cssmin:core', 'uglify:core', 'clean:build']);

	// Default task.
	grunt.registerTask('default', ['build']);
};