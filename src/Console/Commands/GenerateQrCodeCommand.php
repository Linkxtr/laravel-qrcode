<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Facades\QrCode;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

final class GenerateQrCodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr:generate 
                            {data? : The data/payload to encode in the QR code}
                            {--O|output= : The file path to save the QR code (e.g., public/qr.svg)}
                            {--F|format= : The output format (svg, png, webp, eps)}
                            {--S|size= : The size of the QR code in pixels}
                            {--C|color= : Foreground color as RGB comma-separated (e.g., 0,0,0)}
                            {--B|backgroundColor= : Background color as RGB comma-separated (e.g., 255,255,255)}
                            {--E|errorCorrection= : Error correction level (L, M, Q, H)}
                            {--M|margin= : The margin around the QR code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a QR code directly from the command line';

    public function handle(): int
    {
        $dataArg = $this->getStringArgument('data');
        $isInteractive = $dataArg === null;

        $data = $dataArg;
        if ($isInteractive) {
            $data = text(
                label: 'What data/payload should be encoded in the QR code?',
                placeholder: 'https://linkxtr.com',
                required: true
            );
        }

        $data = (string) $data;

        $outputOpt = $this->getStringOption('output');
        $output = $outputOpt ?? ($isInteractive ? text(
            label: 'Where should the QR code be saved?',
            placeholder: 'e.g., public/qr.svg (leave empty to print to console)'
        ) : null);

        $output = is_string($output) && $output !== '' ? $output : null;

        $formatOpt = $this->getStringOption('format');
        $format = $formatOpt ?? ($isInteractive && $output !== null ? select(
            label: 'What output format do you want?',
            options: ['svg', 'png', 'webp', 'eps'],
            default: 'svg'
        ) : 'svg');
        $format = (string) $format;

        // Advanced Options Defaults
        $size = $this->getStringOption('size') ?? '400';
        $color = $this->getStringOption('color') ?? '0,0,0';
        $backgroundColor = $this->getStringOption('backgroundColor') ?? '255,255,255';
        $margin = $this->getStringOption('margin') ?? '4';
        $errorCorrection = $this->getStringOption('errorCorrection') ?? 'M';

        $hasPassedAdvancedOptions = $this->option('size') || $this->option('color') || $this->option('backgroundColor') || $this->option('margin') || $this->option('errorCorrection');

        if ($isInteractive && ! $hasPassedAdvancedOptions && confirm(label: 'Do you want to configure advanced options (size, colors, margin)?', default: false)) {
            $size = text(
                label: 'Size in pixels',
                default: '400',
                validate: fn (string $value): ?string => is_numeric($value) && (int) $value > 0 ? null : 'Size must be a positive integer.'
            );

            $color = text(
                label: 'Foreground color (RGB or RGBA comma-separated)',
                default: '0,0,0',
                validate: fn (string $value): ?string => $this->validateColorString($value)
            );

            $backgroundColor = text(
                label: 'Background color (RGB or RGBA comma-separated)',
                default: '255,255,255',
                validate: fn (string $value): ?string => $this->validateColorString($value)
            );

            $margin = text(
                label: 'Margin',
                default: '4',
                validate: fn (string $value): ?string => is_numeric($value) && (int) $value >= 0 ? null : 'Margin must be a positive integer or zero.'
            );

            $errorCorrection = select(
                label: 'Error correction level',
                options: ['L', 'M', 'Q', 'H'],
                default: 'M'
            );
        }

        if (! is_numeric($size) || (int) $size <= 0) {
            error('Size must be a positive integer.');

            return self::FAILURE;
        }

        if (! is_numeric($margin) || (int) $margin < 0) {
            error('Margin must be a positive integer or zero.');

            return self::FAILURE;
        }

        $errorCorrectionLevel = ErrorCorrectionLevel::tryFrom(strtoupper((string) $errorCorrection));

        if ($errorCorrectionLevel === null) {
            error('Invalid error correction level. Please use L, M, Q, or H.');

            return self::FAILURE;
        }

        $rgb = $this->parseColor($color);
        $bgRgb = $this->parseColor($backgroundColor);

        if ($rgb === [] || $bgRgb === []) {
            return self::FAILURE;
        }

        try {
            $generator = QrCode::size((int) $size)
                ->margin((int) $margin)
                ->format($format)
                ->errorCorrection($errorCorrectionLevel)
                ->color(...$rgb)
                ->backgroundColor(...$bgRgb);

            if ($output !== null) {
                $generator->generate($data, $output);
                info('✨ QR Code successfully generated and saved to: '.$output);
            } else {
                $result = $generator->generate($data);
                $this->line((string) $result);
            }

            return self::SUCCESS;

        } catch (Exception $exception) {
            error('Failed to generate QR Code: '.$exception->getMessage());

            return self::FAILURE;
        }
    }

    private function getStringArgument(string $key): ?string
    {
        $value = $this->argument($key);

        return is_string($value) ? $value : null;
    }

    private function getStringOption(string $key): ?string
    {
        $value = $this->option($key);

        return is_string($value) ? $value : null;
    }

    private function validateColorString(string $color): ?string
    {
        $rgb = explode(',', $color);
        if (count($rgb) !== 3 && count($rgb) !== 4) {
            return 'Invalid format. Please use RGB or RGBA comma-separated values (e.g., 255,0,0).';
        }

        foreach ($rgb as $index => $val) {
            $trimmed = trim($val);
            if (! is_numeric($trimmed)) {
                return 'All color values must be numeric.';
            }

            $val = (int) $trimmed;
            if ($index < 3 && ($val < 0 || $val > 255)) {
                return 'RGB values must be between 0 and 255.';
            }

            if ($index === 3 && ($val < 0 || $val > 100)) {
                return 'Alpha value must be between 0 and 100.';
            }
        }

        return null;
    }

    /**
     * @return array{0: int, 1: int, 2: int, 3: int|null}|array{}
     */
    private function parseColor(string $color): array
    {
        $validationError = $this->validateColorString($color);

        if ($validationError) {
            error($validationError);

            return [];
        }

        $rgb = explode(',', $color);

        return [
            0 => (int) trim($rgb[0]),
            1 => (int) trim($rgb[1]),
            2 => (int) trim($rgb[2]),
            3 => isset($rgb[3]) ? (int) trim($rgb[3]) : null,
        ];
    }
}
