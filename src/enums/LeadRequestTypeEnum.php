<?php

namespace logisticdesign\formieactioncrm\enums;

enum LeadRequestTypeEnum: string
{
    case ASS = 'ASS'; // Servizi assistenza SERV
    case TDR = 'TDR'; // Test-drive SALES
    case PREVS = 'PREVS'; // Preventivo nuovo SALES
    case PARTS = 'PARTS'; // Info ricambi SERV
    case PREVU = 'PREVU'; // Preventivo usato SALES
    case ASSB1 = 'ASSB1'; // Prenotazione tagliando SERV
    case ASSB2 = 'ASSB2'; // Prenotazione intervento SERV
    case FUPTD = 'FUPTD'; // Follow-up Test-Drive SALES
    case PREVR = 'PREVR'; // Preventivo Noleggio SALES
    case TCM = 'TCM'; // Rinnovo SALES
    case PREVA = 'PREVA'; // Preventivo service SERV
    case RCONT = 'RCONT'; // Richiesta di contatto (Tutti)
    case SERV = 'SERV'; // Servizi (Tutti)
    case ALERT = 'ALERT'; // Alert malfunzionamento SERV
    case CAMP = 'CAMP'; // Campagna (Tutti)
    case SRADD = 'SRADD'; // Servizi aggiuntivi (Tutti)
    case INFO = 'INFO'; // Richiesta informazioni (Tutti)
    case BROCH = 'BROCH'; // Brochure (Tutti)
    case KMEUP = 'KMEUP'; // Keep me updated (Tutti)
}
