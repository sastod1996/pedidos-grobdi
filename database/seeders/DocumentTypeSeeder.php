<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DocumentType::factory()->create(['code' => 'DNI', 'name' => 'Documento Nacional de Identidad', 'state' => true]);
        DocumentType::factory()->create(['code' => 'RUC', 'name' => 'Registro Único de Contribuyentes', 'state' => true]);
        DocumentType::factory()->create(['code' => 'CE', 'name' => 'Carné de Extranjería', 'state' => true]);
        DocumentType::factory()->create(['code' => 'PASS', 'name' => 'Pasaporte', 'state' => true]);
    }
}
