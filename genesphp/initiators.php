<?php
namespace genesphp;

include_once 'individual.php';
include_once 'binaryind.php';
include_once 'utils.php';

/**
* Crea una población de tamaño *$n* de individuos con un genoma que almacena
* permutaciones. Los elementos a permutar se establecen en $elements.
*
* @param int $n Cantidad de individuos a crear.
* @param array $elements Un arreglo con los elementos a permutar.
*
* @return array La población.
**/
function init_permutation_pop($n, $elements)
{
    # Se forzan al menos dos individuos
    if ($n < 2) {
        $n = 2;
    }

    $new_pop = [];
    for ($i = 0; $i < $n; $i++) {
        $genome = $elements;
        fisher_yates($genome);
        $new_pop[] = new Individual($genome);
    }

    return $new_pop;
}

/**
* Crea una población de tamaño *$n* de individuos con un genoma que almacena
* números flotantes.
*
* @param int $n Cantidad de individuos a crear.
* @param int $numbers Cantidad de números almacenados en un genoma.
* @param float $min Valor mínimo del rango del cual se tomarán los números.
* @param float $max Valor máximo del rango del cual se tomarán los números.
*
* @return array La población.
**/
function init_float_pop($n, $numbers, $min, $max)
{
    # Se forzan al menos dos individuos
    if ($n < 2) {
        $n = 2;
    }

    $new_pop = [];
    for ($i = 0; $i < $n; $i++) {
        $genome = [];
        for($j = 0; $j < $numbers; $j++) {
            $range = $max - $min;
            $genome[] =
                (((float) \mt_rand() / (float) \mt_getrandmax()) * $range) +
                $min;
        }
        $new_pop[] = new Individual($genome);
    }

    return $new_pop;
}

/**
* Crea una población de tamaño *n* de individuos con un genoma que
* almacena variables codificadas en binario.
*
* @param int $n Cantidad de individuos a crear.
* @param array $structure Un arreglo que especifica cómo se codificarán las
* variables en el genoma. Para cada variavle hay un elemento. A su
* vez, cada elemento posee otros tres, que indicarán, en este
* orden: bit de signo, bits de parte entera, bits de mantisa.
*
* *Ejemplo*
*
* Dos varables, la primera con bit de signo, diez bits para almacenar la
* parte entera, y cinco para la parte decimal. La segunda variable no
* posee signo, trece bits para la parte entera y cero para la mantisa.
*
* [[true, 10, 5], [false, 13, 0]]
*
* @return array La población.
**/
function init_binary_pop($n, $structure)
{
    // Se forzan al menos dos individuos
    if ($n < 2) {
        $n = 2;
    }

    // Se calculan atributos de aceleración de cálculo con base en estructura
    $total_bits = 0;
    $var_bits = [];
    $sign_bits = [];
    $precalc = [];
    $n_vars = \count($structure);
    foreach ($structure as $i) {
        $current_bits = \array_sum($i);
        $total_bits += $current_bits;
        $var_bits[] = $current_bits;
        $sign_bits[] = $i[0];
        $precalc[] = 1.0 / (2**$i[2]);
    }

    // Se crean los individuos
    $vault = ['0', '1'];
    $new_pop = [];
    for ($i = 0; $i < $n; $i++) {
        $genome = [];
        for ($j = 0; $j < $total_bits; $j++) {
            $genome[] = $vault[\mt_rand(0, 1)];
        }
        $new_pop[] = new BinaryInd($genome,
                                   $var_bits,
                                   $sign_bits,
                                   $precalc,
                                   $structure);
    }

    return $new_pop;
}
