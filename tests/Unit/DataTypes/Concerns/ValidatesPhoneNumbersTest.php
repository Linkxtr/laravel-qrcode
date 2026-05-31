<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\Concerns\ValidatesPhoneNumbers;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidPhoneNumberArgumentException;

covers(ValidatesPhoneNumbers::class);

$traitTester = new class
{
    use ValidatesPhoneNumbers;

    public function test(string $phone): string
    {
        return $this->validatePhoneNumber($phone);
    }
};

test('it successfully strips visual formatting characters', function () use ($traitTester): void {
    $result = $traitTester->test('+1 (555) 123-4567.89');
    expect($result)->toBe('+1555123456789');
});

test('it mathematically enforces the 15-digit maximum boundary', function () use ($traitTester): void {
    expect($traitTester->test('+123456789012345'))->toBe('+123456789012345');

    expect(fn (): string => $traitTester->test('+1234567890123456'))
        ->toThrow(InvalidPhoneNumberArgumentException::class);
});

test('it mathematically enforces the 1-digit minimum boundary', function () use ($traitTester): void {
    expect($traitTester->test('1'))->toBe('1');
    expect($traitTester->test('+1'))->toBe('+1');

    expect(fn (): string => $traitTester->test(''))
        ->toThrow(InvalidPhoneNumberArgumentException::class);

    expect(fn (): string => $traitTester->test('() - '))
        ->toThrow(InvalidPhoneNumberArgumentException::class);
});

test('it strictly enforces the plus sign positioning', function () use ($traitTester): void {
    expect($traitTester->test('+123'))->toBe('+123');

    expect(fn (): string => $traitTester->test('++123'))
        ->toThrow(InvalidPhoneNumberArgumentException::class);

    expect(fn (): string => $traitTester->test('12+3'))
        ->toThrow(InvalidPhoneNumberArgumentException::class);
});

test('it throws exception if string contains letters or symbols', function () use ($traitTester): void {
    expect(fn (): string => $traitTester->test('invalid-phone-string'))
        ->toThrow(InvalidPhoneNumberArgumentException::class, 'Phone number contains invalid characters');

    expect(fn (): string => $traitTester->test('abc1234567'))
        ->toThrow(InvalidPhoneNumberArgumentException::class, 'Phone number contains invalid characters');

    expect(fn (): string => $traitTester->test('15551234567 ext 123'))
        ->toThrow(InvalidPhoneNumberArgumentException::class, 'Phone number contains invalid characters');

    expect(fn (): string => $traitTester->test('+1-555-123-4567@'))
        ->toThrow(InvalidPhoneNumberArgumentException::class, 'Phone number contains invalid characters');
});
