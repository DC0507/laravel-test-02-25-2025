<?php
/**
 * Website - Category/SubCategory
 * Website - Category Level 1
 * Website - Category/SubCategory/Flavor
 */
return [
    'units_of_measure' => [
        'GRAM' => 'g',
        'MILLIGRAM' => 'mg',
        'MICROGRAM_MCG' => 'mcg',
    ],
    'field_mappings' => [
        'slug_field' => [
//            'Website - Category/SubCategory/Flavor',
//            'Website - Product Label Name',
            'Website - Slug'
        ],
        'position' => '', // @todo define the position field (if exists)
        'name' => 'Website - Product Label Name',

        'product' => [
            'combined_cat_subcat_flavor' => 'Website - Category/SubCategory/Flavor',
            'category' => 'Website - Product Category',
            'flavor' => 'Website - Product Flavor',
//            'name' => 'Product Label Name (DevEX)',
            'name' => 'Website - Product Label Name',
            'description' => 'Product Short Bulleted Description',
            'category_position' => 'Category Position',
            'subcategory_position' => 'SubCategory Position',
            'brand_architecture' => 'Brand Architecture',
            'sizes' => [
                'size' => 'Consumer Sellable Unit Description',
                'description' => 'Product Short Bulleted Description',
                'material' => 'Consumable Level Packaging Material',
                'sku' => 'Welchs SKU',
                'upc' => 'UPC',
                'position' => 'Position', // @todo Define this Salsify field
                'ingredients' => 'Ingredients - Computed',
                'serving_size' => 'Serving Size Description - Manual',
                'calories' => 'Calories (DevEX)',
                'servings_per_container' => 'Serving Suggestion',
                'hero_image' => 'Website - Hero Image',
                'smartcommerce_id' => 'Website - Smart Commerce ID', // @todo define this Salsify field
                'primary_claims' => 'Claims - Primary Claims',
                'claims' => [
                    'claim_gluten_free' => 'Claims - Gluten Free',
                    'claim_cane_sugar' => 'Website - Made with Real Cane Sugar Claim', // no mapping
                    'claim_no_artificial_colors' => 'Claims - No Artificial Colors',
                    'claim_no_artificial_flavors' => 'Claims - No Artificial Flavors',
                    'claim_no_artificial_sweeteners' => 'Claims - No Artificial Sweeteners',
                    'claim_no_corn_syrup' => 'Claims - No High Fructose Corn Syrup',
                    'claim_no_preservatives' => 'Claims - No Preservatives',
                    'claim_no_sugar_added' => 'Claims - No Sugar Added',
                    'claim_less_sugar' => 'Claims - 50% Less Sugar Claim', 
                    'claim_favorite' => 'Claims - America 1 Grape Jelly or Jam',
                    'claim_made_usa' => 'Claims - Made in USA',
                    'claim_no_alcohol' => 'Claims - Non Alcoholic',
                    'claim_gmo' => 'Claims - Non GMO Project Verified',
                    'claim_usa_grapes' => 'Claims - USA Grown Grapes',
                    'claim_2_servings' => 'Claims - 2 Servings of Fruit per 8oz',
                    'claim_healthy_heart' => 'Claims - Helps Support a Healthy Heart',
                    'claim_excellent_vitamin_c' => 'Claims - Excellent Source of Vitamin C',
                    'claim_good_vitamin_c' => 'Claims - Good Source of Vitamin C',
                    'claim_good_calcium' => 'Claims - Good Source of Calcium',
                    'claim_good_fiber' => 'Claims - Good Source of Fiber',
                    'claim_wic' => 'Website - WIC Approved Claim', // no mapping
                    'claim_less_plastic' => 'Claims - 15% Less Plastic',
                    'claim_real_fruit' => 'Claims - Made with Real Fruit Juice',
                    'claim_usa_apples' => 'Claims - USA Grown Apples',
                    'claim_23rd_less_calories' => 'Claims - 2/3 Less Calories Claim', 
                    'claim_10_calories_per_8_oz' => 'Claims - 10 Calories per 8 oz',

                    // new claims
                    'claim_north_american_grown' => 'Claims - North American Grown',
                    'claim_not_from_concentrate' => 'Claims - Not from Concentrate',
                    'claim_colors_only_from_natural_sources' => 'Claims - Colors Only from Natural Sources',
                    'claim_non_gmo' => 'Claims - Non GMO',

                    // unknown claims
                    'claim_contains_sulfites' => 'Claims - Contains Sulfites',
                    'claim_keep_frozen' => 'Claims - Keep Frozen',
                    'claim_light' => 'Claims - Light',
                    'claim_made_with_unfiltered_juice' => 'Claims - Made with Unfiltered Juice',
                    'claim_natural' => 'Claims - Natural',
                    'claim_no_fdc_color' => 'Claims - No FD&C Color',
                    'claim_no_red40' => 'Claims - No Red 40',
                    'claim_frozen_concentrate' => 'Claims - Frozen Concentrate',
                    'claim_no_yellow_5' => 'Claims - No Yellow 5',
                    'claim_packed_peurto_rico' => 'Claims - Packed in Puerto Rico',
                    'claim_reduced_calorie' => 'Claims - Reduced Calorie',
                    'claim_reduced_sugar' => 'Claims - Reduced Sugar',
                    'claim_refrigerate' => 'Claims - Refrigerate After Opening',
                    'claim_serve_chilled' => 'Claims - Serve Well Chilled',
                    'claim_sulfite_free' => 'Claims - Sulfite Free',
                    'claim_sweetened_with_fruit' => 'Claims - Sweetened Only with Fruit Juice',

                ]
            ]
        ],
        'nutrition' => [
            'top_level_fields' => [
                'calories' => 'Calories',
                'servings_per_container' => 'Serving Suggestion',
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
                    'value' => 'Saturated Fat (DevEX)',
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
                'Total Carbohydrate' => [
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
                'Total Sugars' => [
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
                    'hide_when_empty' => true
                ]
            ],
        ],
    ],
    'storyblok' => [
        'space_id' => env('STORYBLOK_SPACE_ID'),
        'api_key' => env('STORYBLOK_API_KEY'),
        'mgmt_api_key' => env('STORYBLOK_MGMT_API_KEY'),
        'product_slug_prefix' => env('STORYBLOK_PRODUCT_SLUG_PREFIX'),
        'product_component_name' => env('STORYBLOK_PRODUCT_COMPONENT_NAME'),
    ]
];
