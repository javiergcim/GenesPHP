<?php
namespace genesphp;

include_once 'utils.php';

/**
* Realiza cruzas con el método Vasconcelos, con probabilidad *$cp*. Los cambios
* en la población son realizados in-situ en el objeto Task pasado como
* referencia. Los hijos sustituyen a los padres.
*
* Se asume que hay definida una función de cruza que regresa dos descendientes.
*
* @param Task $task Una referencia la tarea invoulcrada.
* @param array $args Un arreglo con los argumentos propios de la función. *cp*
* indica la propabilidad de cruzamiento.
**/
function select_vasconcelos(&$task, &$args)
{
    $cp = $args['cp'];
    $mayor = $task->get_size() - 1;
    $max_minor = \floor($task->get_size() / 2);
    $population = $task->get_population();

    // Hacemos la selección (mejor contra peor)
    for ($minor = 0; $minor <= $max_minor; $minor++) {
        // Si el dado favorece, se hace la cruza
        $p = (float) \mt_rand() / (float) \mt_getrandmax();
        if ($p < $cp) {
            $childs = $task->apply_crossover(
                $population[$minor],
                $population[$mayor]
            );
            $population[$minor] = $childs[0];
            $population[$mayor] = $childs[1];
        }
        $mayor--;
    }

    $task->replace_population($population);
}

/**
* Realiza cruzas utilizando el método de selección por torneo, eligiendo dos
* padres para cada cruza, y anexando al final los descendientes resultantes a la
* población actual de la tarea.
*
* Se asume que hay definida una función de cruza que regresa dos descendientes.
*
* @param Task $task Una referencia la tarea invoulcrada.
* @param array $args Un arreglo con los argumentos propios de la función. 'k'
* indica el número de individuos en el torneo.
**/
function select_tournament(&$task, &$args)
{
    $k = $args['k'];
    if (\array_key_exists('obj_index', $args)) {
        $obj_index = $args['obj_index'];
    }
    else {
        $obj_index = 0;
    }
    $population = $task->get_population();
    $pop_max_i = \count($population) - 1;
    $p_type = $task->get_obj_factors($obj_index);
    $matchs = $args['matchs'];

    for ($pair = 0; $pair < $matchs; $pair++) {
        // Elegimos padres
        $p = 0;
        $parents_index = [];
        while ($p < 2) {
            // Hago el torneo
            $tournament = sample_for_range(0, $pop_max_i, $k);
            $best = null;
            $best_i = null;
            foreach ($tournament as $i) {
                $current_fitness = $population[$i]->get_fitness($obj_index);
                // Verificamos si es maximización o minimización
                if ($p_type > 0.0) {
                    if ($best == null || $current_fitness > $best) {
                        $best = $current_fitness;
                        $best_i = $i;
                    }
                }
                else {
                    if ($best == null || $current_fitness < $best) {
                        $best = $current_fitness;
                        $best_i = $i;
                    }
                }
            }

            $parents_index[] = $best_i;
            $p++;
        }

        $childs = $task->apply_crossover(
            $population[$parents_index[0]],
            $population[$parents_index[1]]
        );
        $population[$parents_index[0]] = $childs[0];
        $population[$parents_index[1]] = $childs[1];
    }

    $task->replace_population($population);
}
