<?php

declare(strict_types=1);

namespace Salsiccia\Cassa;

// T26: unica fonte per i tipi ordine (sostituisce le whitelist copy-paste
// in OrderService::impostaTipo + mostraIndicatoreTipo/mostraOpzioni).
enum OrderType: string
{
    case Normale = 'nor';
    case Prevendita = 'pre';
    case Musicisti = 'mus';
    case Staff = 'stf';
    case Asporto = 'asp';

    public function label(): string
    {
        return match ($this) {
            self::Normale => 'NORMALE',
            self::Prevendita => 'PREVENDITA',
            self::Musicisti => 'MUSICISTI',
            self::Staff => 'STAFF',
            self::Asporto => 'ASPORTO',
        };
    }

    // Lettera indicatore nav; Normale non mostra nulla.
    public function lettera(): ?string
    {
        return match ($this) {
            self::Prevendita => 'P',
            self::Musicisti => 'M',
            self::Staff => 'S',
            self::Asporto => 'A',
            self::Normale => null,
        };
    }
}
