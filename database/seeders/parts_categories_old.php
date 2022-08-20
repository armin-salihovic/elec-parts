<?php

$partCategories = [
    [
        'name' => 'IC',
        'children' => [
            ['name' => '74 Series'],
            ['name' => 'op amp'],
        ],
    ],
    [
        'name' => 'Transistors',
        'children' => [
            ['name' => '2N Series'],
            ['name' => 'BC Series'],
            ['name' => 'FETs & Mosfets'],
            ['name' => 'MJ Series'],
            ['name' => 'Other Transistors'],
            ['name' => 'PN Series'],
            ['name' => 'TIP Series'],
        ],
    ],
    [
        'name' => 'Diodes',
        'children' => [
            ['name' => 'Fast Recovery'],
            ['name' => 'Diac'],
            ['name' => 'Germanium'],
            ['name' => 'Schottky'],
            ['name' => 'Standard'],
            ['name' => 'Zener'],
            ['name' => 'Bridge Rectifier'],
            ['name' => 'Power Rectifiers'],
        ],
    ],
    [
        'name' => 'Capacitors',
        'children' => [
            ['name' => 'Ceramic Disc Capacitors'],
            ['name' => 'Monolithic Ceramic Capacitor'],
            ['name' => 'Electrolytic Capacitors'],
            ['name' => 'Polyester Mylar Film Capacitors'],
            ['name' => 'Polyester Film Box Type Capacitors'],
            ['name' => 'Tantalum Capacitors'],
            ['name' => 'Axial Polypropylene Film Capacitors'],
            [
                'name' => 'SMD Ceramic Chip Capacitors',
                'children' => [
                    ['name' => '0805'],
                    ['name' => '0603'],
                    ['name' => '1206'],
                    ['name' => '1210'],
                    ['name' => '0402'],
                ],
            ],
            ['name' => 'SMD Electrolytic Capacitors'],
            ['name' => 'Super Capacitors'],
            ['name' => 'Variable Trimmer Capacitors'],
            ['name' => 'Safety Capacitor'],
            ['name' => 'Fan Capacitor'],
            ['name' => 'Bipolar Electrolytic Capacitors'],
        ],
    ],
    [
        'name' => 'Hardware',
        'children' => [
            ['name' => 'Speaker Terminals'],
            ['name' => 'RCA'],
            ['name' => 'Enclosures'],
            ['name' => 'Heatsink'],
            ['name' => 'XLR Plugs & Sockets'],
            ['name' => '6.35mm - 1/4" Plugs & Jacks'],
            ['name' => '3.5mm Plugs & Jacks'],
            ['name' => 'DC Power'],
            ['name' => 'AC/DC Power Adapter'],
            ['name' => 'Prototyping Boards'],
            ['name' => 'Breadboards'],
            ['name' => 'Speakers'],
            ['name' => 'Knobs'],
            ['name' => 'Binding Posts'],
            ['name' => 'Banana Plugs'],
            ['name' => 'Alligator Clips'],
            ['name' => 'Measurement'],
            ['name' => 'Hand Tools'],
            ['name' => 'Cable / Wire / Heat Shrink Tubing'],
            ['name' => 'Thermal Electric Coolers'],
            ['name' => 'Nuts Washers Screws Spacers and More'],
            ['name' => 'AC Power'],
            ['name' => 'Packaging & Storage Materials'],
        ],
    ],
    [
        'name' => 'Resistors',
        'children' => [
            ['name' => '1/8W Metal Film Resistors'],
            ['name' => '1/4W Carbon Film Resistors'],
            ['name' => '1/4W Metal Film Resistors'],
            ['name' => '1/2W Carbon Film Resistors'],
            ['name' => '1/2W Metal Film Resistors'],
            ['name' => '1W Carbon Film Resistors'],
            ['name' => '1W Metal Film Resistors'],
            ['name' => '1W Metal Oxide Film Resistors'],
            ['name' => '2W Metal Oxide Film Resistors'],
            ['name' => '3W Metal Oxide Film Resistors'],
            ['name' => '5W Resistors'],
            [
                'name' => 'SMD Chip Resistors',
                'children' => [
                    ['name' => '0805'],
                    ['name' => '0603'],
                    ['name' => '1206'],
                    ['name' => '1210'],
                ],
            ],
            ['name' => 'Photoresistors'],
        ],
    ],
    [
        'name' => 'Potentiometers',
        'children' => [
            [
                'name' => 'Rotary Potentiometer',
                'children' => [
                    ['name' => 'Linear'],
                    ['name' => 'Logarithmic'],
                    ['name' => 'Anti-Log (Reverse)'],
                    ['name' => 'W-Taper'],
                    ['name' => 'Knobs'],
                    ['name' => 'MN Taper'],
                ],
            ],
            [
                'name' => 'Cermet Potentiometers',
                'children' => [
                    ['name' => '3006P'],
                    ['name' => '3296W'],
                    ['name' => '3362P'],
                ],
            ],
            [
                'name' => 'Trimmers',
                'children' => [
                    ['name' => '6mm Top Adjustment'],
                    ['name' => '6mm Side Adjustment'],
                ],
            ],
            ['name' => 'Knobs'],
            ['name' => 'Slide Potentiometers'],
        ],
    ],
    [
        'name' => 'LEDs',
        'children' => [
            ['name' => '3mm LEDs'],
            ['name' => '5mm LEDs'],
            ['name' => '10mm LEDs'],
            ['name' => '4.8mm LEDs'],
            ['name' => '8mm LEDs'],
        ]
    ],
];

// DatabaseSeeder::findCategoryIDinTree simplified this piece of code using recursion
foreach($nodes as $node) {
    if($node->name == $category[0]) {
        if(count($category) == 1) return $node->id;
        $level2 = $node->children;
        foreach($level2 as $node2) {
            if($node2->name == $category[1]) {
                if(count($category) == 2) return $node2->id;
                $level3 = $node2->children;
                foreach($level3 as $node3) {
                    if($node3->name == $category[2]) {
                        if(count($category) == 3) return $node3->id;
                    }
                }
            }
        }
    }
}
throw new \Exception("ERROR with finding the node");
