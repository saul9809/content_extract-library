<?php

/**
 * Example: Semantic Structuring with SchemaAwareStructurer
 * 
 * This example demonstrates the new semantic structuring pipeline
 * for extracting structured data from documents with field aliases.
 * 
 * Scenario: Extract CV data from poorly formatted text
 * 
 * Features:
 * - Text normalization and cleaning
 * - Semantic fragmentation
 * - Alias-driven field matching
 * - Automatic warning generation
 * - Type conversion
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ContentProcessor\Structurers\SchemaAwareStructurer;
use ContentProcessor\Schemas\ArraySchema;

// Example CV text (unstructured, noisy format)
$rawCVText = <<<'TEXT'
==========================================
   CURRICULUM VITAE
==========================================

PERSONAL INFORMATION
Name:   Juan Perez García
Email:   juan.perez@example.com
Phone: +34-666-777-888
Address: Calle Principal 123, Madrid, Spain

PROFESSIONAL SUMMARY
========================================
Senior backend engineer with 8 years of experience
in PHP, Laravel, and cloud-native architectures.

KEY SKILLS
- PHP 8.1+
- Laravel Framework
- PostgreSQL & MySQL
- Docker & Kubernetes
- RESTful API Design

PROFESSIONAL EXPERIENCE
========================================

Position: Senior Backend Engineer
Company: TechCorp Solutions
Duration: 2020 - Present
- Led team of 5 developers
- Architected microservices platform

Position: Backend Developer
Company: StartupXYZ
Duration: 2017 - 2020
- Built real-time analytics system
- Implemented CI/CD pipelines

EDUCATION
========================================
Degree: Bachelor of Computer Science
University: Universidad de Madrid
Graduation: 2017

Languages: Spanish (Native), English (Fluent)

TEXT;

// Define schema with semantic aliases
// This tells the structurer how to find each field
$cvSchema = [
    // Personal information
    'name' => [
        'type' => 'string',
        'required' => true,
        'aliases' => [
            'name',
            'full name',
            'applicant name',
            'personal name',
            'nombre completo', // Spanish alternative
        ],
    ],
    'email' => [
        'type' => 'string',
        'required' => true,
        'aliases' => [
            'email',
            'email address',
            'e-mail',
            'contact email',
            'correo electrónico', // Spanish
        ],
    ],
    'phone' => [
        'type' => 'string',
        'required' => false,
        'aliases' => [
            'phone',
            'telephone',
            'mobile',
            'cell phone',
            'contact number',
            'teléfono', // Spanish
        ],
    ],
    'address' => [
        'type' => 'string',
        'required' => false,
        'aliases' => [
            'address',
            'location',
            'home address',
            'dirección', // Spanish
        ],
    ],

    // Professional info
    'years_experience' => [
        'type' => 'integer',
        'required' => false,
        'aliases' => [
            'years of experience',
            'years experience',
            'experience',
            'experience level',
            'años de experiencia', // Spanish
        ],
    ],
    'current_position' => [
        'type' => 'string',
        'required' => false,
        'aliases' => [
            'position',
            'job title',
            'current position',
            'role',
            'puesto actual', // Spanish
        ],
    ],
    'current_company' => [
        'type' => 'string',
        'required' => false,
        'aliases' => [
            'company',
            'current company',
            'employer',
            'organization',
            'empresa actual', // Spanish
        ],
    ],
    'skills' => [
        'type' => 'array',
        'required' => false,
        'aliases' => [
            'skills',
            'technical skills',
            'competencies',
            'key skills',
            'habilidades', // Spanish
        ],
    ],
    'education' => [
        'type' => 'string',
        'required' => false,
        'aliases' => [
            'education',
            'degree',
            'studies',
            'educational background',
            'formación académica', // Spanish
        ],
    ],
];

// Initialize the schema-aware structurer
// with custom configuration
$structurer = new SchemaAwareStructurer(
    $matchThreshold = 0.65,  // Threshold for accepting matches
    $normalizerConfig = [
        'lowercase' => true,
        'remove_accents' => true, // Help match "Perez" with "Perez"
    ],
    $maxWordsPerFragment = 15
);

// Create ArraySchema from definition
$schema = new ArraySchema($cvSchema);

// Structure the CV text
echo "=== Semantic CV Extraction Example ===\n";
echo "Processing raw CV text...\n\n";

$result = $structurer->structure($rawCVText, $schema);

$data = $result['data'] ?? [];
$warnings = $result['warnings'] ?? [];

// Display extracted data
echo "✓ EXTRACTED DATA:\n";
echo str_repeat("-", 50) . "\n";

foreach ($cvSchema as $fieldName => $fieldDef) {
    $value = $data[$fieldName] ?? null;
    $required = $fieldDef['required'] ? '(required)' : '(optional)';

    if ($value === null) {
        echo "  {$fieldName}: {$required} NOT FOUND\n";
    } else {
        if (is_array($value)) {
            echo "  {$fieldName}: " . implode(', ', $value) . "\n";
        } else {
            $displayValue = strlen($value) > 40 ? substr($value, 0, 40) . '...' : $value;
            echo "  {$fieldName}: {$displayValue}\n";
        }
    }
}

// Display warnings
echo "\n⚠ WARNINGS & NOTES:\n";
echo str_repeat("-", 50) . "\n";

if (empty($warnings)) {
    echo "  No warnings - extraction clean!\n";
} else {
    foreach ($warnings as $idx => $warning) {
        $category = strtoupper($warning['category']);
        $field = $warning['field'];
        $msg = substr($warning['message'], 0, 60);
        echo "  [{$category}] {$field}: {$msg}...\n";
    }
}

// Show statistics
echo "\n📊 EXTRACTION STATISTICS:\n";
echo str_repeat("-", 50) . "\n";
echo "  Fields defined: " . count($cvSchema) . "\n";
echo "  Fields extracted: " . count($data) . "\n";
echo "  Warnings generated: " . count($warnings) . "\n";
$coverage = count($data) / count($cvSchema) * 100;
echo "  Coverage: {$coverage}%\n";

echo "\n=== End Example ===\n";
