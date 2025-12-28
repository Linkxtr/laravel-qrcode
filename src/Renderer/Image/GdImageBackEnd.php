<?php
declare(strict_types = 1);

namespace Linkxtr\QrCode\Renderer\Image;

use BaconQrCode\Exception\RuntimeException;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Path\Path;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use GdImage;

final class GdImageBackEnd implements ImageBackEndInterface
{
    private ?GdImage $image = null;
    private int $width;
    private int $height;
    private array $transformationMatrix = [1, 0, 0, 1, 0, 0];
    private array $matrixStack = [];
    private string $format;

    public function __construct(string $format = 'png')
    {
        $this->format = $format;
    }

    public function new(int $size, ColorInterface $backgroundColor): void
    {
        $this->width = $size;
        $this->height = $size;
        $this->image = imagecreatetruecolor($size, $size);

        if ($this->image === false) {
            throw new RuntimeException('Could not create GD image resource');
        }

        $rgb = $backgroundColor->toRgb();
        $color = imagecolorallocate($this->image, $rgb->getRed(), $rgb->getGreen(), $rgb->getBlue());
        imagefill($this->image, 0, 0, $color);

        if ($backgroundColor->getAlpha() < 100) {
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
        $alpha = (int) (127 - ($color->getAlpha() / 100 * 127));
        $gdColor = imagecolorallocatealpha($this->image, $rgb->getRed(), $rgb->getGreen(), $rgb->getBlue(), $alpha);

        $this->drawPath($path, $gdColor);
    }

    private function drawPath(Path $path, int $color): void
    {
         // Implementation assuming polygons (e.g. square modules).
         // Complex paths with curves (Cubic bezier 'C', Elliptic arc 'A') are not fully supported
         // in this basic GD implementation and will result in skipped segments or simplified rendering.
         // This is sufficient for standard square-module QR codes.
         $points = [];
         $currentPoint = [0, 0];

         foreach ($path as $op) {
             switch ($op[0]) {
                 case 'M': // MoveTo
                     if (!empty($points)) {
                         $this->flushPolygon($points, $color);
                         $points = [];
                     }
                     $currentPoint = $this->transformPoint($op[1], $op[2]);
                     $points[] = $currentPoint[0];
                     $points[] = $currentPoint[1];
                     break;
                 case 'L': // LineTo
                     $currentPoint = $this->transformPoint($op[1], $op[2]);
                     $points[] = $currentPoint[0];
                     $points[] = $currentPoint[1];
                     break;
                 case 'C': // CurveTo
                 case 'A': // EllipticArc
                     // Not supported in this basic GD backend.
                     break;
                 case 'Z': // Close
                     $this->flushPolygon($points, $color);
                     $points = [];
                     break;
             }
         }

         if (!empty($points)) {
             $this->flushPolygon($points, $color);
         }
    }

    private function flushPolygon(array $points, int $color): void
    {
        if (count($points) >= 6) { // At least 3 points (x,y)
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
        // Gradient support in GD is not implemented.
        // Fallback to the start color of the gradient.
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

    private function transformPoint(float $x, float $y): array
    {
        $m = $this->transformationMatrix;
        return [
            $m[0] * $x + $m[2] * $y + $m[4],
            $m[1] * $x + $m[3] * $y + $m[5],
        ];
    }
}
