<?php
namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrivacyController extends Controller
{
    public function set(Request $request): Response
    {
        $request->validate([
            'analytics'   => 'required',
            'advertising' => 'required',
        ]);
        if ($this->auth) {
            $this->auth->gdpr = \json_encode([
                'analytics'   => (bool)$request->get('analytics'),
                'advertising' => (bool)$request->get('advertising'),
            ]);
            $this->auth->save();
            return response(status:200);
        }
        return response(status:403);
    }

    public function reset(): RedirectResponse
    {
        if ($this->auth) {
            $this->auth->gdpr = null;
            $this->auth->save();
        }
        return redirect('/');
    }
}
