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

        $toInt = fn (string $value): int => (int) $value;

        $llx = $toInt($matches[1]);
        $lly = $toInt($matches[2]);
        $urx = $toInt($matches[3]);
        $ury = $toInt($matches[4]);

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

        $boxW = $qrWidth * $this->percentage;
        $boxH = $qrHeight * $this->percentage;

        $scale = min($boxW / $logoW, $boxH / $logoH);

        $targetW = max(1, (int) ($logoW * $scale));
        $targetH = max(1, (int) ($logoH * $scale));

        $posX = (int) (($qrWidth - $targetW) / 2) + $llx;
        $posY = (int) (($qrHeight - $targetH) / 2) + $lly;

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

        $rawBuffer = '';

        for ($y = 0; $y < $targetH; ++$y) {
            for ($x = 0; $x < $targetW; ++$x) {
                $rgb = imagecolorat($resizedLogo, $x, $y);
                $rawBuffer .= chr(($rgb >> 16) & 0xFF).chr(($rgb >> 8) & 0xFF).chr($rgb & 0xFF);
            }
        }

        $hexData = trim(chunk_split(bin2hex($rawBuffer), 72, "\n"));

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
