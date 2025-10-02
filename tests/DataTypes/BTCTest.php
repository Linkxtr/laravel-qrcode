<?php

use Linkxtr\QrCode\DataTypes\BTC;

beforeEach(function () {
    $this->btc = new BTC();
});

it('should generate a valid BTC QR code', function () {
    $this->btc->create(['btcaddress', 0.0034]);
    expect(strval($this->btc))->toBe('bitcoin:btcaddress?amount=0.0034');
});

it('should generate a valid BTC QR code with label', function () {
    $this->btc->create(['btcaddress', 0.0034, ['label' => 'label']]);
    expect(strval($this->btc))->toBe('bitcoin:btcaddress?amount=0.0034&label=label');
});

it('should generate a valid BTC QR code with message', function () {
    $this->btc->create(['btcaddress', 0.0034, ['message' => 'message']]);
    expect(strval($this->btc))->toBe('bitcoin:btcaddress?amount=0.0034&message=message');
});

it('should generate a valid BTC QR code with label and message', function () {
    $this->btc->create(['btcaddress', 0.0034, ['label' => 'label', 'message' => 'message']]);
    expect(strval($this->btc))->toBe('bitcoin:btcaddress?amount=0.0034&label=label&message=message');
});

it('should generate a valid BTC QR code with label and message and return address', function () {
    $this->btc->create(['btcaddress', 0.0034, ['label' => 'label', 'message' => 'message', 'returnAddress' => 'https://www.returnaddress.com']]);
    expect(strval($this->btc))->toBe('bitcoin:btcaddress?amount=0.0034&label=label&message=message&r=' . urlencode('https://www.returnaddress.com'));
});
