<?php

return [
    'unhandled' => 'Wystąpił błąd. Płatność nie została pobrana. Prosimy o kontakt pod adresem: ' . config('mail.from.address'),
    'validation' => 'Płatność nie została pobrana. Serwer płatności zwrócił błąd: ":message". Skontaktuj się z nami pod adresem: ' . config('mail.from.address'),

    'success' => 'Dziękujemy! Płatność została zaksięgowana. Za chwilę dostaniesz potwierdzenie na adres e-mail.',
    'pending' => 'Dziękujemy! W momencie zaksięgowania wpłaty, dostaniesz potwierdzenie na adres e-mail.'
];
