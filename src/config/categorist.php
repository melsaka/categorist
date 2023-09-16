<?php

return [

    /*
     *
     * all categories are stored in categories table by default.
     * you can change default table and customize its name.
     * this table will be migrated when you call `migrate`
     * artisan command: "php artisan migrate".
     *
     */

    'table' => 'categories',
    
    'morph_table' => 'categorized',
];