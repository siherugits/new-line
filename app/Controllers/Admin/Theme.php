<?php

namespace App\Controllers\Admin;

use Config\Theme as ThemeConfig;

class Theme extends BaseAdminController
{
    public function index(): string
    {
        return $this->render('admin/theme/index', [
            'fields'    => ThemeConfig::colorFields(),
            'values'    => $this->currentValues(),
            'navbarDark' => (bool) $this->settingBool('Theme.navbarDark'),
            'darkMode'   => (bool) setting('Theme.darkMode'),
        ], 'Tema Aplikasi');
    }

    public function update()
    {
        $fields = ThemeConfig::colorFields();

        // Validate every color is a valid hex value.
        $rules = [];
        foreach (array_keys($fields) as $key) {
            $rules[$key] = 'required|regex_match[/^#[0-9A-Fa-f]{6}$/]';
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        foreach (array_keys($fields) as $key) {
            setting()->set('Theme.' . $key, $this->request->getPost($key));
        }
        setting()->set('Theme.navbarDark', $this->request->getPost('navbarDark') ? '1' : '0');
        setting()->set('Theme.darkMode', $this->request->getPost('darkMode') ? '1' : '0');

        return redirect()->to('admin/theme')->with('message', 'Tema berhasil disimpan.');
    }

    /**
     * Reset all theme settings back to the config defaults.
     */
    public function reset()
    {
        $keys = array_keys(ThemeConfig::colorFields());
        $keys[] = 'navbarDark';
        $keys[] = 'darkMode';
        foreach ($keys as $key) {
            setting()->forget('Theme.' . $key);
        }

        return redirect()->to('admin/theme')->with('message', 'Tema dikembalikan ke default.');
    }

    /**
     * Current color values (DB setting or config fallback).
     *
     * @return array<string,string>
     */
    private function currentValues(): array
    {
        $values = [];
        foreach (array_keys(ThemeConfig::colorFields()) as $key) {
            $values[$key] = (string) setting('Theme.' . $key);
        }

        return $values;
    }

    private function settingBool(string $key): bool
    {
        $val = setting($key);

        return $val === null ? true : (bool) $val;
    }
}
