<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class EnergyTransactionCollectionController
{
    public function __invoke(Request $request): iterable
    {
        // API Platform's provider (Doctrine + filters + pagination)
        // stores the collection result in the "data" request attribute.
        $data = $request->attributes->get('data');

        if (!is_iterable($data)) {
            // Safety fallback – should not happen if the operation is configured correctly
            return [];
        }

        // Optionally post-process items here (e.g. wrap to DTOs)
        return $data;
    }
}
