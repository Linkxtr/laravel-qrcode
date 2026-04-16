<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\Concerns\ValidatesPhoneNumbers;

// Create an anonymous class to expose the protected trait method for testing
$traitTester = new class
{
    use ValidatesPhoneNumbers;

    public function test(string $phone): string
    {
        return $this->validatePhoneNumber($phone);
    }
};

test('it successfully strips visual formatting characters', function () use ($traitTester) {
    $result = $traitTester->test('+1 (555) 123-4567.89');
    expect($result)->toBe('+1555123456789');
});

test('it mathematically enforces the 15-digit maximum boundary to kill {1,16} mutants', function () use ($traitTester) {
    expect($traitTester->test('+123456789012345'))->toBe('+123456789012345');

    expect(fn () => $traitTester->test('+1234567890123456'))
        ->toThrow(InvalidArgumentException::class);
});

test('it mathematically enforces the 1-digit minimum boundary to kill {2,15} and {0,15} mutants', function () use ($traitTester) {
    expect($traitTester->test('1'))->toBe('1');
    expect($traitTester->test('+1'))->toBe('+1');

    expect(fn () => $traitTester->test(''))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => $traitTester->test('() - '))
        ->toThrow(InvalidArgumentException::class);
});

test('it strictly enforces the plus sign positioning to kill prefix mutants', function () use ($traitTester) {
    expect($traitTester->test('+123'))->toBe('+123');

    expect(fn () => $traitTester->test('++123'))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => $traitTester->test('12+3'))
        ->toThrow(InvalidArgumentException::class);
});

test('it throws exception if string contains only letters', function () use ($traitTester) {
    expect(fn () => $traitTester->test('invalid-phone-string'))
        ->toThrow(InvalidArgumentException::class);
});
