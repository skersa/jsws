<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name'        => ['required', 'string', 'max:191'],
            'slug'        => [
                'nullable', 'string', 'max:191',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'parent_id'   => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
                function ($attr, $value, $fail) use ($categoryId) {
                    if (! $categoryId || ! $value) return;
                    if ((int) $value === $categoryId) {
                        $fail('Kategori kendisinin üst kategorisi olamaz.');
                        return;
                    }
                    $child = \App\Models\Category::find($categoryId);
                    if ($child && in_array((int) $value, $child->allChildrenIds())) {
                        $fail('Alt kategori, üst kategori olarak seçilemez.');
                    }
                },
            ],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active'   => ['nullable', 'boolean'],
            'sort_order'  => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Kategori adı zorunludur.',
            'name.max'         => 'Kategori adı en fazla 191 karakter olabilir.',
            'slug.unique'      => 'Bu slug zaten kullanılıyor.',
            'parent_id.exists' => 'Seçilen üst kategori bulunamadı.',
            'image.image'      => 'Geçerli bir resim dosyası seçin.',
            'image.max'        => 'Resim en fazla 2MB olabilir.',
            'sort_order.min'   => 'Sıralama değeri 0 veya daha büyük olmalı.',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        if (empty($data['slug'])) unset($data['slug']);

        $data['is_active']  = (bool) ($data['is_active']  ?? false);
        $data['sort_order'] = (int)  ($data['sort_order'] ?? 0);

        return $data;
    }
}
