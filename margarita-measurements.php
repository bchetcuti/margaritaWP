<?php
/*
Plugin Name: Margarita Measurements
Description: Calculates measurements for making a margarita cocktail
Version: 1.1
*/

function margarita_measurements_shortcode() {
    $drinks = isset($_POST['drinks']) ? intval($_POST['drinks']) : 1;
    $tequila = $drinks * 1.5;
    $lime_juice = $drinks * 0.75;
    $lemon_juice = $drinks * 0.25;
    $cointreau = $drinks * 0.5;
    $salt = 'A pinch';
    $ice = 'As needed';

    $output = '<form method="POST">';
    $output .= '<label for="drinks">How many drinks would you like to make?</label>';
    $output .= '<input type="number" name="drinks" value="' . $drinks . '" min="1" max="10">';
    $output .= '<button type="submit">Calculate</button>';
    $output .= '</form>';

    $output .= '<ul>';
    $output .= '<li>Tequila: ' . $tequila . ' shots</li>';
    $output .= '<li>Lime juice: ' . $lime_juice . ' shots</li>';
    $output .= '<li>Lemon juice: ' . $lemon_juice . ' shots</li>';
    $output .= '<li>Cointreau: ' . $cointreau . ' shots</li>';
    $output .= '<li>Salt: ' . $salt . '</li>';
    $output .= '<li>Ice: ' . $ice . '</li>';
    $output .= '</ul>';

    return $output;
}
add_shortcode('margarita_measurements', 'margarita_measurements_shortcode');
?>
