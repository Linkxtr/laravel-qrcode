<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support;

use ArgumentCountError;
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\DataTypes\GenericInvalidDataTypeArgumentException;
use Linkxtr\QrCode\Exceptions\UnknownMethodException;
use TypeError;

final class DataTypeResolver
{
    /**
     * In-memory cache of dynamically resolved data types.
     *
     * @var array<string, string>|null
     */
    private static ?array $map = null;

    /**
     * Resolve a data type method call into its string payload.
     *
     * @param  array<mixed>  $arguments
     *
     * @throws UnknownMethodException
     */
    public static function resolve(string $method, array $arguments): string
    {
        $dataTypes = self::getMap();

        $normalizedMethod = strtolower($method);

        if (! array_key_exists($normalizedMethod, $dataTypes)) {
            throw UnknownMethodException::methodNotFound($method);
        }

        $className = $dataTypes[$normalizedMethod];

        try {
            if (count($arguments) === 1 && is_array($arguments[0])) {
                $arguments = $arguments[0];
            }

            $dataType = new $className(...$arguments);
        } catch (ArgumentCountError|TypeError $e) {
            if ($e instanceof ArgumentCountError) {
                throw GenericInvalidDataTypeArgumentException::missingArguments($e->getMessage());
            }

            throw GenericInvalidDataTypeArgumentException::invalidArgument($e->getMessage());
        }

        if (! $dataType instanceof DataTypeInterface) {
            throw UnknownMethodException::dataTypeNotImplemented($className);
        }

        return (string) $dataType;
    }

    /**
     * Get the map of data types.
     *
     * @return array<string, string>
     */
    private static function getMap(): array
    {
        if (self::$map !== null) {
            return self::$map;
        }

        $directory = __DIR__.'/../DataTypes';

        $dataTypes = [];

        foreach (scandir($directory) as $file) {
            if (str_ends_with($file, '.php')) {
                $className = basename($file, '.php');
                $fullClassName = 'Linkxtr\\QrCode\\DataTypes\\'.$className;
                $dataTypes[strtolower($className)] = $fullClassName;
            }
        }

        self::$map = $dataTypes;

        return $dataTypes;
    }
}
