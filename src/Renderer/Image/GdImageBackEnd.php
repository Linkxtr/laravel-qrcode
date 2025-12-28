<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Renderer\Image;

use BaconQrCode\Exception\RuntimeException;
use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Path\Close;
use BaconQrCode\Renderer\Path\Line;
use BaconQrCode\Renderer\Path\Move;
use BaconQrCode\Renderer\Path\Path;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use GdImage;

final class GdImageBackEnd implements ImageBackEndInterface
{
    private ?GdImage $image = null;

    /** @var array<int, float> */
    private array $transformationMatrix = [1.0, 0.0, 0.0, 1.0, 0.0, 0.0];

    /** @var array<int, array<int, float>> */
    private array $matrixStack = [];

    private string $format;

    public function __construct(string $format = 'png')
    {
        $this->format = $format;
    }

    public function new(int $size, ColorInterface $backgroundColor): void
    {
        if ($size < 1) {
            throw new RuntimeException('Image size must be at least 1 pixel');
        }

        $image = imagecreatetruecolor($size, $size);

        if ($image === false) {
            throw new RuntimeException('Could not create GD image resource');
        }

        $this->image = $image;

        $rgb = $backgroundColor->toRgb();
        $color = imagecolorallocate(
            $this->image,
            max(0, min(255, (int) $rgb->getRed())),
            max(0, min(255, (int) $rgb->getGreen())),
            max(0, min(255, (int) $rgb->getBlue()))
        );

        if ($color === false) {
            throw new RuntimeException('Could not allocate background color');
        }

        imagefill($this->image, 0, 0, $color);

        if ($backgroundColor instanceof Alpha && $backgroundColor->getAlpha() < 100) {
            imagecolortransparent($this->image, $color);
        }
    }

    public function scale(float $size): void
    {
        $this->multiplyMatrix($size, 0, 0, $size, 0, 0);
    }

    public function translate(float $x, float $y): void
    {
        $this->multiplyMatrix(1, 0, 0, 1, $x, $y);
    }

    public function rotate(int $degrees): void
    {
        $radians = deg2rad($degrees);
        $cos = cos($radians);
        $sin = sin($radians);
        $this->multiplyMatrix($cos, $sin, -$sin, $cos, 0, 0);
    }

    public function push(): void
    {
        $this->matrixStack[] = $this->transformationMatrix;
    }

    public function pop(): void
    {
        if (empty($this->matrixStack)) {
            throw new RuntimeException('Matrix stack is empty');
        }
        $this->transformationMatrix = array_pop($this->matrixStack);
    }

    public function drawPathWithColor(Path $path, ColorInterface $color): void
    {
        if ($this->image === null) {
            throw new RuntimeException('No image started');
        }

        $rgb = $color->toRgb();
        $alphaValue = 0;

        if ($color instanceof Alpha) {
            $alphaValue = $color->getAlpha();
        }

        $alpha = max(0, min(127, (int) (127 - ($alphaValue / 100 * 127))));
        $gdColor = imagecolorallocatealpha(
            $this->image,
            max(0, min(255, (int) $rgb->getRed())),
            max(0, min(255, (int) $rgb->getGreen())),
            max(0, min(255, (int) $rgb->getBlue())),
            $alpha
        );

        if ($gdColor === false) {
            throw new RuntimeException('Could not allocate color');
        }

        $this->drawPath($path, $gdColor);
    }

    private function drawPath(Path $path, int $color): void
    {
        $points = [];
        $currentPoint = [0.0, 0.0];

        foreach ($path as $op) {
            if ($op instanceof Move) {
                if (! empty($points)) {
                    $this->flushPolygon($points, $color);
                    $points = [];
                }
                $currentPoint = $this->transformPoint($op->getX(), $op->getY());
                $points[] = $currentPoint[0];
                $points[] = $currentPoint[1];
            } elseif ($op instanceof Line) {
                $currentPoint = $this->transformPoint($op->getX(), $op->getY());
                $points[] = $currentPoint[0];
                $points[] = $currentPoint[1];
            } elseif ($op instanceof Close) {
                $this->flushPolygon($points, $color);
                $points = [];
            }
        }

        if (! empty($points)) {
            $this->flushPolygon($points, $color);
        }
    }

    /** @param array<int, float> $points */
    private function flushPolygon(array $points, int $color): void
    {
        if ($this->image === null) {
            return;
        }

        if (count($points) >= 6) {
            imagefilledpolygon($this->image, $points, $color);
        }
    }

    public function drawPathWithGradient(
        Path $path,
        Gradient $gradient,
        float $x,
        float $y,
        float $width,
        float $height
    ): void {
        $start = $gradient->getStartColor();
        $this->drawPathWithColor($path, $start);
    }

    public function done(): string
    {
        if ($this->image === null) {
            throw new RuntimeException('No image started');
        }

        ob_start();
        if ($this->format === 'webp') {
            imagewebp($this->image);
        } else {
            imagepng($this->image);
        }
        $data = ob_get_clean();

        imagedestroy($this->image);
        $this->image = null;

        return $data ?: '';
    }

    private function multiplyMatrix(float $a, float $b, float $c, float $d, float $x, float $y): void
    {
        $current = $this->transformationMatrix;

        $this->transformationMatrix = [
            $current[0] * $a + $current[2] * $b,
            $current[1] * $a + $current[3] * $b,
            $current[0] * $c + $current[2] * $d,
            $current[1] * $c + $current[3] * $d,
            $current[0] * $x + $current[2] * $y + $current[4],
            $current[1] * $x + $current[3] * $y + $current[5],
        ];
    }

    /** @return array<int, float> */
    private function transformPoint(float $x, float $y): array
    {
        $m = $this->transformationMatrix;

        return [
            $m[0] * $x + $m[2] * $y + $m[4],
            $m[1] * $x + $m[3] * $y + $m[5],
        ];
    }
}
