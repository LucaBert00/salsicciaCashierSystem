# CONTEXT.md — SalsicciaStagisti (cassa touch kiosk)

Glossario dei termini locked dalla mappa responsive #69. Solo linguaggio di dominio, nessun dettaglio implementativo.

## Legge di scala

La regola che fa crescere bottoni e testo con la viewport tenendo la cassa 1280x1024 pixel-identica.
Decisa in [R6 Decide](https://github.com/LucaBert00/SalsicciaStagisti/issues/75): clamp fluido con
crescita oltre vmin=1024, mai tiers a breakpoint né container query.

## Pendenza dedicata

La crescita oltre la cassa usa una pendenza propria a partire da vmin=1024, così alla misura di
cassa il valore è esatto per costruzione e su PC/tablet grandi cresce in modo visibile
(la vecchia pendenza lineare dava solo ~+5% su PC 1920x1080: troppo poco).

## Cap PC

Il tetto massimo della crescita: 1,35x il valore di cassa. Oltre non si cresce, anti-esplosione
su schermi enormi.

## Floor touch

Il minimo assoluto dei controlli toccabili su tablet: 44px. Mai sotto, anche se la scala
direbbe altrimenti.

## Perimetro di scala

Cosa scala: bottoni (.bottone, nav, footer, tastierino) e font (h1, prezzi, carrello).
Cosa NON scala mai: sfondi cover, banconote (aspect-ratio), monete (cerchio), split 67/33 main/aside.

## Scontrino

Il documento fiscale emesso via registratore in fiera.
Deciso in [G5a Decide](https://github.com/LucaBert00/SalsicciaStagisti/issues/88).

## Ricevuta

L'etichetta non fiscale che esce dalla stampante etichette, mai sostituto dello scontrino.

## Fallback

La cassa che prosegue la vendita quando il fiscale non risponde, mai un blocco.

## Coda

I documenti fiscali in attesa di riemissione quando il fallback è scattato.
