<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\InvalidMacroReturnTypeException;

covers(InvalidMacroReturnTypeException::class);

test('invalidType creates exception with correct error code and helper message', function (): void {
    $invalidMacroReturnTypeException = InvalidMacroReturnTypeException::invalidType('test', 'invalidType');

    expect($invalidMacroReturnTypeException)
        ->toBeInstanceOf(InvalidMacroReturnTypeException::class)
        ->and($invalidMacroReturnTypeException->getMessage())
        ->toBe('Macro "test" must return a string, Stringable, or QrCodeResult. invalidType returned.')
        ->and($invalidMacroReturnTypeException->getErrorCode())
        ->toBe('INVALID_MACRO_RETURN_TYPE')
        ->and($invalidMacroReturnTypeException->getHelperMessage())
        ->toBe('Ensure your registered macro in the AppServiceProvider returns a plain string payload or a fully generated QrCodeResult instance from $this->generate().');
});
