jeh/sage-9-wp-cli-commands
===========================

Command line tool to generate sage 9 template and controller files.


## Installing

Installing this package requires WP-CLI v2.4.0 or greater. Update to the latest stable release with `wp cli update`.

You can install this package with following command.

```bash
wp package install git@github.com:jeh5256/sage-9-wp-cli-commands.git
```

## Usage

`wp sage home-page --c --type=page`

This will generate three files:
* template-home-page.blade.php
* content-page-home-page.blade.php
* TemplateHomePage.php


`wp sage news-detail --type=post`

This will generate one file:
* content-single-news-detail.blade.php


**Options**

    --c
        Whether or not to create a controller file.
        
        default: false
        
    --type=<type>
        Create files for post or page
        
        default: page
              options:
                - page
                - post
