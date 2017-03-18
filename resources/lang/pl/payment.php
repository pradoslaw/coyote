<?php

return [
    'validation' => 'Płatność nie została pobrana. Wprowadzone dane są nieprawidłowe. Prosimy o kontakt pod adresem ' . config('mail.from.address'),
    'declined' => 'Płatność została odrzucona. Proszę sprawdzić, czy podany numer karty oraz CVC, są prawidłowe.',
    'forbidden' => 'Płatność nie została pobrana. Prosimy o kontakt pod adresem ' . config('mail.from.address'),
    'internal_server' => 'Płatność nie została pobrana. Prosimy o kontakt pod adresem ' . config('mail.from.address'),
    'service_unavailable' => 'Płatność nie została pobrana. Usługa tymczasowo niedostępna. Prosimy spróbować za kilka minut.',
    'unauthorized' => 'Płatność nie została pobrana. Prosimy o kontakt pod adresem ' . config('mail.from.address'),
    'unhandled' => 'Wystąpił błąd. Płatność nie została pobrana. Prosimy o kontakt pod adresem ' . config('mail.from.address'),

    'success' => 'Dziękujemy! Płatność została zaksięgowana. Za chwilę zaczniemy promowanie ogłoszenia.'
];
