<?php

return [
    // Fields to exclude from forms and tables
    'disabled_fields' => ['id', 'created_at', 'updated_at', 'deleted_at'],

    // Readonly fields (e.g., `is_verified` should not be editable)
    'readonly_fields' => ['is_verified', 'status'],

    // Custom table headers (Rename fields for display)
    'table_header_names' => [
        'user_id' => 'User',
        'email' => 'Email Address',
        'created_at' => 'Date Created',
        'updated_at' => 'Last Updated'
    ],

    // Custom field labels for forms
    'form_labels' => [
        'name' => 'Full Name',
        'email' => 'Email Address',
        'phone' => 'Phone Number',
        'address' => 'Home Address'
    ],

    // Placeholder text for input fields
    'field_placeholders' => [
        'email' => 'Enter your email...',
        'phone' => 'Enter your phone number...'
    ]
];
