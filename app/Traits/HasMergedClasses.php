<?php

namespace App\Traits;

trait HasMergedClasses
{
    public string $class = '';

    public ?string $classes = '';

    public function setMergedClasses(string|array $mergedClasses): void
    {
        if (is_array($mergedClasses)) {
            $mergedClasses = implode(' ', $mergedClasses);
        }
        $this->classes = $mergedClasses;
    }

    public function mountHasMergedClasses(): void
    {
        $split = array_merge(explode(' ', $this->class), explode(' ', $this->classes));
        $this->classes = implode(' ', array_unique($split));
    }

    public function getMergedClasses(): string
    {
        return $this->classes;
    }
}
