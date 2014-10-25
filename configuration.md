### Configuration

The `$config` array, at the beginning of the script, contains all configurable settings for **srcset_factory**:

##### `breakpoints`

An array of size breakpoints (image widths) that the script should generate images for. Ideally, these would match the breakpoints you use in your <small>CSS</small>.

##### `group_breakpoints`

A boolean value. Set to `TRUE` to direct the script to use sub-folders to group the image size variations. Set to `FALSE` to direct the script to save all sizes directly to `destination` directory.

##### `jpeg_quality`

An integer. Determines the quality of <small>JPEG</small> images the program will generate. A higher value results in better quality but carries the penalty of a larger file-size.

##### `sharpen`

A boolean value. Set to `TRUE` to apply a sharpening matrix to the images the script generates. Set to `false` to apply no sharpening. The program is slower when it applies sharpening &mdash; but probably not in any way that you'd notice.

##### `exclusions`

An array of filenames in the source directory that the program should not try to process.

##### `conserve_disk_space`

Let's say you have a source image that has a width of 1000px. If you set `conserve_disk_space` to `FALSE` and you have breakpoints that are greater than 1000px, then the script will generate a 1000px image for each of those larger breakpoints (it never upscales images). This ensures consistency of image production. However, if you set `conserve_disk_space` to `FALSE` then the script will only produce images for breakpoints that are smaller, or equal to, the width of the source image, thus saving disk space.

##### `interlace_jpegs`

A boolean value. Set to `TRUE` if you require [interlaced <small>JPEG</small>s,](http://tinyurl.com/24qxslv) otherwise set to `FALSE`.

##### `ratios`

An array of integers. We often size [high-<small>PPI</small>](http://en.wikipedia.org/wiki/Pixel_density) images by pixel density ratios. So, for example, an image might have 2&times;, 3&times; or even higher variants to service high-<small>PPI</small> displays. If you wish to produce such images, simply add their ratios to this array. The script will append a ratio suffix to the filenames where appropriate (eg: "my_image_&#x00040;2x.jpg"). Note that you must supply source images of sufficient resolution to support this as the script will not upscale images.

##### `serialize_filenames`

A boolean value. Set to `TRUE` to direct the script to produce unique filenames for the images it generates. It will do this by using [<small>PHP</small>'s `md5_file` method.](http://php.net/manual/en/function.md5-file.php) This will just about guarantee that there will be no filename collisions. Set to `FALSE` to retain the original filenames. If set to `FALSE` and the program detects a filename conflict, it will first try to rename the new file(s) to avoid the collision. Failing that, it will abort. It will not overwrite existing files.

##### `template`

A string that contains the <small>HTML</small> template markup that the script will use to generate a <small>HTML</small> snippet with when it completes image generation. The string contains variables that the script will substitute with image properties.

For each image it generates, the script will provide a filename `{Px}`, width `{Wx}` and height `{Hx}` value. The template can reference these by numerical index (eg: `{P1}`, `{W1}`, `{H1}`, &hellip;)

You can access the variable `{ORIGINAL}` to retrieve the source image's original filename.
