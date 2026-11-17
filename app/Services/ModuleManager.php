<?php

namespace App\Services;

use App\Models\ModuleSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ModuleManager
{
    public function all(): Collection
    {
        $defaults = collect(config('modules', []));

        if (! Schema::hasTable('module_settings')) {
            return $defaults->map(fn (array $module, string $key) => array_merge($module, ['key' => $key]));
        }

        $overrides = Cache::remember('module_settings_map', 60, function () {
            return ModuleSetting::query()->pluck('enabled', 'key')->all();
        });

        return $defaults->map(function (array $module, string $key) use ($overrides) {
            if (array_key_exists($key, $overrides)) {
                $module['enabled'] = (bool) $overrides[$key];
            }

            return array_merge($module, ['key' => $key]);
        });
    }

    public function enabled(string $key): bool
    {
        $module = $this->all()->firstWhere('key', $key);

        return (bool) ($module['enabled'] ?? false);
    }

    public function enabledKeys(): array
    {
        return $this->all()
            ->filter(fn (array $module) => $module['enabled'])
            ->pluck('key')
            ->values()
            ->all();
    }

    public function weeklyHoursSaved(): int
    {
        return (int) $this->all()
            ->filter(fn (array $module) => $module['enabled'])
            ->sum(fn (array $module) => (int) ($module['hours_saved_weekly'] ?? 0));
    }

    public function set(string $key, bool $enabled): void
    {
        $config = config("modules.{$key}", []);

        ModuleSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'name' => $config['name'] ?? $key,
                'description' => $config['description'] ?? null,
                'category' => $config['category'] ?? null,
                'enabled' => $enabled,
                'is_core' => (bool) ($config['core'] ?? false),
                'hours_saved_weekly' => (int) ($config['hours_saved_weekly'] ?? 0),
            ]
        );

        Cache::forget('module_settings_map');
    }

    public function syncFromConfig(): void
    {
        foreach (config('modules', []) as $key => $module) {
            ModuleSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'name' => $module['name'],
                    'description' => $module['description'] ?? null,
                    'category' => $module['category'] ?? null,
                    'enabled' => (bool) ($module['enabled'] ?? false),
                    'is_core' => (bool) ($module['core'] ?? false),
                    'hours_saved_weekly' => (int) ($module['hours_saved_weekly'] ?? 0),
                ]
            );
        }

        Cache::forget('module_settings_map');
    }
}