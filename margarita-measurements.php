<?php
/*
Plugin Name: Margarita Measurements
Description: Calculates measurements for making a margarita cocktail
Version: 1.0
*/

function margarita_measurements_shortcode() {
    $tequila = 1.5;
    $lime_juice = 0.75;
    $lemon_juice = 0.25;
    $cointreau = 0.5;
    $salt = 'A pinch';
    $ice = 'As needed';
    
    $output = "<ul>
                    <li>Tequila: {$tequila} shots</li>
                    <li>Lime juice: {$lime_juice} shots</li>
                    <li>Lemon juice: {$lemon_juice} shots</li>
                    <li>Cointreau: {$cointreau} shots</li>
                    <li>Salt: {$salt}</li>
                    <li>Ice: {$ice}</li>
                </ul>";
                
    return $output;
}
add_shortcode('margarita_measurements', 'margarita_measurements_shortcode');
?>
