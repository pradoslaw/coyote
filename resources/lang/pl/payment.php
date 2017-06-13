<?php

return [
    'forbidden' => 'Płatność nie została pobrana. Prosimy o kontakt pod adresem ' . config('mail.from.address'),
    'internal_server' => 'Płatność nie została pobrana. Prosimy o kontakt pod adresem ' . config('mail.from.address'),
    'timeout' => 'Płatność nie została pobrana. Usługa tymczasowo niedostępna. Prosimy spróbować za kilka minut.',
    'server_error' => 'Płatność nie została pobrana. Prosimy o kontakt pod adresem ' . config('mail.from.address'),
    'unhandled' => 'Wystąpił błąd. Płatność nie została pobrana. Prosimy o kontakt pod adresem ' . config('mail.from.address'),
    'validation' => 'Płatność nie została pobrana. Prosimy o sprawdzenie poprawności wpisanych danych oraz sprawdzenie czy na karcie dostępne są wymagane środki. Jeżeli tak, to prosimy o kontakt pod adresem ' . config('mail.from.address'),

    'success' => 'Dziękujemy! Płatność została zaksięgowana. Za chwilę zaczniemy promowanie ogłoszenia.'
];
