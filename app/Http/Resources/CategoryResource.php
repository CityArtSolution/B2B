<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    private function getTranslationsMap(string $field, mixed $fallback = null): array
    {
        $translations = $this->relationLoaded('translations')
            ? $this->translations
            : $this->translations()->get();

        $values = $translations
            ->filter(fn ($translation) => filled($translation->lang) && filled($translation->{$field} ?? null))
            ->mapWithKeys(fn ($translation) => [$translation->lang => $translation->{$field}])
            ->toArray();

        if (filled($fallback) && ! array_key_exists('en', $values)) {
            $values['en'] = $fallback;
        }

        return $values;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing('translations');

        $lang = request()->header('accept-language') ?? 'en';
        $translation = $lang != 'en' ? $this->translations->where('lang', $lang)->first() : null;

        return [
            'id' => $this->id ?? null,
            'name' => $translation ? $translation->name : ($this->name ?? null),
            'translations' => [
                'name' => $this->getTranslationsMap('name', $this->name),
            ],
            'thumbnail' => $this->thumbnail ?? null,
            'sub_categories' => SubCategoryResource::collection($this->subCategories ?? []),
        ];
    }
}
