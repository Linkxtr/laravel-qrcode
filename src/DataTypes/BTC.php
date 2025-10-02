<?php

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\DataTypes\DataTypeInterface;

class BTC implements DataTypeInterface
{
    protected string $prefix = 'bitcoin:';

    protected string $address;

    protected float $amount;

    protected ?string $label = null;

    protected ?string $message = null;

    protected ?string $returnAddress = null;

    public function create(array $arguments): void
    {
        $this->setProperties($arguments);
    }

    public function __toString(): string
    {
        return $this->buildBitCoinString();
    }

    protected function setProperties(array $arguments)
    {
        if (isset($arguments[0])) {
            $this->address = $arguments[0];
        }

        if (isset($arguments[1])) {
            $this->amount = $arguments[1];
        }

        if (isset($arguments[2])) {
            $this->setOptions($arguments[2]);
        }
    }

    protected function setOptions(array $options)
    {
        if (isset($options['label'])) {
            $this->label = $options['label'];
        }

        if (isset($options['message'])) {
            $this->message = $options['message'];
        }

        if (isset($options['returnAddress'])) {
            $this->returnAddress = $options['returnAddress'];
        }
    }

    protected function buildBitCoinString(): string
    {
        $query = http_build_query([
            'amount'    => $this->amount,
            'label'     => $this->label,
            'message'  => $this->message,
            'r'         => $this->returnAddress,
        ]);

        $btc = $this->prefix.$this->address.'?'.$query;

        return $btc;
    }
}