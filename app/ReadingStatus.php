<?php

namespace App;

enum ReadingStatus: string
{
    case Pending = 'pending';
    case Reading = 'reading';
    case Completed = 'completed';
    case Abandoned = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Reading => 'Leyendo',
            self::Completed => 'Terminado',
            self::Abandoned => 'Abandonado',
        };
    }
}
