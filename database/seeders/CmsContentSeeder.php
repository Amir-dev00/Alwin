<?php

namespace Database\Seeders;

class CmsContentSeeder extends CmsSeeder
{
    /**
     * Add missing CMS settings/pages/blocks. Existing values are never overwritten.
     */
    public function run(): void
    {
        $this->seedSettings();
        $this->seedPages();
    }
}
