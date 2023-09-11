<?php

declare(strict_types=1);

namespace App\Response;

readonly class LabelledCollectionBody implements BodyInterface
{
    /**
     * @param non-empty-string $identifier
     * @param BodyInterface[]  $items
     */
    public function __construct(
        private string $identifier,
        private array $items,
    ) {
    }

    /**
     * @return array<non-empty-string,array<mixed>>
     */
    public function toArray(): array
    {
        $renderedItems = [];
        foreach ($this->items as $item) {
            $renderedItems[] = $item->toArray();
        }

        return [
            $this->identifier => $renderedItems,
        ];
    }
}
