<?php

if (! function_exists('project_path')) {
    function project_path(string $path = ''): string
    {
        return $path !== '' ? base_path($path) : base_path();
    }
}
