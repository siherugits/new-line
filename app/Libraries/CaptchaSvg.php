<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Simple SVG based CAPTCHA. Requires no GD extension.
 *
 * Generates a random code, stores a hash of it in the session, and
 * renders a distorted SVG image so the code is hard to read for bots.
 */
class CaptchaSvg
{
    /** Session key used to store the answer hash. */
    public const SESSION_KEY = 'captcha_hash';

    /** Characters used for the code (ambiguous ones removed). */
    private const CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private int $length;
    private int $width;
    private int $height;

    public function __construct(int $length = 5, int $width = 160, int $height = 50)
    {
        $this->length = $length;
        $this->width  = $width;
        $this->height = $height;
    }

    /**
     * Generates a fresh code, stores its hash in the session and returns
     * the rendered SVG markup.
     */
    public function generate(): string
    {
        $code = $this->randomCode();

        session()->set(self::SESSION_KEY, $this->hash($code));

        return $this->render($code);
    }

    /**
     * Verifies the user's answer against the stored hash. The stored hash
     * is always cleared afterwards so each captcha can be used only once.
     */
    public function verify(?string $answer): bool
    {
        $stored = session()->get(self::SESSION_KEY);
        session()->remove(self::SESSION_KEY);

        if ($stored === null || $answer === null || $answer === '') {
            return false;
        }

        return hash_equals($stored, $this->hash($answer));
    }

    private function hash(string $code): string
    {
        // Case-insensitive, whitespace tolerant.
        $normalized = strtoupper(trim($code));

        return hash_hmac('sha256', $normalized, (string) config('Encryption')->key ?: 'captcha');
    }

    private function randomCode(): string
    {
        $max  = strlen(self::CHARS) - 1;
        $code = '';

        for ($i = 0; $i < $this->length; $i++) {
            $code .= self::CHARS[random_int(0, $max)];
        }

        return $code;
    }

    /**
     * Builds distorted SVG markup for the given code.
     */
    private function render(string $code): string
    {
        $w = $this->width;
        $h = $this->height;

        $svg  = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '" role="img" aria-label="captcha">';
        $svg .= '<rect width="100%" height="100%" fill="#f8f9fa"/>';

        // Noise lines.
        for ($i = 0; $i < 6; $i++) {
            $x1 = random_int(0, $w);
            $y1 = random_int(0, $h);
            $x2 = random_int(0, $w);
            $y2 = random_int(0, $h);
            $svg .= '<line x1="' . $x1 . '" y1="' . $y1 . '" x2="' . $x2 . '" y2="' . $y2 . '" stroke="' . $this->randomColor(180, 220) . '" stroke-width="1"/>';
        }

        // Noise dots.
        for ($i = 0; $i < 40; $i++) {
            $cx = random_int(0, $w);
            $cy = random_int(0, $h);
            $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="1" fill="' . $this->randomColor(150, 210) . '"/>';
        }

        // Characters.
        $len     = strlen($code);
        $slot    = $w / ($len + 1);
        $centerY = $h / 2;

        for ($i = 0; $i < $len; $i++) {
            $char   = $code[$i];
            $x      = (int) ($slot * ($i + 1));
            $y      = (int) ($centerY + random_int(-4, 4));
            $rotate = random_int(-25, 25);
            $size   = random_int($h - 22, $h - 12);
            $color  = $this->randomColor(20, 110);

            $svg .= '<text x="' . $x . '" y="' . $y . '" font-family="Verdana, Arial, sans-serif" '
                . 'font-size="' . $size . '" font-weight="bold" fill="' . $color . '" '
                . 'text-anchor="middle" dominant-baseline="middle" '
                . 'transform="rotate(' . $rotate . ' ' . $x . ' ' . $y . ')">'
                . $this->escape($char) . '</text>';
        }

        $svg .= '</svg>';

        return $svg;
    }

    private function randomColor(int $min, int $max): string
    {
        return sprintf(
            '#%02x%02x%02x',
            random_int($min, $max),
            random_int($min, $max),
            random_int($min, $max),
        );
    }

    private function escape(string $char): string
    {
        return htmlspecialchars($char, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
