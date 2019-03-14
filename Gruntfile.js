'use strict';
module.exports = function(grunt) {

  grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

    uglify: {
      dist: {
        files: {
          'lib/js/init-select2.min.js': [
            'lib/js/init-select2.js',
          ],
          'lib/js/yikes-tax-drag-drop.min.js': [
            'lib/js/yikes-tax-drag-drop.js',
          ],
        }
      }
    },

    cssmin: {
      target: {
        files: [
          {
						'lib/css/yikes-tax-drag-drop.min.css':
						[
							'lib/css/yikes-tax-drag-drop.css',
						],
          }
        ]
      }
    },

    pot: {
      options: {
        text_domain: 'simple-taxonomy-ordering', 
        dest: 'languages/', 
            keywords: [
              '__:1',
              '_e:1',
          '_x:1,2c',
          'esc_html__:1',
          'esc_html_e:1',
          'esc_html_x:1,2c',
          'esc_attr__:1', 
          'esc_attr_e:1', 
          'esc_attr_x:1,2c', 
          '_ex:1,2c',
          '_n:1,2', 
          '_nx:1,2,4c',
          '_n_noop:1,2',
          '_nx_noop:1,2,3c'
        ],
      },
      files: {
        src:  [ '**/*.php' ],
        expand: true,
      }
    }

  });

  // load tasks
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-pot');

  // register task
  grunt.registerTask('default', [
		'uglify',
    'cssmin',
  ]);

};
