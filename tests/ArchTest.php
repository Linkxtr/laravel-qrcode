<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Component;
use Linkxtr\QrCode\Contracts\ColorInterface;
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Contracts\MergerInterface;
use Linkxtr\QrCode\Contracts\QrCodeExceptionInterface;
use Linkxtr\QrCode\Enums\Concerns\EnumHelper;
use Linkxtr\QrCode\Exceptions\Concerns\HasHelperMessage;
use Linkxtr\QrCode\QrCodeServiceProvider;

arch()->preset()->php();
arch()->preset()->security()->ignoring('sha1');

arch()
    ->expect('Linkxtr\QrCode')->toUseStrictTypes()
    ->not->toUse(['die', 'dd', 'dump', 'ray']);

arch()
    ->expect('Linkxtr\QrCode\Components')
    ->toBeClasses()
    ->toBeFinal()
    ->toHaveSuffix('Component')
    ->toExtend(Component::class);

arch()
    ->expect('Linkxtr\QrCode\Console\Commands')
    ->toBeClasses()
    ->toBeFinal()
    ->toHaveSuffix('Command')
    ->toExtend(Command::class)
    ->toHaveMethod('handle');

arch()
    ->expect('Linkxtr\QrCode\Contracts')
    ->toBeInterfaces()
    ->toHaveSuffix('Interface');

arch()
    ->expect('Linkxtr\QrCode\DataTypes')
    ->toBeClasses()
    ->ignoring('Linkxtr\QrCode\DataTypes\Concerns');

arch()
    ->expect('Linkxtr\QrCode\DataTypes\Concerns')
    ->toBeTraits()
    ->toOnlyBeUsedIn('Linkxtr\QrCode\DataTypes');

arch()
    ->expect('Linkxtr\QrCode\DataTypes')
    ->classes()
    ->toBeFinal()
    ->classes()
    ->toImplement(DataTypeInterface::class);

arch()
    ->expect('Linkxtr\QrCode\DTOs')
    ->toBeClasses()
    ->toBeFinal();

arch()
    ->expect('Linkxtr\QrCode\Enums')
    ->toBeEnums()
    ->ignoring('Linkxtr\QrCode\Enums\Concerns');

arch()
    ->expect('Linkxtr\QrCode\Enums\Concerns')
    ->toBeTraits()
    ->toOnlyBeUsedIn('Linkxtr\QrCode\Enums');

arch()
    ->expect('Linkxtr\QrCode\Enums')
    ->enums()
    ->toUseTrait(EnumHelper::class);

arch()
    ->expect('Linkxtr\QrCode\Exceptions')
    ->toBeClasses()
    ->ignoring('Linkxtr\QrCode\Exceptions\Concerns');

arch()
    ->expect('Linkxtr\QrCode\Exceptions\Concerns')
    ->toBeTraits()
    ->toOnlyBeUsedIn('Linkxtr\QrCode\Exceptions');

arch()
    ->expect('Linkxtr\QrCode\Exceptions')
    ->classes()
    ->toExtend(Exception::class)
    ->classes()
    ->toImplement(QrCodeExceptionInterface::class)
    ->classes()
    ->toUseTrait(HasHelperMessage::class)
    ->classes()
    ->toHaveSuffix('Exception');

arch()
    ->expect('Linkxtr\QrCode\Facades')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(Facade::class);

arch()
    ->expect('Linkxtr\QrCode\Mergers')
    ->toBeClasses()
    ->toBeFinal()
    ->toHaveSuffix('Merger')
    ->toImplement(MergerInterface::class);

arch()
    ->expect('Linkxtr\QrCode\Renderers')
    ->toBeClasses()
    ->toBeReadonly()
    ->toHaveSuffix('Renderer')
    ->toBeFinal();

arch()
    ->expect('Linkxtr\QrCode\Support')
    ->toBeClasses()
    ->toBeFinal();

arch()
    ->expect('Linkxtr\QrCode\ValueObjects\Colors')
    ->toBeClasses()
    ->toImplement(ColorInterface::class)
    ->toBeReadonly()
    ->toBeFinal();

arch()
    ->expect(QrCodeServiceProvider::class)
    ->toBeClasses()
    ->toBeFinal()
    ->toHaveSuffix('ServiceProvider')
    ->toExtend(ServiceProvider::class)
    ->toHaveMethod('boot')
    ->toHaveMethod('register');
