<?php
//$ingredient_template = [
//    'name' => 'Sodium',
//    'value' => '5',
//    'uom' => 'g',
//    'rda' => '5%',
//    'relevant' => true,
//];

return [

    'top_level_fields' => [
        'calories' => 'Calories',
        'servings_per_container' => 'Serving Suggestion - Manual',
        'serving_size' => 'Serving Size Description - Manual',
    ],

    'unit_fields' => [
        'Total Fat' => [
            'value' => 'Total Fat (DevEX)',
            'uom' => 'Total Fat UOM (DevEX)',
            'dv' => 'Total Fat (% DV)',
            'ignorable' => false,
        ],
        'Trans Fat' => [
            'value' => 'Trans Fat (DevEX)',
            'uom' => 'Trans Fat UOM (DevEX)',
            'dv' => 'Trans Fat (% DV)',
            'ignorable' => true,
        ],
        'Saturated Fat' => [
            'value' => 'Saturated Fat UOM (DevEX)',
            'uom' => 'Saturated Fat UOM (DevEX)',
            'dv' => 'Saturated Fat (% DV)',
            'ignorable' => true,
        ],
        'Cholesterol' => [
            'value' => 'Cholesterol (DevEX)',
            'uom' => 'Cholesterol UOM (DevEX)',
            'dv' => 'Cholesterol (% DV)',
            'ignorable' => true,
        ],
        'Sodium' => [
            'value' => 'Sodium (DevEX)',
            'uom' => 'Sodium UOM (DevEX)',
            'dv' => 'Sodium (% DV)',
            'ignorable' => false,
        ],
        'Total Carbohydrates' => [
            'value' => 'Total Carbohydrate (DevEX)',
            'uom' => 'Total Carbohydrate UOM (DevEX)',
            'dv' => 'Total Carbohydrates (% DV)',
            'ignorable' => false,
        ],
        'Dietary Fiber' => [
            'value' => 'Dietary fiber (DevEX)',
            'uom' => 'Dietary fiber UOM (DevEX)',
            'dv' => 'Total Dietary Fiber (% DV)',
            'ignorable' => true,
        ],
        'Total Sugar' => [
            'value' => 'Total Sugars (DevEX)',
            'uom' => 'Total Sugars UOM (DevEX)',
            'dv' => null,
            'ignorable' => false,
        ],
        'Added Sugars' => [
            'value' => 'Added Sugars (DevEX)',
            'uom' => 'Added Sugars UOM (DevEX)',
            'dv' => 'Added Sugar (% DV)',
            'ignorable' => false,
        ],
        'Protein' => [
            'value' => 'Protein (DevEX)',
            'uom' => 'Protein UOM (DevEX)',
            'dv' => null,
            'ignorable' => false,
        ],
        'Vitamin D' => [
            'value' => 'Vitamin D (DevEX)',
            'uom' => 'Vitamin D UOM (DevEX)',
            'dv' => 'Vitamin D (% DV)',
            'ignorable' => true,
        ],
        'Calcium' => [
            'value' => 'Calcium (DevEX)',
            'uom' => 'Calcium UOM (DevEX)',
            'dv' => 'Calcium (% DV)',
            'ignorable' => true,
        ],
        'Iron' => [
            'value' => 'Iron (DevEX)',
            'uom' => 'Iron UOM (DevEX)',
            'dv' => 'Iron (% DV)',
            'ignorable' => true,
        ],
        'Potassium' => [
            'value' => 'Potassium (DevEX)',
            'uom' => 'Potassium UOM (DevEX)',
            'dv' => 'Potassium (% DV)',
            'ignorable' => true,
        ],
        'Vitamin C' => [
            'value' => 'Vitamin C (DevEX)',
            'uom' => 'Vitamin C UOM (DevEX)',
            'dv' => 'Vitamin C (% DV)',
            'ignorable' => true,
        ]
    ],
];