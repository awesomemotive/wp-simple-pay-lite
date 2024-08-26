module.exports = function (grunt) {
  var pkg = grunt.file.readJSON("package.json");

  console.log(pkg.title + " - " + pkg.version);

  // Set files to include/exclude in a release.
  var distFiles = [
    "**",
    "!bower_components/**",
    "!build/**",
    "!node_modules/**",
    "!vendor/bin/**",
    "!wordpress_org_assets/**",
    "!.editorconfig",
    "!.gitignore",
    "!.jshintrc",
    "!bower.json",
    "!composer.json",
    "!composer.lock",
    "!contributing.md",
    "!gruntfile.js",
    "!package.json",
    "!package-lock.json",
    "!readme.md",
    "!**/*~",
  ];

  const updatedVersion = process.env.PLUGIN_VERSION
    ? process.env.PLUGIN_VERSION
    : pkg.version;

  grunt.initConfig({
    pkg: pkg,

    // Validates text domain.
    sed: {
      text_domain: {
        pattern: '"simple-pay"',
        replacement: '"stripe"',
        recursive: true,
        path: "includes/core/assets/js/dist",
      },
      update_stripe_checkout_version: {
        path: "stripe-checkout.php",
        pattern: "Version: [0-9]+\\.[0-9]+\\.[0-9]+",
        replacement: `Version: ${updatedVersion}`,
        recursive: false,
      },
      // Update the version number in the SIMPLE_PAY_VERSION definition
      update_stripe_checkout_define: {
        path: "stripe-checkout.php",
        pattern:
          "define\\( 'SIMPLE_PAY_VERSION', '[0-9]+\\.[0-9]+\\.[0-9]+' \\);",
        replacement: `define( 'SIMPLE_PAY_VERSION', '${updatedVersion}' );`,
        recursive: false,
      },
      update_readme: {
        path: "readme.txt",
        pattern: "Stable tag: [0-9]+\\.[0-9]+\\.[0-9]+",
        replacement: `Stable tag: ${updatedVersion}`,
        recursive: false,
      },
      update_package_json: {
        path: "package.json",
        pattern: '"version": "[0-9]+\\.[0-9]+\\.[0-9]+",',
        replacement: `"version": "${updatedVersion}",`,
        recursive: false,
      },
    },

    checktextdomain: {
      options: {
        text_domain: "stripe",
        correct_domain: true,
        keywords: [
          "__:1,2d",
          "_e:1,2d",
          "_x:1,2c,3d",
          "esc_html__:1,2d",
          "esc_html_e:1,2d",
          "esc_html_x:1,2c,3d",
          "esc_attr__:1,2d",
          "esc_attr_e:1,2d",
          "esc_attr_x:1,2c,3d",
          "_ex:1,2c,3d",
          "_n:1,2,4d",
          "_nx:1,2,4c,5d",
          "_n_noop:1,2,3d",
          "_nx_noop:1,2,3c,4d",
          "wp_set_script_translations:1,2d",
        ],
      },
      files: {
        src: [
          "includes/**/*.php",
          "includes/core/assets/js/dist/**.js",
          "views/*.php",
          "src/**/*.php",
          "stripe-checkout.php",
          "uninstall.php",
        ],
        expand: true,
      },
    },

    // Adds/adjusts text domains.
    addtextdomain: {
      options: {
        textdomain: "stripe",
      },
      target: {
        files: {
          src: ["includes/**/*.php", "stripe-checkout.php", "uninstall.php"],
        },
      },
    },

    // Wipe out build folder.
    clean: {
      build: ["build"],
    },

    // Build the plugin zip file and place in build folder.
    compress: {
      main: {
        options: {
          mode: "zip",
          archive: `./build/stripe-${updatedVersion}.zip`,
        },
        expand: true,
        src: distFiles,
        dest: "/stripe",
      },
    },

    // Copy files to build folder.
    copy: {
      main: {
        expand: true,
        src: distFiles,
        dest: "build/stripe",
      },
    },
  });

  require("load-grunt-tasks")(grunt);

  grunt.registerTask("build", [
    "sed:update_stripe_checkout_version",
    "sed:update_stripe_checkout_define",
    "sed:update_readme",
    "sed:update_package_json",
    "checktextdomain",
    "clean:build",
    "copy:main",
    "compress",
  ]);

  grunt.util.linefeed = "\n";
};
