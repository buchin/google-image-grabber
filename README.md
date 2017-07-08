# Google Image Grabber

Scrape google images using PHP

## Getting Started

Get this up and running

### Prerequisites

composer

### Installing

```bash
composer require buchin/google-image-grabber
```

### Usage

```php
use Buchin\GoogleImageGrabber\GoogleImageGrabber;

$keyword = 'makan nasi';

$images = GoogleImageGrabber::grab($keyword);

```

## Test

```bash
./vendor/bin/kahlan --reporter=verbose
```

## Authors

* **Mochammad Masbuchin** - *Initial work* - [buchin](https://github.com/buchin)

See also the list of [contributors](https://github.com/your/project/contributors) who participated in this project.

## License

Halal
