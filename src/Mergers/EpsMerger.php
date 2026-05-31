<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Mergers;

use Linkxtr\QrCode\Contracts\MergerInterface;
use Linkxtr\QrCode\Exceptions\ImageMergeException;

final readonly class EpsMerger implements MergerInterface
{
    public function __construct(
        private string $epsContent,
        private string $mergeImageContent,
        private float $percentage
    ) {
        if ($this->percentage <= 0 || $this->percentage >= 1) {
            throw ImageMergeException::invalidPercentage();
        }
    }

    public function merge(): string
    {
        if (! preg_match('/%%BoundingBox:\s*(-?\d+)\s+(-?\d+)\s+(-?\d+)\s+(-?\d+)/', $this->epsContent, $matches)) {
            throw ImageMergeException::couldNotDetermineEpsDimensions();
        }

        $llx = (int) $matches[1]; // @pest-mutate-ignore
        $lly = (int) $matches[2]; // @pest-mutate-ignore
        $urx = (int) $matches[3]; // @pest-mutate-ignore
        $ury = (int) $matches[4]; // @pest-mutate-ignore

        $qrWidth = $urx - $llx;
        $qrHeight = $ury - $lly;

        $logo = @imagecreatefromstring($this->mergeImageContent);

        if (! $logo) {
            $error = error_get_last();
            $message = $error !== null ? $error['message'] : 'Unknown GD error';

            throw ImageMergeException::invalidImage($message);
        }

        $logoW = imagesx($logo);
        $logoH = imagesy($logo);

        $ratio = $logoW / $logoH;

        $targetW = max(1, (int) ($qrWidth * $this->percentage)); // @pest-mutate-ignore
        $targetH = max(1, (int) ($targetW / $ratio)); // @pest-mutate-ignore

        if ($targetH > $qrHeight * $this->percentage) {
            $targetH = max(1, (int) ($qrHeight * $this->percentage)); // @pest-mutate-ignore
            $targetW = max(1, (int) ($targetH * $ratio)); // @pest-mutate-ignore
        }

        $posX = (int) (($qrWidth - $targetW) / 2) + $llx; // @pest-mutate-ignore
        $posY = (int) (($qrHeight - $targetH) / 2) + $lly; // @pest-mutate-ignore

        $resizedLogo = imagecreatetruecolor($targetW, $targetH);

        if (! $resizedLogo) {
            throw ImageMergeException::failedToCreateResizedLogoCanvas();
        }

        $white = imagecolorallocate($resizedLogo, 255, 255, 255);

        if (! $white) {
            throw ImageMergeException::couldNotAllocateWhiteColor();
        }

        imagefill($resizedLogo, 0, 0, $white);

        imagecopyresampled(
            $resizedLogo, $logo,
            0, 0, 0, 0,
            $targetW, $targetH,
            $logoW, $logoH
        );

        ob_start();
        for ($y = 0; $y < $targetH; ++$y) {
            for ($x = 0; $x < $targetW; ++$x) {
                $rgb = imagecolorat($resizedLogo, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                printf('%02x%02x%02x', $r, $g, $b);
            }
        }

        $hexData = ob_get_clean();

        if ($hexData === false) {
            throw ImageMergeException::failedToCaptureHexDataFromOutputBuffer();
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
