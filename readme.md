# Achievements for WordPress
Achievements gamifies your WordPress site with challenges, badges, and points.

* [Download the plugin from WordPress.org](https://wordpress.org/plugins/achievements).
* For more information, go to [achievementsapp.com](http://achievementsapp.com/).

Have you found a bug, or have a feature request? If you have a Github account, please report issues [here](https://github.com/paulgibbs/achievements/issues). Otherwise, send an email to: paul at byotos.com.

If you just want to use Achievements on your WordPress site, it's best to download the plugin from WordPress.org. This Github repository is intended for developers and other people who want to contribute to the project.

----

Requirements
------------
Install [Node.js](http://nodejs.org/) and the [Grunt CLI](http://gruntjs.com/getting-started).

* **Install Node.js:** You can [download and install the package](http://nodejs.org/) or use a package manager, like [Homebrew](http://brew.sh/).
* **Install the Grunt CLI:** After installing Node, run `npm install -g grunt-cli` in your shell.


Getting Started
---------------
Once you've installed Node.js and the Grunt CLI, you're ready to get started.

1. Clone this repository in a directory of your choice by running `git clone https://github.com/paulgibbs/achievements.git`.
2. Navigate to the directory in your shell.
3. Run `npm install`.


Documentation
-------------

The `src` directory contains the Achievements' plugin core files. You can develop against the `src` directory like you normally would develop against any other WordPress plugins' SVN trunk.

### `grunt` or `grunt build`
Generates the development-optimised source in the `src` directory.

### `grunt build-prod`
Generates the production-optimised source in the `build` directory.

### `grunt clean:all`
Removes the `build` directory.

### `grunt watch:css`
Watches for changes to the LESS files; compiles into CSS, and copies into the `src` directory.