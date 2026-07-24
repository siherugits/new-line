<?php

namespace App\Controllers;

use App\Libraries\CaptchaSvg;
use CodeIgniter\HTTP\ResponseInterface;

class CaptchaController extends BaseController
{
    /**
     * Outputs a fresh SVG captcha image and stores its answer in session.
     */
    public function index(): ResponseInterface
    {
        $svg = (new CaptchaSvg())->generate();

        return $this->response
            ->setHeader('Content-Type', 'image/svg+xml')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setBody($svg);
    }
}
