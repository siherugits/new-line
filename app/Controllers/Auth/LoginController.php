<?php

namespace App\Controllers\Auth;

use App\Libraries\CaptchaSvg;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Controllers\LoginController as ShieldLoginController;

class LoginController extends ShieldLoginController
{
    /**
     * Verify the captcha before delegating to Shield's login logic.
     */
    public function loginAction(): RedirectResponse
    {
        if (! (new CaptchaSvg())->verify($this->request->getPost('captcha'))) {
            return redirect()->route('login')
                ->withInput()
                ->with('error', lang('Auth.captchaInvalid'));
        }

        return parent::loginAction();
    }
}
