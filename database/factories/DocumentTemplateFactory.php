<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentTemplate>
 */
class DocumentTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'document_type' => DocumentType::Invoice,
            'name' => $this->faker->word,
            'description' => $this->faker->paragraph,
            'package' => '@'.$this->faker->word.'/'.$this->faker->word,
            'installation_path' => 'none',
            'options' => ['locales' => ['sk'], 'entryPoint' => 'render.js'],
            'is_default' => false,
        ];
    }
}
