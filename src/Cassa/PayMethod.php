<?php

declare(strict_types=1);

namespace Salsiccia\Cassa;

// T26: unica fonte per i metodi di pagamento (sostituisce le whitelist
// copy-paste in mostraSchermataStampa/mostraLayoutResto + fiscale.inc).
enum PayMethod: string
{
    case Contanti = 'contanti';
    case Carta = 'carta';
}
