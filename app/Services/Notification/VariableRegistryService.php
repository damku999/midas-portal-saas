<?php

namespace App\Services\Notification;

use Illuminate\Support\Collection;

/**
 * Variable Registry Service
 *
 * Central registry for all notification template variables
 */
class VariableRegistryService
{
    protected array $config;

    protected Collection $variables;

    protected Collection $categories;

    public function __construct()
    {
        $this->config = config('notification_variables', []);
        $this->loadVariables();
        $this->loadCategories();
    }

    /**
     * Load variables from config
     */
    protected function loadVariables(): void
    {
        $variables = $this->config['variables'] ?? [];

        $this->variables = collect($variables)->map(fn ($metadata, $key): array => array_merge($metadata, ['key' => $key]));
    }

    /**
     * Load categories from config
     */
    protected function loadCategories(): void
    {
        $categories = $this->config['categories'] ?? [];

        $this->categories = collect($categories)->map(fn ($metadata, $key): array => array_merge($metadata, ['key' => $key]))->sortBy('order');
    }

    /**
     * Get all registered variables
     */
    public function getAllVariables(): Collection
    {
        return $this->variables;
    }

    /**
     * Get all categories
     */
    public function getAllCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * Get variables by category
     *
     * @param  string  $category  Category key
     */
    public function getVariablesByCategory(string $category): Collection
    {
        return $this->variables->filter(fn (array $variable): bool => $variable['category'] === $category);
    }

    /**
     * Get variables by notification type
     *
     * @param  string|null  $notificationType  Notification type key
     */
    public function getVariablesByNotificationType(?string $notificationType = null): Collection
    {
        if (in_array($notificationType, [null, '', '0'], true)) {
            // Return all variables if no type specified
            return $this->variables;
        }

        $typeConfig = $this->config['notification_types'][$notificationType] ?? null;

        if (! $typeConfig) {
            // Return all if type not found
            return $this->variables;
        }

        // Get suggested variables for this type
        $suggestedKeys = $typeConfig['suggested_variables'] ?? [];

        if (empty($suggestedKeys)) {
            // No suggestions, return all
            return $this->variables;
        }

        // Return suggested variables + variables that don't require specific context
        return $this->variables->filter(static function (array $variable) use ($suggestedKeys): bool {
            // Include if in suggested list
            if (in_array($variable['key'], $suggestedKeys)) {
                return true;
            }

            // Include if requires no specific context (system variables like current_date, company info)
            return empty($variable['requires']) || $variable['requires'] === [];
        });
    }

    /**
     * Get variable metadata by key
     *
     * @param  string  $variableKey  Variable key
     */
    public function getVariableMetadata(string $variableKey): ?array
    {
        return $this->variables->firstWhere('key', $variableKey);
    }

    /**
     * Get variables grouped by category
     *
     * @param  string|null  $notificationType  Optional notification type to filter
     * @return Collection Collection of categories with their variables
     */
    public function getVariablesGroupedByCategory(?string $notificationType = null): Collection
    {
        $variables = $this->getVariablesByNotificationType($notificationType);

        return $variables->groupBy('category')->map(function ($categoryVariables, $categoryKey): array {
            $categoryMeta = $this->categories->firstWhere('key', $categoryKey);

            return [
                'category' => $categoryKey,
                'label' => $categoryMeta['label'] ?? ucfirst($categoryKey),
                'color' => $categoryMeta['color'] ?? 'secondary',
                'icon' => $categoryMeta['icon'] ?? 'fa-tag',
                'order' => $categoryMeta['order'] ?? 999,
                'variables' => $categoryVariables->values(),
            ];
        })->sortBy('order')->values();
    }

    /**
     * Get required context for notification type
     *
     * @param  string  $notificationType  Notification type key
     * @return array Array of required entity names (e.g., ['customer', 'insurance'])
     */
    public function getRequiredContext(string $notificationType): array
    {
        $typeConfig = $this->config['notification_types'][$notificationType] ?? null;

        return $typeConfig['required_context'] ?? [];
    }

    /**
     * Get suggested variables for notification type
     *
     * @param  string  $notificationType  Notification type key
     * @return array Array of suggested variable keys
     */
    public function getSuggestedVariables(string $notificationType): array
    {
        $typeConfig = $this->config['notification_types'][$notificationType] ?? null;

        return $typeConfig['suggested_variables'] ?? [];
    }

    /**
     * Check if variable exists
     *
     * @param  string  $variableKey  Variable key
     */
    public function hasVariable(string $variableKey): bool
    {
        return $this->variables->contains('key', $variableKey);
    }

    /**
     * Get attachment variables only
     */
    public function getAttachmentVariables(): Collection
    {
        return $this->variables->filter(fn (array $variable): bool => $variable['type'] === 'attachment');
    }

    /**
     * Get computed variables only
     */
    public function getComputedVariables(): Collection
    {
        return $this->variables->filter(fn (array $variable): bool => $variable['type'] === 'computed');
    }

    /**
     * Get system variables only
     */
    public function getSystemVariables(): Collection
    {
        return $this->variables->filter(fn (array $variable): bool => $variable['type'] === 'system');
    }

    /**
     * Get setting variables only
     */
    public function getSettingVariables(): Collection
    {
        return $this->variables->filter(fn (array $variable): bool => $variable['type'] === 'setting');
    }

    /**
     * Extract variables from template content
     *
     * @param  string  $template  Template content
     * @return array Array of variable keys found in template
     */
    public function extractVariablesFromTemplate(string $template): array
    {
        // Match {{variable}} and {{@attachment}}
        preg_match_all('/\{\{(@?\w+)\}\}/', $template, $matches);

        return array_unique($matches[1] ?? []);
    }

    /**
     * Validate template has all required variables for notification type
     *
     * @param  string  $template  Template content
     * @param  string  $notificationType  Notification type key
     * @return array ['valid' => bool, 'missing' => array, 'unknown' => array]
     */
    public function validateTemplate(string $template, string $notificationType): array
    {
        $extracted = $this->extractVariablesFromTemplate($template);
        $suggested = $this->getSuggestedVariables($notificationType);

        $unknown = [];
        foreach ($extracted as $varKey) {
            if (! $this->hasVariable($varKey)) {
                $unknown[] = $varKey;
            }
        }

        $missing = array_diff($suggested, $extracted);

        return [
            'valid' => $unknown === [],
            'missing' => array_values($missing), // Suggested but not used (warning)
            'unknown' => array_values($unknown), // Used but not registered (error)
            'used' => $extracted,
        ];
    }

    /**
     * Get variable info for UI display
     *
     * @param  string  $variableKey  Variable key
     * @return array UI-friendly variable info
     */
    public function getVariableForUI(string $variableKey): array
    {
        $metadata = $this->getVariableMetadata($variableKey);

        if ($metadata === null || $metadata === []) {
            return [];
        }

        $category = $this->categories->firstWhere('key', $metadata['category']);

        return [
            'key' => $metadata['key'],
            'label' => $metadata['label'],
            'description' => $metadata['description'],
            'sample' => $metadata['sample'],
            'color' => $category['color'] ?? 'secondary',
            'icon' => $category['icon'] ?? 'fa-tag',
            'type' => $metadata['type'],
            'category' => $metadata['category'],
            'variable_format' => $metadata['variable_format'] ?? $metadata['key'],
        ];
    }

    /**
     * Get all variables formatted for UI
     *
     * @param  string|null  $notificationType  Optional notification type filter
     */
    public function getAllVariablesForUI(?string $notificationType = null): Collection
    {
        $variables = $this->getVariablesByNotificationType($notificationType);

        return $variables->map(fn (array $metadata): array => $this->getVariableForUI($metadata['key']));
    }
}
