<?php

declare(strict_types=1);

function renderView(string $template, array $data = []):void
{
    if (!empty($data)) {
        extract($data, EXTR_SKIP);
    }

    include TEMPLATES_DIR . '/' . $template . '.php';
}   
?>