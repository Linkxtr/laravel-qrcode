<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions\DataTypes;

use Linkxtr\QrCode\Exceptions\InvalidDataTypeArgumentException;

/**
 * Fallback exception for data type argument validation.
 * This exception is used when a more specific exception class is not defined.
 * It still provides meaningful error messages for common validation failures.
 */
final class GenericInvalidDataTypeArgumentException extends InvalidDataTypeArgumentException {}
