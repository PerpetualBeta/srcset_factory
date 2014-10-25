### Introduction

A <small>PHP</small> script to automate the production, from a single source image, of a set of images that the <small>HTML</small> `srcset` attribute requires. I wrote **srcset_factory** to act as [a component part of a `srcset` production workflow,](https://www.perpetual-beta.org/weblog/srcset-production-factory.html) but you can use it as a standalone script where appropriate.

Given a source image &mdash; of the types: <small>GIF</small>, <small>JPEG</small> or <small>PNG</small> &mdash; and, optionally, a destination directory, **srcset_factory** will generate different sized versions of the source image with widths that match breakpoints and/or ratios (@2x, @3x, etc.) specified in its `$config` array.

Given a source directory, **srcset_factory** will process each image file (<small>GIF</small>, <small>JPEG</small> or <small>PNG</small>) that it finds within.

It will optionally group them in sub-directories by size.

The script handles translucency correctly with <small>GIF</small> and <small>PNG</small> images.

Aside from the images it produces, **srcset_factory** will also output a template-based snippet of <small>HTML</small>, that you can pipe to your system clipboard, for pasting directly into your markup or <small>CMS</small>.

The program is non-destructive (the source image is always preserved) although, in some circumstances, it will rename source images to avoid file-system conflicts.

### Limitations

1. Performance: **srcset_factory** is a <small>PHP</small> script and uses the [<small>GD</small> library](http://www.libgd.org/) to process images. It can be time-consuming to process a large volume of images;
2. While **srcset_factory** can process animated <small>GIF</small>s, the results will not be what you expect. 😃

### Installation

1. [Download `srcset_factory/archive/master.zip` from GitHub;](https://github.com/PerpetualBeta/srcset_factory/archive/master.zip)
2. Extract `srcset_factory.php` from the archive and move it to a permanent location on your system. I run it from a folder named `Helpers` in my home directory;
3. Open `srcset_factory.php` in your text editor and adjust the `$config` variables to suit your use case. If you are unsure, the default configuration gives you a useable starting point. See: [Configuration](configuration.md) for further details;
4. You may need to adjust the [shebang line](http://tinyurl.com/2pscey) to fit your system (eg: if `which php` returns `/usr/bin/php` then the shebang line should read `#!/usr/bin/php`). However, in most cases, this won't be necessary;
5. Make the `srcset_factory.php` script executable, eg.: `chmod a+x ~/Helpers/srcset_factory.php`.

### Usage

```bash
/path/to/srcset_factory.php --source=/path/to/srcset_drop_folder [--destination=/path/to/destination_folder]
```

Where `source` is the path to an image or folder and (optionally) `destination` is the path to a folder. If you do not specify a `destination` folder then the script to save its output to the `source` folder.

### Polyfill

I recommend that you use **srcset_factory** in conjunction with the [Picturefill polyfill](http://scottjehl.github.io/picturefill/) until [browser support catches up with `srcset`.](http://caniuse.com/#feat=srcset)

### See Also:

* [Configuration.](configuration.md)
* [Automated "srcset" Image Factory](https://www.perpetual-beta.org/weblog/srcset-production-factory.html) (workflow).

### Inspiration

* Matt Wilcox's [Adaptive Images](https://github.com/MattWilcox/Adaptive-Images) &mdash; [(CC BY 3.0)](http://creativecommons.org/licenses/by/3.0/)
* [Responsive Images Community Group](http://responsiveimages.org/)
* [Automation](http://xkcd.com/1319/)

### Licence

<div style="text-align: center;">
<a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-sa/4.0/88x31.png" /></a><br />The license for <span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">srcset_factory.php</span>, by <a xmlns:cc="http://creativecommons.org/ns#" href="https://www.perpetual-beta.org/weblog/srcset-production-factory.html" property="cc:attributionName" rel="cc:attributionURL">Jonathan Hollin</a>, is the<br /><a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>.
</div>
