# 🖼️ Negarity

**Negarity** is a modern, extensible, and high-performance image processing library for PHP.  
It aims to become the **de facto standard** for graphics manipulation, combining the flexibility of GD, the power of Imagick, and the elegance of a fluent API.

---

## 🚀 Features

- 🧱 Modular architecture — Core, Drivers, Operations, Effects, and more.
- ⚙️ Multi-backend support (GD, Imagick, Vips, custom).
- 🎨 Fluent API for chaining image transformations.
- 🧠 Advanced tools: Extractors, Converters, Compositors, Generators.
- 🧩 Fully PSR-4 compliant, testable, and extensible.
- ⚡ PHP 8.2+ optimized.

---

## 🧰 Installation

```bash
composer require jalaljaberi/negarity
```

---

## 🧪 Quick Start

```php
use Negarity\Core\Image;
use Negarity\Effects\Artistic\Sepia;
use Negarity\Operations\Geometry\Resize;

$image = Image::fromFile('photo.jpg')
    ->apply(new Resize(800, 600))
    ->apply(new Sepia())
    ->save('output.jpg');
```

Or using the processing pipeline:

```php
use Negarity\Pipeline\ImageProcessor;
use Negarity\Operations\Geometry\Resize;
use Negarity\Effects\Artistic\Vignette;

$processor = (new ImageProcessor())
    ->add(new Resize(1024, 768))
    ->add(new Vignette());

$processor->run('input.jpg', 'result.jpg');
```

---

## 🧱 Architecture Overview

Negarity follows a **layered architecture**:

```
Core → Driver → IO → Generators → Operations → Extractors → Converters → 
Compositors → Analyzers → Effects → Pipeline
```

Each layer serves a specific purpose, from low-level pixel manipulation to high-level artistic effects.

---

## 🧑‍💻 Development

Clone the repository and install dependencies:

```bash
git clone https://github.com/jalaljaberi/negarity.git
cd negarity
composer install
```

Run tests:

```bash
composer test
```

---

## 🧩 Contributing

Contributions are welcome!  
Open issues, submit PRs, or suggest new effects and backends.

Please follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards.

---

## 📄 License

MIT License — see [LICENSE](LICENSE) for details.

---

## 🌟 Author

Developed with ❤️ by **Your Name**  
Follow me on [GitHub](https://github.com/jalaljaberi)

