# Analyze video,audio,image files (with ffmpeg)

Github: 
![GitHub tag](https://img.shields.io/github/v/tag/brightfish-be/media_analyzer)
![Tests](https://github.com/brightfish-be/media_analyzer/workflows/Run%20Tests/badge.svg)
![Psalm](https://github.com/brightfish-be/media_analyzer/workflows/Detect%20Psalm%20warnings/badge.svg)
![Styling](https://github.com/brightfish-be/media_analyzer/workflows/Check%20&%20fix%20styling/badge.svg)

Packagist: 
[![Packagist Version](https://img.shields.io/packagist/v/brightfish/media_analyzer.svg?style=flat-square)](https://packagist.org/packages/brightfish-be/media_analyzer)
[![Packagist Downloads](https://img.shields.io/packagist/dt/brightfish/media_analyzer.svg?style=flat-square)](https://packagist.org/packages/brightfish-be/media_analyzer)

Analyze video,audio,image files (with ffmpeg)

	created on 2020-11-13 by p.forret@brightfish.be

## Installation

You can install the package via composer:

```bash
composer require brightfish/media_analyzer
```

## Usage

``` php
use Brightfish\MediaAnalyzer\Analyzer;

$obj = new Analyzer();
// or
$obj = new Analyzer("/usr/bin/ffprobe", $logger, $cache);

$obj->meta("video.mp4");
echo $obj->video->fps; 
echo $obj->audio->sample_rate;
echo $obj->container->duration;
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Peter Forret](https://github.com/cinemapub)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
