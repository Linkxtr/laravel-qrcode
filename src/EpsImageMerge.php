<?php

namespace Linkxtr\QrCode;

use InvalidArgumentException;

final class EpsImageMerge
{
    public function __construct(
        protected string $epsContent,
        protected string $mergeImageContent,
        protected float $percentage
    ) {}

    public function merge(): string
    {
        if ($this->percentage <= 0 || $this->percentage > 1) {
            throw new InvalidArgumentException('$percentage must be between 0 and 1');
        }

        if (! preg_match('/%%BoundingBox:\s*(-?\d+)\s+(-?\d+)\s+(-?\d+)\s+(-?\d+)/', $this->epsContent, $matches)) {
            throw new InvalidArgumentException('Could not determine EPS dimensions (Missing %%BoundingBox).');
        }

        $urx = (int) $matches[3];
        $ury = (int) $matches[4];

        $qrWidth = $urx;
        $qrHeight = $ury;

        $logo = @imagecreatefromstring($this->mergeImageContent);

        if (! $logo) {
            throw new InvalidArgumentException('Invalid merge image provided.');
        }

        $logoW = imagesx($logo);
        $logoH = imagesy($logo);
        $ratio = $logoW / $logoH;

        /** @var int<1, max> $targetW */
        $targetW = (int) ($qrWidth * $this->percentage);
        /** @var int<1, max> $targetH */
        $targetH = (int) ($targetW / $ratio);

        $posX = (int) (($qrWidth - $targetW) / 2);
        $posY = (int) (($qrHeight - $targetH) / 2);

        $resizedLogo = imagecreatetruecolor($targetW, $targetH);

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

        if (str_contains($this->epsContent, 'showpage')) {
            return str_replace('showpage', $psBlock."\nshowpage", $this->epsContent);
        }

        return $this->epsContent."\n".$psBlock;
    }
}
