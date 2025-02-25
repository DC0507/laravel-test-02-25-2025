<?php
return [
    'contactform' => [
        'recipient' => env('CONTACTFORM_RECIPIENT', 'justin@hellodesign.com'),

        'astute_mapping' => [
            'salutation' => 'Title',
            'first-name' => 'First Name',
            'last-name' => 'Last Name',
            'address1' => 'Address 1',
            'address2' => 'Address 2',
            'city' => 'City',
            'state' => 'State',
            'zip' => 'Zip',
            'email' => 'Email',
            'dob' => 'Date of birth',
            '0-3' => 'Age of children 0-3',
            '4-6' => 'Age of children 4-6',
            '7-9' => 'Age of children 7-9',
            '10-12' => 'Age of children 10-12',
            '13-17' => 'Age of children 13-17',
            '18+' => 'Age of children 18+',
            'contact-type' => 'Subject',
            //Will you continue to purchase this product
            'product-name' => 'Product Name',
            'product-size' => 'Product Size',
            'upc-code' => 'UPC Code',
            'lot-code' => 'Lot Code',
            'store-purchased-from' => 'Store Purchased',
            'receive-promotions' => 'Send Promotions',
            'number-products-purchased' => 'number products purchased',
            //Which of the following would you be interested in hearing more about from Welch's
            'receive-coupons' => 'Coupons',
            'receive-promotions' => 'Promotions',
            'receive-recipes' => 'Recipes',
            'receive-nutritional-news' => 'Nutritional News',
            'receive-other' => 'Other',
            'receive-other-description' => 'Other Description',
            'message' => 'Inqury', // [sic] on purpose
        ],

        'validation' => [
            'contact-type' => [
                'required',
                'in:Comment,Inquiry,Concern',
            ],
            'message' => [
                'required',
                'max:300',
            ],
            'salutation' => [
                'sometimes',
                'in:,Mr.,Ms.,Mrs.,Dr.'
            ],
            'first-name' => [
                'required',
                'max:48',
            ],
            'last-name' => [
                'required',
                'max:48',
            ],
            'address1' => [
                'sometimes',
                'max:128',
            ],
            'address2' => [
                'sometimes',
                'max:128',
            ],
            'city' => [
                'sometimes',
                'max:128',
            ],
            'state' => [
                'sometimes',
                'max:2',
                // @todo validate against array of valid states
            ],
            'zip' => [
                'sometimes',
                'nullable',
                'regex:/^[0-9]{5}$/',
            ],
            'dob' => [
                'sometimes',
                'max:24'
            ],
            'email' => [
                'required',
                'email',
            ],
            'email2' => [
                'required',
                'email',
                'same:email',
            ],
            'number-products-purchased' => [
                'nullable',
                'in:none,1-2,3-5,6-more',
            ],
//            'ages-children' => [
//                'array',
//            ],
            'ages-children.*' => [
                'nullable',
                'in:,no-children,0-3,4-6,7-9,10-12,13-17,18+'
            ],
            'product-name' => [
                'sometimes',
                'max:100'
            ],
            'product-size' => [
                'sometimes',
                'max:100'
            ],
            'upc-code' => [
                'sometimes',
                'max:100'
            ],
            'lot-code' => [
                'sometimes',
                'max:100'
            ],
            'store-purchased-from' => [
                'sometimes',
                'max:100'
            ],
            'opt-in' => [
                'sometimes',
                'in:,1',
            ],
            'receive-coupons' => [
                'sometimes',
                'in:,1',
            ],
            'receive-promotions' => [
                'sometimes',
                'in:,1',
            ],
            'receive-recipes' => [
                'sometimes',
                'in:,1',
            ],
            'receive-nutritional-news' => [
                'sometimes',
                'in:,1',
            ],
            'receive-other' => [
                'sometimes',
                'in:,1',
            ],
            'receive-other-description' => [
                'required_if:other,1',
                'max:300',
            ],

        ]
    ],
    'subscribeform' => [
        'recipient' => env('SUBSCRIBEFORM_RECIPIENT', 'justin@hellodesign.com'),
        'validation' => [
            'email' => [
                'required',
                'email',
                'bail',
            ]
        ]
    ],
];