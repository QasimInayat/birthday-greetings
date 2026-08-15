<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Guards the two layout mistakes that pushed page content off screen.
 */
class LayoutTest extends TestCase
{
    public function test_no_page_double_offsets_the_sidebar_width(): void
    {
        // .main-content already carries margin-left:260px for the fixed sidebar.
        // A second 260px on an inner container pushes content off the right edge.
        $offenders = [];

        foreach (glob(resource_path('views/**/*.blade.php')) as $file) {
            if (preg_match('/margin-left:\s*260px/', file_get_contents($file))) {
                $offenders[] = basename(dirname($file)) . '/' . basename($file);
            }
        }

        $this->assertSame([], $offenders, 'These views double the sidebar offset: ' . implode(', ', $offenders));
    }

    public function test_default_avatar_exists(): void
    {
        $this->assertFileExists(
            public_path('assets/img/profile-default.png'),
            'Views fall back to this image for employees with no photo.'
        );
    }

    public function test_wide_tables_are_scrollable(): void
    {
        $unwrapped = [];

        foreach (glob(resource_path('views/**/*.blade.php')) as $file) {
            $html = file_get_contents($file);

            if (str_contains($html, '<table') && !str_contains($html, 'table-responsive')) {
                $unwrapped[] = basename(dirname($file)) . '/' . basename($file);
            }
        }

        $this->assertSame([], $unwrapped, 'Tables without .table-responsive overflow on small screens: ' . implode(', ', $unwrapped));
    }
}
