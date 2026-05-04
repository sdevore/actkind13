<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ActType: string implements HasColor, HasIcon, HasLabel
{
    case DID = 'did';
    case SAW = 'saw';
    case RECEIVED = 'received';

    public function getLabel(): string
    {
        return match ($this) {
            self::DID => 'Did this Act of Kindness',
            self::SAW => 'Saw this Act of Kindness',
            self::RECEIVED => 'Received this Act of Kindness',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::DID => 'fas-hand-holding-box',
            self::SAW => 'fas-hand-holding-hand',
            self::RECEIVED => 'fas-hands-holding-diamond',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DID => Color::Green,
            self::SAW => Color::Emerald,
            self::RECEIVED => Color::Teal,
        };
    }

    public function getBackgroundColor(): string|array|null
    {
        return match ($this) {
            self::DID => 'bg-green-500 dark:bg-green-800/10',
            self::SAW => 'bg-emerald-500 dark:bg-emerald-800/10',
            self::RECEIVED => 'bg-teal-500 dark:bg-teal-800/10',
        };
    }

    public function getLightBackgroundColor(): string|array|null
    {
        return match ($this) {
            self::DID => 'bg-green-500/40 dark:bg-green-800/60',
            self::SAW => 'bg-emerald-500/40 dark:bg-emerald-800/60',
            self::RECEIVED => 'bg-teal-500/40 dark:bg-teal-800/60',

        };
    }

    public function getBorderColor(): string|array|null
    {
        return match ($this) {
            self::DID => 'border-green-700',
            self::SAW => 'border-emerald-700',
            self::RECEIVED => 'border-teal-700',

        };
    }

    public function getTextColor(): string|array|null
    {
        return match ($this) {
            self::DID => 'text-green-800 dark:text-green-400',
            self::SAW => 'text-emerald-800 dark:text-emerald-400',
            self::RECEIVED => 'text-teal-800 dark:text-teal-400',

        };
    }

    public function getButtonColor(): string|array|null
    {
        return match ($this) {
            self::DID => 'bg-green-500 hover:bg-green-600 boder border-green-700 text-white',
            self::SAW => 'bg-emerald-500 hover:bg-emerald-600 border border-emerald-700 text-white',
            self::RECEIVED => 'bg-teal-500 hover:bg-teal-600 border border-teal-700 text-white',
        };
    }

    public function getBadge(): string|array|null
    {
        return match ($this) {
            self::DID => '<span class="inline-flex items-center rounded-md bg-green-50 dark:bg-green-800/20 px-2 py-1 text-xs font-medium text-green-600 dark:text-green-300 ring-1 ring-inset ring-green-500/10 dark:ring-green-500/80 mx-1">did</span>',
            self::SAW => '<span class="inline-flex items-center rounded-md bg-emerald-50 dark:bg-emerald-800/20 px-2 py-1 text-xs font-medium text-emerald-600 dark:text-emerald-300 ring-1 ring-inset ring-emerald-500/10 dark:ring-emerald-500/80 mx-1">saw</span>',
            self::RECEIVED => '<span class="inline-flex items-center rounded-md bg-teal-50 dark:bg-teal-800/20 px-2 py-1 text-xs font-medium text-teal-600 dark:text-teal-300 ring-1 ring-inset ring-teal-500/10 dark:ring-teal-500/80 mx-1">received</span>',
        };
    }
}
