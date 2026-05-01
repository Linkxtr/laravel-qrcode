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

        $data = $dataArg ?? text(
            label: 'What data/payload should be encoded in the QR code?',
            placeholder: 'https://linkxtr.com',
            required: 'Data payload is required.'
        );

        $outputOpt = $this->getStringOption('output');
        $output = $outputOpt ?? ($isInteractive ? text(
            label: 'Where should the QR code be saved?',
            placeholder: 'e.g., public/qr.svg (leave empty to print to console)'
        ) : null);

        $output = is_string($output) && $output !== '' ? $output : null;

        $formatOpt = $this->getStringOption('format');

        /** @var string $format */
        $format = $formatOpt ?? ($isInteractive && $output !== null ? select(
            label: 'What output format do you want?',
            options: ['svg', 'png', 'webp', 'eps'],
            default: 'svg'
        ) : 'svg');

        $size = $this->getStringOption('size') ?? '400';
        $color = $this->getStringOption('color') ?? '0,0,0';
        $backgroundColor = $this->getStringOption('backgroundColor') ?? '255,255,255';
        $margin = $this->getStringOption('margin') ?? '4';
        $errorCorrection = $this->getStringOption('errorCorrection') ?? 'M';

        $hasPassedAdvancedOptions = $this->option('size') !== null // @pest-mutate-ignore
            || $this->option('color') !== null // @pest-mutate-ignore
            || $this->option('backgroundColor') !== null // @pest-mutate-ignore
            || $this->option('margin') !== null // @pest-mutate-ignore
            || $this->option('errorCorrection') !== null; // @pest-mutate-ignore

        if ($isInteractive && confirm(label: 'Do you want to configure advanced options (size, colors, margin)?', default: $hasPassedAdvancedOptions)) {
            if ($this->option('size') === null) {
                $size = text(label: 'Size in pixels', default: $size, validate: $this->validateSize(...));
            }

            if ($this->option('color') === null) {
                $color = text(label: 'Foreground color (RGB or RGBA comma-separated)', default: $color, validate: $this->validateColorString(...));
            }

            if ($this->option('backgroundColor') === null) {
                $backgroundColor = text(label: 'Background color (RGB or RGBA comma-separated)', default: $backgroundColor, validate: $this->validateColorString(...));
            }

            if ($this->option('margin') === null) {
                $margin = text(label: 'Margin', default: $margin, validate: $this->validateMargin(...));
            }

            if ($this->option('errorCorrection') === null) {
                /** @var string $errorCorrection */
                $errorCorrection = select(label: 'Error correction level', options: ['L', 'M', 'Q', 'H'], default: $errorCorrection);
            }
        }

        if ($sizeError = $this->validateSize($size)) {
            error($sizeError);

            return self::FAILURE;
        }

        if ($marginError = $this->validateMargin($margin)) {
            error($marginError);

            return self::FAILURE;
        }

        $errorCorrectionLevel = ErrorCorrectionLevel::tryFrom(strtoupper($errorCorrection));

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

                $this->line(sprintf('%s', $result));
            }

            return self::SUCCESS;

        } catch (Exception $exception) {
            error('Failed to generate QR Code: '.$exception->getMessage());

            return self::FAILURE;
        }
    }

    private function validateSize(string $value): ?string
    {
        $intVal = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($intVal) && $intVal > 0 ? null : 'Size must be a positive integer.';
    }

    private function validateMargin(string $value): ?string
    {
        $intVal = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($intVal) && $intVal >= 0 ? null : 'Margin must be a positive integer or zero.';
    }

    private function validateColorString(string $color): ?string
    {
        $rgb = explode(',', $color);
        if (count($rgb) !== 3 && count($rgb) !== 4) {
            return 'Invalid format. Please use RGB or RGBA comma-separated values (e.g., 255,0,0).';
        }

        foreach ($rgb as $index => $val) {
            $intVal = filter_var($val, FILTER_VALIDATE_INT);

            if (! is_int($intVal)) {
                return 'All color values must be numeric.';
            }

            if ($index < 3 && ($intVal < 0 || $intVal > 255)) {
                return 'RGB values must be between 0 and 255.';
            }

            if ($index === 3 && ($intVal < 0 || $intVal > 100)) {
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
            (int) $rgb[0],
            (int) $rgb[1],
            (int) $rgb[2],
            isset($rgb[3]) ? (int) $rgb[3] : null,
        ];
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
}
