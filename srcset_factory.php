#!/usr/bin/env php

<?php

/* Inspired by: https://github.com/MattWilcox/Adaptive-Images */

# BEGIN: Configuration
$config = array (
  'breakpoints' => array(80, 480, 768, 820, 1024, 1640),
  'group_breakpoints' => TRUE, // if TRUE, will group the images into folders for each breakpoint
  'jpeg_quality' => 75,
  'sharpen' => TRUE,
  'exclusions' => array('landscape.gif', '.DS_Store'), // won't do anything with these files
  'conserve_disk_space' => FALSE, // if TRUE, won't duplicate files for breakpoints greater than their widths
  'interlace_jpegs' => TRUE,
  'ratios' => array(1),
  'serialize_filenames' => TRUE, // (almost) guarantees unique filenames
  'template' => '<figure><img alt="" title="{ORIGINAL}" src="/assets/images/{P1}" srcset="/assets/images/{P0} {W0}w, /assets/images/{P1} {W1}w, /assets/images/{P2} {W2}w, /assets/images/{P3} {W3}w, /assets/images/{P4} {W4}w, /assets/images/{P5} {W5}w" sizes="100vw" class="rounded load" width="{W3}" height="{H3}" /><figcaption><strong itemprop="title">IMAGE_TITLE</strong> --- <span>Credit: <a href="AUTHOR_URL"><span itemprop="author">PHOTOGRAPHER_NAME</span>.</a></span> <span>License: <a href="LICENSE_URL" itemprop="license">LICENCE</a></span></figcaption></figure>{: style="width: {W3}px;" .align-center .captioned-image }',
);
# END: Configuration

// Do nothing if this is executed via a web-browser
if (php_sapi_name() !== 'cli') die('FATAL ERROR: You should run this program from the command-line only.' . PHP_EOL);

// Do nothing if the GD library is not available
if (!extension_loaded('gd')) { // it's not loaded
  if (!function_exists('dl') || !dl('gd.so')) { // and we can't load it either
    echo 'FATAL ERROR: GD library is not available, or could not be loaded.' . PHP_EOL;
    exit(1);
  }
}

// Parse Options/Args
$long_opts = array(
  'source:', // source is a required input!
  'destination::' // destination is an optional input - if no destination is given then the program will use the source folder
);
$options = getopt(NULL, $long_opts);

// Do nothing if a path or filename hasn't been supplied
if ( !isset($options['source']) || ($options['source'] == '') ) {
  echo 'Nothing to do. No source path or filename given.' . PHP_EOL;
  exit(1);
}

// Check Destination
if ( isset($options['destination']) ) {
  $options['destination'] = realpath(rtrim($options['destination'], '/'));
  if ( !directory_manager($options['destination']) ) {
    echo 'Destination (' . $options['destination'] . ') does not exist, is not a directory or is otherwise unavailable.' . PHP_EOL;
    exit(1);
  }
}

// Pre-flight
$template_properties = array();

// Run-time!
if (file_exists($options['source'])) {
  $options['source'] = realpath($options['source']);
  $config['allowed_types'] = array('image/jpeg', 'image/png', 'image/gif');
  $response = '';
  if (is_dir($options['source'])) {
    $scanned_directory = array_diff(scandir($options['source']), array('..', '.'));
    foreach ($scanned_directory as $file) {
      $response .= PHP_EOL . create_image_sets($options['source'] . DIRECTORY_SEPARATOR . $file) . PHP_EOL;
    }
  } else {
    $response = create_image_sets($options['source']);
  }
  if ( $response ) {
    echo ltrim(rtrim($response));
    exit(0);
  } else {
    echo 'ALERT: There were errors while creating the image-set!' . PHP_EOL;
    exit(1);
  }
} else {
  echo 'ALERT: The given path or filename does not exist.' . PHP_EOL;
  exit(1);
}

function create_image_sets($filename = NULL) {
  global $config, $template_properties, $options;
  $template_properties = array();
  if ( is_null($filename) || !file_exists($filename) || is_dir($filename) || is_null($config) || !is_array($config) || !count($config) ) return FALSE;

  // Parse the path/filename
  $parts = pathinfo($filename);
  if ( isset($config['exclusions']) && is_array($config['exclusions']) && count($config['exclusions']) && in_array($parts['basename'], $config['exclusions']) ) return FALSE;
  $serialized_filename = md5_file($filename);
  if ( !isset($config['breakpoints']) || !is_array($config['breakpoints']) || !count($config['breakpoints']) ) $config['breakpoints'] = array(480, 768, 1024);

  // Is the file an image?
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime_type = finfo_file($finfo, $filename);
  finfo_close($finfo);
  if ( !in_array($mime_type, $config['allowed_types']) ) return FALSE;

  // Get the image's dimensions
  $dimensions = getimagesize($filename);

  // Cycle through the breakpoints
  foreach ( $config['breakpoints'] as $breakpoint ) {
    $write_directory = ( isset($options['destination']) ) ? $options['destination'] : $parts['dirname'];
    $relative_path = '';
    if ( isset($config['group_breakpoints']) && $config['group_breakpoints'] ) {
      $write_directory .= DIRECTORY_SEPARATOR . $breakpoint;
      $relative_path .= $breakpoint . DIRECTORY_SEPARATOR;
    }
    if ( !directory_manager($write_directory) ) return FALSE;

    // Cycle through the ratios (1x, 2x, etc.)
    if ( !isset($config['ratios']) || !is_array($config['ratios']) || !count($config['ratios']) ) $config['ratios'] = array(1);
    foreach ($config['ratios'] as $ratio) {
      $suffix = '';
      if ( !isset($config['group_breakpoints']) || ((boolean)$config['group_breakpoints'] === FALSE) ) $suffix .= '_' . $breakpoint;
      $suffix .= ( intval($ratio) !== (int)1 ) ? '_@' . $ratio . 'x' : '';
      if ( isset($config['serialize_filenames']) && ((boolean)$config['serialize_filenames'] === TRUE) ) $parts['filename'] = $serialized_filename;
      $write_filename = $parts['filename'] . $suffix;
      switch ( $mime_type ) {
        case 'image/png':
          $extension = 'png';
          break;
        case 'image/gif':
          $extension = 'gif';
          break;
        default:
          $extension = 'jpg';
          break;
      }

      if ( file_exists( $write_directory . DIRECTORY_SEPARATOR . $write_filename . '.' . $extension ) ) {
        $write_filename = $serialized_filename . $suffix;
        $parts['filename'] = $serialized_filename;
      }
      $relative_path .= $write_filename . '.' . $extension;

      if ( !file_exists( $write_directory . DIRECTORY_SEPARATOR . $write_filename . '.' . $extension ) ) {
        if ( $dimensions[0] > ($breakpoint * $ratio) ) {
          $multiplier = $dimensions[1] / $dimensions[0];
          $width = ($breakpoint * $ratio);
          $height = ceil($width * $multiplier);

          $file_spec = array(
            'source'            => $filename,
            'source_width'      => $dimensions[0],
            'source_height'     => $dimensions[1],
            'mime_type'         => $mime_type,
            'width'             => $width,
            'height'            => $height,
            'write_directory'   => $write_directory,
            'write_filename'    => $write_filename,
            'relative_path'     => $relative_path,
          );
          if ( !save_image($file_spec) ) return FALSE;
          $template_properties[] = array( 'P' => $file_spec['relative_path'], 'W' => $file_spec['width'], 'H' => $file_spec['height'] );
        } elseif ( !$config['conserve_disk_space'] ) {
          $destination = $write_directory . DIRECTORY_SEPARATOR . $write_filename . '.';
          $destination .= $extension;
          if ( !copy($filename, $destination) ) return FALSE;
          $template_properties[] = array( 'P' => $relative_path, 'W' => $dimensions[0], 'H' => $dimensions[1] );
        }
      } else {
        return FALSE;
      }

    } // END: each ratio
  } // END: each breakpoint

  $write_directory = ( isset($options['destination']) ) ? $options['destination'] : $parts['dirname'];
  $write_directory .= DIRECTORY_SEPARATOR . 'sources';
  if ( !directory_manager($write_directory) ) return FALSE;
  if ( !file_exists( $write_directory . DIRECTORY_SEPARATOR . $parts['filename'] . '.' . $extension ) ) {
    if ( !rename($filename, $write_directory . DIRECTORY_SEPARATOR . $parts['filename'] . '.' . $extension) ) return FALSE;
  }
  if ( isset($config['template']) && $config['template'] ) {
    $response = $config['template'];
    $response = preg_replace_callback( '(\{(P|W|H)(\d)\})', function ($m) { global $template_properties; return $template_properties[ $m[2] ][ $m[1] ]; }, $response );
    $response = preg_replace('/\{ORIGINAL\}/', $parts['basename'], $response);
    return $response;
  } else {
    return TRUE;
  }
}

function save_image($file_spec = NULL) {
  if ( is_null($file_spec) || !is_array($file_spec) || !count($file_spec) ) return FALSE;

  $destination = imagecreatetruecolor($file_spec['width'], $file_spec['height']);

  switch ( $file_spec['mime_type'] ) {
    case 'image/png':
      $source = @imagecreatefrompng($file_spec['source']);
      imagealphablending($destination, FALSE);
      imagesavealpha($destination, TRUE);
      $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
      imagefilledrectangle($destination, 0, 0, $file_spec['width'], $file_spec['height'], $transparent);
      break;
    case 'image/gif':
      $source = @imagecreatefromgif($file_spec['source']);
      $current_transparent = imagecolortransparent($source);
      if ($current_transparent != -1) {
        $transparent_colour = imagecolorsforindex($source, $current_transparent);
        $current_transparent = imagecolorallocate($destination, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
        imagefill($destination, 0, 0, $current_transparent);
        imagecolortransparent($destination, $current_transparent);
      }
      break;
    default:
      $source = @imagecreatefromjpeg($file_spec['source']);
      if ( isset($config['interlace_jpegs']) && $config['interlace_jpegs'] ) imageinterlace($destination, TRUE);
      break;
  }

  imagecopyresampled($destination, $source, 0, 0, 0, 0, $file_spec['width'], $file_spec['height'], $file_spec['source_width'], $file_spec['source_height']);
  imagedestroy($source);

  if ( isset($config['sharpen']) && $config['sharpen'] && function_exists('imageconvolution') ) {
    $sharpness = calculate_sharpness_adjustment($file_spec['source_width'], $file_spec['width']);
    $convolution_matrix = array(
      array(-1, -2, -1),
      array(-2, $sharpness + 12, -2),
      array(-1, -2, -1)
    );
    imageconvolution($destination, $convolution_matrix, $sharpness, 0);
  }

  if ( !is_writable($file_spec['write_directory']) ) return FALSE;

  $new_image = NULL;
  $saved = FALSE;
  switch ( $file_spec['mime_type'] ) {
    case 'image/png':
      $new_image = $file_spec['write_directory'] . DIRECTORY_SEPARATOR . $file_spec['write_filename'] . '.png';
      $saved = imagepng($destination, $new_image);
      break;
    case 'image/gif':
      $new_image = $file_spec['write_directory'] . DIRECTORY_SEPARATOR . $file_spec['write_filename'] . '.gif';
      $saved = imagegif($destination, $new_image);
      break;
    default:
      $new_image = $file_spec['write_directory'] . DIRECTORY_SEPARATOR . $file_spec['write_filename'] . '.jpg';
      $quality = ( isset($config['jpeg_quality']) && is_numeric($config['jpeg_quality']) ) ? intval($config['jpeg_quality']) : 75;
      $saved = imagejpeg($destination, $new_image, $quality);
      break;
  }
  imagedestroy($destination);

  if ( !$saved || is_null($new_image) || !file_exists($new_image) ) {
    return FALSE;
  } else {
    return TRUE;
  }
}

function calculate_sharpness_adjustment($width_source, $width_destination) {
  $width_destination = $width_destination * (750.0 / $width_source);
  $intA = 52;
  $intB = -0.27810650887573124;
  $intC = .00047337278106508946;
  $intRes = $intA + $intB * $width_destination + $intC * $width_destination * $width_destination;
  return max(round($intRes), 0);
}

function directory_manager($write_directory = NULL) {
  if ( is_null($write_directory) || !$write_directory ) return FALSE;
  // Ensure that the folder-structure can support our write plan
  if ( file_exists($write_directory) ) { // write path exists
    if ( is_dir($write_directory) ) { // it's a directory
      if ( !is_writable($write_directory) ) { // the directory is not writable
        // if we can't make it writable then bail out
        if ( !chmod($write_directory, 0777) ) return FALSE;
      }
      // the directory is writable
    } else {
      // it's not a directory
      return FALSE;
    }
  } else {
    // the directory doesn't exist
    if ( !mkdir($write_directory, 0777) ) { // and we're unable to create it
      // but make sure there wasn't a race condition before bailing out
      if ( !file_exists($write_directory) || !is_dir($write_directory) ) return FALSE;
    }
  }
  return TRUE;
}
