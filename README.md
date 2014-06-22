tect-media
===================================
A plugin that restructures WordPress’s default upload and media behaviour, meant to complement the [tect theme](https://github.com/Arty2/tect).

**WARNING: alpha quality; install at your own risk.**

What it does
-----------------------------------
* Changes the upload directory from `/wp-content/uploads/` to `/media/`.
* Turns off *“Organize my uploads into month- and year-based foldears”*.
* New thumbnails are moved into `/media/thumbnail-size/`, no more dimensions in thumbnail’s filenames. Old thumbnails can be deleted manually with the [delete_deprecated_thumbs.php](https://gist.github.com/Arty2/9390440) script and recreated with [Regenerate Thumbnails](http://wordpress.org/plugins/regenerate-thumbnails/).
* Makes inserted image URLs relative.

What it wants to do
-----------------------------------
* Improve (as defined by the author) image behaviour.
* Responsive images.
* Media migration.

To-do
-----------------------------------
* Fix visual editor hiccups when editing images with relative paths.


[GitHub Updater](https://github.com/afragen/github-updater) enabled.


~ a pet project by [Heracles Papatheodorou](http://archi.tect.gr) a.k.a [Arty2](http://www.twitter.com/Arty2)