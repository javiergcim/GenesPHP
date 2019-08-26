<?php
namespace genesphp;

include_once 'utils.php';

/**
* Muta el individuo proporcionado in-situ, intercambiando de posición dos
* elementos de sus genomas cada vez. La cantidad de parejas intercambiadas
* estará en función de la probabilidad *mp*.
*
* Establece en *null* el fitness del individuo mutado.
*
* @param Task $task Una referencia a la tarea asociada al elemento (no usada).
* @param Individual $individual Un individuo.
* @param array $args Un arreglo con los parámetros propios de este método. *mp*
* como un número entre 0.0 y 1.0, que representa la probabilidad de que un
* elemento de $genome sea escogido para ser intercambiado por otro.
**/
function mutate_swap(&$task, &$individual, &$args)
{
    $mp = $args['mp'];
    $gen = $individual->get_raw_genome();
    $max_i = \count($gen) - 1;

    $j = geometric_dist($mp) - 1;  // Primer j (y nodo a intercambiar)
    while ($j <= $max_i) {
        // Elegimos al azar el nodo k
        $k = \mt_rand(0, $max_i);

        // Intercambiamos j y k
        $tmp = $gen[$j];
        $gen[$j] = $gen[$k];
        $gen[$k] = $tmp;
        $j += geometric_dist($mp);
    }

    $individual->set_genome_from_raw($gen);
    $individual->set_fitness(null);
}

/**
* Muta el individuo proporcionado in situ. Divide el genoma en tres
* subarreglos A, B, C. El operador hace al genoma A, C, B.
*
* Establece en *null* el fitness del individuo mutado.
*
* @param Task $task Una referencia a la tarea asociada al elemento (no usada).
* @param Individual $individual Un individuo.
* @param array $args Un arreglo con los parámetros propios de este método
* (no usados).
**/
function mutate_insert(&$task, &$individual, &$args)
{
    $gen = $individual->get_raw_genome();

    $max_i = \count($gen) - 1;
    $a = \mt_rand(0, $max_i);
    $b = \mt_rand(0, $max_i);

    if ($b < $a) {
        $tmp = $a;
        $a = $b;
        $b = $tmp;
    }

    $slice_a = \array_slice($gen, 0, $a);
    $slice_b = \array_slice($gen, $a, $b - $a);
    $slice_c = \array_slice($gen, $b);

    $gen = \array_merge($slice_a, $slice_c, $slice_b);

    $individual->set_genome_from_raw($gen);
    $individual->set_fitness(null);
}

/**
* Muta el individuo proporcionado in situ, elige al azar un operador de
* mutación de los especificados en la inicialización. Los parámetros de para
* cada operador de mutación se especificaron en la inicialización.
*
* Establece en null el fitness del individuo mutado.
*
* @param Task $task Una referencia a la tarea asociada al elemento.
* @param Individual $individual Un individuo.
* @param array $args Un arreglo con los parámetros propios de este método.
* 'operators': Un arreglo con las funciones de mutación. El resto de llaves se
* corresponderán con los parámetros dados a las funciones de mutación.
* Si más de un operador de mutación utilizan un argumento con el mismo
* nombre, será compartido entre ellos.
**/
function mutate_multiple(&$task, &$individual, &$args)
{
    $max_operators_index = \count($args['operators']) - 1;
    $operator_index = \mt_rand(0, $max_operators_index);

    ($args['operators'][$operator_index])($task, $individual, $args);
}

/**
* Muta el individuo proporcionado in situ, alterando de forma normal los valores
* de los genes elegidos al azar en el genoma.
*
* Establece en null el fitness del individuo mutado.
*
* @param Task $task Una referencia a la tarea asociada al elemento (no usada).
* @param Individual $individual Un individuo.
* @param array $args Un arreglo con los parámetros propios de este método.
* *mp* como la probabilidad de que un gen sea mutado, *sd* como la desviación
* estándar aplicada a la mutación, *integer*, si se desea que los valores
* que se establezcan en el genoma sean enteros.
**/
function mutate_normal(&$task, &$individual, &$args)
{
    $mp = $args['mp'];
    $sd = $args['sd'];
    $integer = $args['integer'];
    $gen = $individual->get_raw_genome();
    $max_i = \count($gen) - 1;

    $j = geometric_dist($mp) - 1;  // Primer j (y nodo a intercambiar)
    while ($j <= $max_i) {
        $mean = $gen[$j];
        $gen[$j] = gauss_dist($mean, $sd, $integer);

        $j += geometric_dist($mp);
    }

    $individual->set_genome_from_raw($gen);
    $individual->set_fitness(null);
}
