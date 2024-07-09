<?php

namespace App\Services;

use App\Models\Product;



class ProductService
{
    public function importProductFromArray(int $id, array $data): array
    {
        $errors = [];
        if (empty($data['Prescription']['Product'])) {
            $errors[] = "No products found in XML";
            return $errors;
        }
        $products[] = $data['Prescription']['Product'];

        foreach ($products as $product) {
            Product::insert([
                'PrescriptionID' => $id,
                'GUID' => !empty($product['Guid']) ? trim($product['Guid']) : NULL,
                'Code' => trim($product['ProductCode']),
                'Description' => $product['Description'],
                'Instructions' => $product['Instructions'] ?? '',
                'Instructions2' => empty($product['Instructions2']) ? '' : $product['Instructions2'],
                'Quantity' => $product['ProductQuantity']['Quantity'],
                'Unit' => $product['ProductQuantity']['Units'],
                'Dosage' => $product['ProductQuantity']['Dosage'],
            ]);
        }

        return $errors;
    }
}
