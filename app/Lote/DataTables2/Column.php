<?php

declare(strict_types=1);

namespace App\Lote\DataTables2;

class Column
{
    public ?string $label;

    public ?string $sort;

    public ?string $tooltip;

    public ?string $classes;

    public ?string $tdClasses;

    public ?array $style;

    public ?array $tdStyle;

    const STICKY_CLASS = 'dtSticky';

    public static function make(array $data): Column
    {
        return new Column($data);
    }

    public function __construct(array $data)
    {
        $this->label = $data['label'] ?? '';
        $this->sort = $data['sort'] ?? '';
        $this->classes = $data['classes'] ?? '';
        $this->tooltip = $data['tooltip'] ?? null;
        $this->tdClasses = $data['tdClasses'] ?? '';
        $this->style = isset($data['style']) ? $this->styleToArray($data['style']) : [];
        $this->tdStyle = isset($data['tdStyle']) ? $this->styleToArray($data['tdStyle']) : [];
    }

    public function tooltip(?string $tooltip): self
    {
        $this->tooltip = $tooltip;

        return $this;
    }

    public function sort(?string $field): self
    {
        $this->sort = $field;

        return $this;
    }

    public function alignTop(): self
    {
        return $this->addTdStyle('vertical-align: top');
    }

    public function alignRight(): self
    {
        return $this->addClass('text-right')->addTdClass('text-right');
    }

    public function width(string $width): self
    {
        return $this->addStyle('width:'.$width);
    }

    public function sticky(): self
    {
        return $this->addClass(self::STICKY_CLASS)->addTdClass(self::STICKY_CLASS);
    }

    public function isSticky(): bool
    {
        return str_contains($this->classes, self::STICKY_CLASS);
    }

    public function addDefaultWidth($defaultWidth): void
    {
        if (! isset($this->style['width'])) {
            $this->style['width'] = $defaultWidth;
        }
    }

    public function addClass(string $class): self
    {
        $this->classes = $this->formatClass($this->classes, $class);

        return $this;
    }

    public function addTdClass(string $class): self
    {
        $this->tdClasses = $this->formatClass($this->tdClasses, $class);

        return $this;
    }

    private function formatClass(string $currentClass, string $class): string
    {
        $newClasses = explode(' ', $class);
        $classes = explode(' ', $currentClass);

        foreach ($newClasses as $newClass) {
            if (! in_array($newClass, $classes)) {
                $currentClass = trim($currentClass.' '.$newClass);
            }
        }

        return $currentClass;

    }

    public function addStyle(string $style): self
    {
        $new_styles = $this->styleToArray($style);
        $this->style = array_merge($this->style, $new_styles);

        return $this;
    }

    public function addTdStyle(string $style): self
    {
        $new_styles = $this->styleToArray($style);
        $this->tdStyle = array_merge($this->tdStyle, $new_styles);

        return $this;
    }

    private function styleToArray(string $style): array
    {
        $result = [];
        $styles = explode(';', trim($style));

        foreach ($styles as $declaration) {
            if (in_array(trim($declaration), ['', '0'], true)) {
                continue;
            }

            [$property, $value] = explode(':', $declaration, 2);
            $result[trim($property)] = trim($value);
        }

        return $result;
    }

    private function arrayToStyle(array $styles): string
    {
        if ($styles === []) {
            return '';
        }

        return implode(';', array_map(
            fn (string $property, $value): string => sprintf('%s:%s', $property, $value),
            array_keys($styles),
            $styles
        ));
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'sort' => $this->sort,
            'tooltip' => $this->tooltip,
            'classes' => $this->classes,
            'tdClasses' => $this->tdClasses,
            'style' => $this->arrayToStyle($this->style),
            'tdStyle' => $this->arrayToStyle($this->tdStyle),
        ];
    }
}
