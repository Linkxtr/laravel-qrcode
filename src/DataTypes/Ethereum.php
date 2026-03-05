<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;

final class Ethereum implements DataTypeInterface
{
    private string $prefix = 'ethereum:';

    private string $address;

    private ?string $amount = null;

    public function __toString(): string
    {
        return $this->buildEthereumString();
    }

    /**
     * @param  list<mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        $this->setProperties($arguments);
    }

    /**
     * @param  list<mixed>  $arguments
     */
    private function setProperties(array $arguments): void
    {
        if (count($arguments) < 1) {
            throw new InvalidArgumentException('Ethereum address is required.');
        }

        if (! is_string($arguments[0])) {
            throw new InvalidArgumentException('Ethereum address must be a string.');
        }

        $this->address = $arguments[0];

        if (isset($arguments[1])) {
            if (! is_numeric($arguments[1])) {
                throw new InvalidArgumentException('Ethereum amount must be a numeric value.');
            }

            if ((float) $arguments[1] < 0) {
                throw new InvalidArgumentException('Ethereum amount must be non-negative.');
            }

            $this->amount = (string) $arguments[1];
        }
    }

    private function buildEthereumString(): string
    {
        $params = [
            'value' => $this->amount,
        ];

        $params = array_filter($params, fn (?string $value): bool => $value !== null);

        $queryString = $params === [] ? '' : '?'.http_build_query($params);

        return $this->prefix.$this->address.$queryString;
    }
}
