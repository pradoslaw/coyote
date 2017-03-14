<?php

return [
    'validation' => 'Płatność nie została pobrana. Wprowadzone dane są nieprawidłowe. Prosimy o kontakt pod adresem ' . config('mail.from.address'),
    'declined' => 'Płatność została odrzucona. Proszę sprawdzić, czy podany numer karty oraz CVC, są prawidłowe.',

    'success' => 'Dziękujemy! Płatność została zaksięgowana. Ogłoszenie jest już promowane.'
];
