<?php

namespace GL\Core\Migration;

interface MigrationInterface 
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up();
    
    public function down();

    public function getUniqueTag();

    public function getCreationDate();
}
     