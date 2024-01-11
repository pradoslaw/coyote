<?php
namespace Coyote\Domain\Seo\Schema;

class Organization implements Thing
{
    public function schema(): array
    {
        return [
            '@context' => 'http://schema.org',
            '@type'    => 'Organization',
            'name'     => 'Makana Sp. z o.o.',
            'email'    => 'support@4programmers.net',
            'logo'     => 'https://www.4programmers.net/img/apple-touch.png',
            'sameAs'   => [
                'https://www.facebook.com/4programmers.net/',
                'https://www.linkedin.com/company/4programmers/about/',
                'https://github.com/pradoslaw/coyote',
            ],
        ];
    }
}
