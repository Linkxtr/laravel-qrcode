<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Mergers;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\MergerInterface;
use RuntimeException;

final readonly class EpsMerger implements MergerInterface
{
    public function __construct(
        private string $epsContent,
        private string $mergeImageContent,
        private float $percentage
    ) {}

    public function merge(): string
    {
        if ($this->percentage <= 0 || $this->percentage >= 1) {
            throw new InvalidArgumentException('$percentage must be between 0 and 1');
        }

        if (! preg_match('/%%BoundingBox:\s*(-?\d+)\s+(-?\d+)\s+(-?\d+)\s+(-?\d+)/', $this->epsContent, $matches)) {
            throw new InvalidArgumentException('Could not determine EPS dimensions (Missing %%BoundingBox).');
        }

        $llx = (int) $matches[1];
        $lly = (int) $matches[2];
        $urx = (int) $matches[3];
        $ury = (int) $matches[4];

        $qrWidth = $urx - $llx;
        $qrHeight = $ury - $lly;

        $logo = @imagecreatefromstring($this->mergeImageContent);

        if (! $logo) {
            throw new InvalidArgumentException('Invalid merge image provided.');
        }

        $logoW = imagesx($logo);
        $logoH = imagesy($logo);

        $ratio = $logoW / $logoH;

        $targetW = max(1, (int) ($qrWidth * $this->percentage));
        $targetH = max(1, (int) ($targetW / $ratio));

        $posX = (int) (($qrWidth - $targetW) / 2) + $llx;
        $posY = (int) (($qrHeight - $targetH) / 2) + $lly;

        $resizedLogo = imagecreatetruecolor($targetW, $targetH);

        if (! $resizedLogo) {
            throw new RuntimeException('Failed to create resized logo canvas.');
        }

        $white = imagecolorallocate($resizedLogo, 255, 255, 255);

        if (! $white) {
            throw new InvalidArgumentException('Could not allocate white color for the logo.');
        }

        imagefill($resizedLogo, 0, 0, $white);

        imagecopyresampled(
            $resizedLogo, $logo,
            0, 0, 0, 0,
            $targetW, $targetH,
            $logoW, $logoH
        );

        ob_start();
        for ($y = 0; $y < $targetH; $y++) {
            for ($x = 0; $x < $targetW; $x++) {
                $rgb = imagecolorat($resizedLogo, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                printf('%02x%02x%02x', $r, $g, $b);
            }
        }
        $hexData = ob_get_clean();

        if ($hexData === false) {
            throw new RuntimeException('Failed to capture hex data from output buffer.');
        }

        unset($logo);
        unset($resizedLogo);

        $psBlock = <<<PS
% MERGED LOGO START
gsave
{$posX} {$posY} translate
{$targetW} {$targetH} scale
{$targetW} {$targetH} 8
[{$targetW} 0 0 -{$targetH} 0 {$targetH}]
{currentfile 3 {$targetW} mul string readhexstring pop} false 3
colorimage
{$hexData}
grestore
% MERGED LOGO END
PS;

        $lastShowpage = strrpos($this->epsContent, 'showpage');
        if ($lastShowpage !== false) {
            return substr_replace($this->epsContent, $psBlock."\nshowpage", $lastShowpage, strlen('showpage'));
        }

        return $this->epsContent."\n".$psBlock;
    }
}
