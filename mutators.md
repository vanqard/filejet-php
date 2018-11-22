## Mutators

FileJet provides an easy way to construct a mutations of an origin file.
Here, you can find all supported mutators alongside with their examples.

### Basics

Almost all mutators consist of prefix (text before very first underscore)
and their value, for example `resize_100x100` or `crop_100x100_10_10`.

Most of the mutators can be chained, for example following mutation will
firstly crop an image to 500x500px and then resize it to 200x200px and finally rotate it 90 degrees clockwise:

```
crop_500x500,resize_200x200,rotate_90
```

### Geometry

A lot of mutators expecting value in Geometry format, so lets start with it.

Example geometry:

```
1000x500shrink_100_100 = (width)x(height)(modifier)_(offsetX)_(offestY)
```

As you can see, in geometry, we are providing an information about our expectations.
In our example, resulting image will be 1000px wide, 500px tall and offset for processing
will be 100px on both axis. A strange **shrink** modifier saying that FileJet should process
an original image only when width is large then 1000px or height is large then 500px.

In another words, FileJet will process only images bigger then 1000x500px.
Other images will be passed in the original form.

For more information, see the following table:

| Geometry                   | Description                                                                                         |
| -------------------------- | --------------------------------------------------------------------------------------------------- |
| width                      | Width given, height automatically selected to preserve aspect ratio.                                |
| xheight                    | Height given, width automatically selected to preserve aspect ratio.                                |
| widthxheight               | Maximum values of height and width given, aspect ratio preserved.                                   |
| widthxheight**min**        | Minimum values of width and height given, aspect ratio preserved.                                   |
| widthxheight**exact**      | Width and height emphatically given, original aspect ratio ignored.                                 |
| widthxheight**shrink**     | Shrinks an image with dimension(s) larger than the corresponding width and/or height argument(s).   |
| widthxheight**enlarge**    | Enlarges an image with dimension(s) smaller than the corresponding width and/or height argument(s). |
| {size}_{-}x_{-}y           | Horizontal and vertical offsets x and y, specified in pixels.                                       |

Size can be always specified as integer or as float. Integers are considered as pixels.
However, floats are considered as relative size to real image size.
For example `resize_1.5` will scale image to 150%.

## Resize

Resize, scale, shrink or enlarge an image.

* Available prefixes: **resize**, **scale**

Examples:

```
resize_0.5
resize_100
resize_x100
resize_100x100exact
resize_100x100min
resize_100x100shrink
resize_100x100enlarge
```

## Crop

Crop an image.

To allow users of your project to crop their images, crop mutator supports relative crops.
Thanks to that, you can let users to crop their images without knowing which sizes you will need in future.

To use a relative crop, just specify width, height, x and y of geometry in float numbers from interval **(0, 1>**.

Relative crop which will start in the first quarter of width and height and it's size will be a half of the image
will look like this: `crop_0.5x0.5_0.25_0.25`.

Relative crops are preferred way for cropping an images by users as the only thing which matters is aspect ratio.

* Available prefixes: **crop**

Examples:

```
crop_100x100
crop_100x100_10_10
crop_0.2x0.2_0.1_0.1
```

## Rotate

Rotates an image in clockwise direction by a given number of degrees.

* Available prefixes: **rotate**

Examples:

```
rotate_90
rotate_400
```

## Fill

Fills an image into a new image with a specified size.
Default background color for images bigger then origin file is white.

* Available prefixes: **fill**

Examples:

```
fill_100x100
```

## Background

Change a background of a generated part of the new image.

Often used with **fill** mutator to change a background color if an origin file was smaller then result.

* Available prefixes: **bg**
* Available values: [see colors](http://www.imagemagick.org/script/color.php#color_names)

Examples:

```
bg_black,fill_1000x1000
bg_white,fill_1000x1000
```

## Position

Change a position of origin file in result.

Often used with **fill** mutator to change the position of an origin file in new image, which is bigger.

* Available prefixes: **pos**
* Available values: northwest, north, northeast, west, center, east, southwest, south, southeast

Examples:

```
pos_center,fill_1000x1000
pos_southeast,bg_black,fill_1000x1000
```

## No auto-orient

Disable the auto orientation of mutated images.
In default, FileJet will auto rotate all images, which origin contains rotation information.

To disable this functionality, you can provide `no_ao` mutator.

## Quality (JPEG only)

Change the resulting JPEG quality. It will be ignored in non-jpeg images.

Default quality, when this mutator is omitted, is 92%.

* Available prefixes: **q**

Examples:

```
resize_100x100,q_80
```
