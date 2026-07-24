<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Default theme colors. These act as the fallback values for
 * CodeIgniter Settings: setting('Theme.primary') returns the DB value
 * if present, otherwise the property defined here.
 *
 * Colors are hex strings (e.g. "#0d6efd").
 */
class Theme extends BaseConfig
{
    /** Main accent — buttons, links, active states. */
    public string $primary = '#0d6efd';

    /** Secondary accent — badges, muted buttons. */
    public string $secondary = '#6c757d';

    /** Success state (badges, alerts). */
    public string $success = '#198754';

    /** Danger state (delete buttons, alerts). */
    public string $danger = '#dc3545';

    /** Top navbar background. */
    public string $navbarBg = '#212529';

    /** Top navbar menu text color. */
    public string $navbarText = '#ffffff';

    /** Page background. */
    public string $bodyBg = '#f5f6fa';

    /** Whether the navbar uses light text (true = dark navbar). */
    public bool $navbarDark = true;

    /** Whether the whole admin uses Bootstrap 5.3 dark mode. */
    public bool $darkMode = false;

    /**
     * The keys editable through the admin theme page, with labels.
     * Used by the controller/view to render the form generically.
     *
     * @return array<string, string>
     */
    public static function colorFields(): array
    {
        return [
            'primary'   => 'Warna Utama (Primary)',
            'secondary' => 'Warna Sekunder',
            'success'   => 'Warna Sukses',
            'danger'    => 'Warna Bahaya (Danger)',
            'navbarBg'   => 'Background Topbar',
            'navbarText' => 'Warna Teks Menu Topbar',
            'bodyBg'     => 'Background Halaman',
        ];
    }
}
