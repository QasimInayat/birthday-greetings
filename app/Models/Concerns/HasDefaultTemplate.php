<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Shared "one default per type" behaviour for SMS and email templates.
 */
trait HasDefaultTemplate
{
    /**
     * Make this the default for its type, clearing any previous default.
     */
    public function makeDefault(): void
    {
        DB::transaction(function () {
            static::where('template_type', $this->template_type)
                ->where('id', '!=', $this->id)
                ->update(['is_default' => false]);

            $this->forceFill(['is_default' => true])->save();
        });
    }

    /**
     * The default template for a type, falling back to the oldest of that type.
     */
    public static function defaultFor(string $type): ?self
    {
        return static::where('template_type', $type)->where('is_default', true)->first()
            ?? static::where('template_type', $type)->orderBy('id')->first();
    }

    /**
     * Keep the flag exclusive whenever a template is saved as default.
     */
    protected static function bootHasDefaultTemplate(): void
    {
        static::saved(function ($template) {
            if ($template->is_default) {
                static::where('template_type', $template->template_type)
                    ->where('id', '!=', $template->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });

        // Moving a template to another type must not strand either type without
        // a default - that silently disables sending for the type it left.
        static::updated(function ($template) {
            $previousType = $template->getOriginal('template_type');

            if (!$previousType || $previousType === $template->template_type) {
                return;
            }

            static::ensureTypeHasDefault($previousType);

            if (!static::where('template_type', $template->template_type)->where('is_default', true)->exists()) {
                $template->forceFill(['is_default' => true])->saveQuietly();
            }
        });
    }

    /**
     * Promote a template so the given type is never left without a default.
     */
    public static function ensureTypeHasDefault(string $type): void
    {
        if (static::where('template_type', $type)->where('is_default', true)->exists()) {
            return;
        }

        if ($next = static::where('template_type', $type)->orderBy('id')->first()) {
            $next->forceFill(['is_default' => true])->saveQuietly();
        }
    }
}
