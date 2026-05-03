<?php
header('Content-Type: application/json');

$tips = [
    'dos' => [
        'Pay rent on time to build a good credit score.',
        'Report maintenance issues immediately to prevent further damage.',
        'Keep the property clean and sanitary.',
        'Respect your neighbors and noise ordinances.',
        'Read your lease agreement carefully.'
    ],
    'donts' => [
        'Do not sub-let without landlord permission.',
        'Do not make structural changes to the property.',
        'Do not ignore minor leaks or electrical issues.',
        'Do not exceed the maximum number of occupants.',
        'Do not paint walls without prior approval.'
    ]
];

echo json_encode($tips);
?>