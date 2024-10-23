<?php
namespace V3;

class Controller
{
    public function setupEndpoints(): void
    {
        Peripheral::addGetRoute('/v3/register', function () {
            if (Peripheral::httpRequestQuery('secret') !== 'v3') {
                abort(404);
            }

            return Peripheral::renderTwig('view', [
                'stylesheetUrl' => Peripheral::resourceUrl('css/v3.css'),
                'logoUrl'       => '/img/v3/logo.svg',
            ]);
        });

        Peripheral::addPostRoute('/v3/createAccount', function () {
            $requestModel = new RequestModel(
                Peripheral::httpRequestField('registerLogin'),
                Peripheral::httpRequestField('registerPassword'),
                Peripheral::httpRequestField('registerEmail'),
            );

            return Peripheral::renderTwig('view', [
                'stylesheetUrl' => Peripheral::resourceUrl('css/v3.css'),
            ]);
        });
    }
}
