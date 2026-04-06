<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support;

use BadMethodCallException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;

final class DataTypeResolver
{
    /**
     * Resolve a data type method call into its string payload.
     *
     * @param  array<mixed>  $arguments
     *
     * @throws BadMethodCallException
     */
    public static function resolve(string $method, array $arguments): string
    {
        $className = self::formatClass($method);

        if (! class_exists($className)) {
            throw new BadMethodCallException(sprintf(
                'Method "%s" does not exist on the QrCode Generator. It is not a registered macro or a valid Data Type.',
                $method
            ));
        }

        $dataType = new $className;

        if ($dataType::class !== $className) {
            throw new BadMethodCallException(sprintf('Class "%s" must implement DataTypeInterface.', $className));
        }

        if (! $dataType instanceof DataTypeInterface) {
            throw new BadMethodCallException(sprintf('Class "%s" must implement DataTypeInterface.', $className));
        }

        $dataType->create($arguments);

        return (string) $dataType;
    }

    public static function formatClass(string $method): string
    {
        return 'Linkxtr\\QrCode\\DataTypes\\'.$method;
    }
}
