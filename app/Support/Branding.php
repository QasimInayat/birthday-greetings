<?php

namespace App\Support;

use App\Models\EmailSetting;

class Branding
{
    /**
     * Absolute URL of the logo to show in emails, or null when none is usable.
     *
     * Mail clients cannot resolve relative paths, so this must always be a full
     * URL built from APP_URL - which means APP_URL has to be correct in .env.
     */
    public static function logoUrl(): ?string
    {
        $path = static::logoPath();

        return $path ? asset($path) : null;
    }

    /**
     * Public-relative path of the logo: the uploaded one, else the app logo.
     */
    public static function logoPath(): ?string
    {
        try {
            $uploaded = optional(EmailSetting::first())->logo_path;
        } catch (\Throwable $e) {
            $uploaded = null; // column not migrated yet
        }

        if ($uploaded && file_exists(public_path($uploaded))) {
            return $uploaded;
        }

        $fallback = 'assets/img/logo.png';

        return file_exists(public_path($fallback)) ? $fallback : null;
    }

    /**
     * True when the logo is the shipped default rather than one the admin chose.
     */
    public static function usingDefaultLogo(): bool
    {
        try {
            $uploaded = optional(EmailSetting::first())->logo_path;
        } catch (\Throwable $e) {
            return true;
        }

        return !($uploaded && file_exists(public_path($uploaded)));
    }
}
