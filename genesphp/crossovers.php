<?php
namespace genesphp;

/**
* Hace un cruzamiento de genomas que codifican una permutación de destinos
* para el problema del agente viajero, inspirado en SCX.
*
* El algoritmo genera dos hijos. Uno calculado de derecha a izquierda y otro de
* izquierda a derecha.
*
* Se asume que el primer objetivo de la tarea asociada determina si el problema
* se maximiza o minimiza.
*
* Ahmed, Z. H. (2010). Genetic algorithm for the traveling salesman problem
* using sequential constructive crossover operator. International Journal of
* Biometrics & Bioinformatics (IJBB), 3(6), 96.
*
* *Asunciones:*
*
* Se asume que el genoma no posee elementos repetidos, que el primer
* elemento del recorrido (la salida), no se encuentra en el genoma y es fijo.
*
* @param Task $task El objeto Task asociado al problema.
* @param Individual $ind_a El primer individuo a cruzar.
* @param Individual $ind_b El segundo individuo a cruzar.
* @param array $args Los parámetros propios del método.
*
* @return array Un arreglo con dos individuos descendientes.
**/

function crossover_scx(&$task, &$ind_a, &$ind_b, &$args)
{
    // Tomamos las variables requeridas de la tarea
    $data = $task->get_data();
    $cost = $data['cost'];
    $start = $data['start'];
    $circuit = $data['circuit'];
    if ($task->get_obj_factors()[0] > 0.0) {
        $minim = false;
    } else {
        $minim = true;
    }

    // Iniciamos el proceso de cruza
    $gen_a = $ind_a->get_raw_genome();
    $gen_b = $ind_b->get_raw_genome();

    // Tamaño de los genomas (todos miden lo mismo)
    $size = \count($gen_a);
    $size_1 = $size - 1;

    // Creamos arreglos con elementos de $gen_a y $gen_b como sus llaves, y
    // sus llaves como valores, a fin de encontrarlos rápido.
    $map_a = \array_flip($gen_a);
    $map_b = \array_flip($gen_b);

    // Conjuntos que nos indican los elementos legítimos de cada hijo.
    $legal_nodes_l = $map_a;
    $legal_nodes_r = $legal_nodes_l;

    // Inicio de hijo izquierdo (son_l) ----------------------------------------

    // Se inicializa el genoma del futuro hijo
    $son_l_gen = [];

    // Se calcula el costo para el elemento apuntalado (la salida)
    $cost_a = $cost[$start][$gen_a[0]];
    $cost_b = $cost[$start][$gen_b[0]];

    // Se elige el nodo que se agregará al genoma del hijo
    if (($cost_a < $cost_b) xor $minim) {
        $last_added_l = $gen_b[0];
    } else {
        $last_added_l = $gen_a[0];
    }
    $son_l_gen[] = $last_added_l;

    // Se elimina de los elementos legales el elemento agregado al hijo
    unset($legal_nodes_l[$last_added_l]);

    // Inicio de hijo derecho (son_r) ------------------------------------------

    // Se inicializa el genoma del futuro hijo
    $son_r_gen = \array_fill(0, $size, null);

    // Inicializamos posición
    $current_index = $size_1;

    // Se calcula el costo para el elemento meta
    $cost_a = $cost[\end($gen_a)][$start];
    $cost_b = $cost[\end($gen_b)][$start];

    if ($circuit) {  // Elegimos último nodo más cercano a la salida
        if (($cost_a < $cost_b) xor $minim) {
            $last_added_r = \end($gen_b);
        } else {
            $last_added_r = \end($gen_a);
        }
    } else {  // Elegimos último nodo más lejano a la salida de los padres
        if (($cost_a > $cost_b) xor $minim) {
            $last_added_r = \end($gen_b);
        } else {
            $last_added_r = \end($gen_a);
        }
    }

    $son_r_gen[$current_index] = $last_added_r;
    // Se elimina de los elementos legales el elemento agregado al hijo
    unset($legal_nodes_r[$last_added_r]);
    $current_index--;

    // Ciclo principal. Se agrega hasta que no haya que agregar
    while ($current_index >= 0) {
        // Sección de hijo izquierdo --------

        // Buscamos el nodo legal en 'a' ---
        $not_found = true;
        for ($i = $map_a[$last_added_l] + 1; $i < $size; $i++) {
            if (\array_key_exists($gen_a[$i], $legal_nodes_l)) {
                $candidate_a = $gen_a[$i];
                $not_found = false;
                break;
            }
        }

        // No se encontró nodo legal en 'a' después del último añadido
        if ($not_found) {
            foreach ($gen_a as $i) {
                if (\array_key_exists($i, $legal_nodes_l)) {
                    $candidate_a = $i;
                    break;
                }
            }
        }

        $cost_a = $cost[$last_added_l][$candidate_a];

        // Buscamos el nodo legal en 'b' ---
        $not_found = true;
        for ($i = $map_b[$last_added_l] + 1; $i < $size; $i++) {
            if (\array_key_exists($gen_b[$i], $legal_nodes_l)) {
                $candidate_b = $gen_b[$i];
                $not_found = false;
                break;
            }
        }

        // No se encontró nodo legal en 'b' después del último añadido
        if ($not_found) {
            foreach ($gen_b as $i) {
                if (\array_key_exists($i, $legal_nodes_l)) {
                    $candidate_b = $i;
                    break;
                }
            }
        }

        $cost_b = $cost[$last_added_l][$candidate_b];

        // Se elige el nodo que se agregará al genoma del hijo
        if (($cost_a < $cost_b) xor $minim) {
            $last_added_l = $candidate_b;
        } else {
            $last_added_l = $candidate_a;
        }
        $son_l_gen[] = $last_added_l;

        // Se elimina de los elementos legales el elemento agregado al hijo
        unset($legal_nodes_l[$last_added_l]);

        // Sección de hijo derecho --------

        // Buscamos el nodo legal en 'a' ---
        $not_found = true;
        for ($i = $map_a[$last_added_r] - 1; $i >= 0; $i--) {
            if (\array_key_exists($gen_a[$i], $legal_nodes_r)) {
                $candidate_a = $gen_a[$i];
                $not_found = false;
                break;
            }
        }

        // No se encontró nodo legal en 'a' después del último añadido
        if ($not_found) {
            foreach ($gen_a as $i) {
                if (\array_key_exists($i, $legal_nodes_r)) {
                    $candidate_a = $i;
                    break;
                }
            }
        }

        $cost_a = $cost[$candidate_a][$last_added_r];

        // Buscamos el nodo legal en 'b' ---
        $not_found = true;
        for ($i = $map_b[$last_added_r] - 1; $i >= 0; $i--) {
            if (\array_key_exists($gen_b[$i], $legal_nodes_r)) {
                $candidate_b = $gen_b[$i];
                $not_found = false;
                break;
            }
        }

        // No se encontró nodo legal en 'b' después del último añadido
        if ($not_found) {
            foreach ($gen_b as $i) {
                if (\array_key_exists($i, $legal_nodes_r)) {
                    $candidate_b = $i;
                    break;
                }
            }
        }

        $cost_b = $cost[$candidate_b][$last_added_r];

        // Se elige el nodo que se agregará al genoma del hijo
        if (($cost_a < $cost_b) xor $minim) {
            $last_added_r = $candidate_b;
        } else {
            $last_added_r = $candidate_a;
        }
        $son_r_gen[$current_index] = $last_added_r;

        // Se elimina de los elementos legales el elemento agregado al hijo
        unset($legal_nodes_r[$last_added_r]);
        $current_index--;
    }

    // Se instancias los hijos
    $a = clone($ind_a);
    $a->set_genome_from_raw($son_l_gen);
    $a->set_fitness(null);
    $b = clone($ind_b);
    $b->set_genome_from_raw($son_r_gen);
    $b->set_fitness(null);

    return [$a, $b];
}

/**
* Hace un cruzamiento de genomas que codifican una permutación, inspirado
* en SCX.
*
* Esta versión no considera una matriz de costos. Los nodos son insertados
* siguiendo un esquema determinista.
*
* El algoritmo genera dos hijos. Uno calculado de derecha a izquierda y otro de
* izquierda a derecha.
*
* Se asume que el primer objetivo de la tarea asociada determina si el problema
* se maximiza o minimiza.
*
* Ahmed, Z. H. (2010). Genetic algorithm for the traveling salesman problem
* using sequential constructive crossover operator. International Journal of
* Biometrics & Bioinformatics (IJBB), 3(6), 96.
*
* *Asunciones:*
*
* Se asume que el genoma no posee elementos repetidos, que el primer
* elemento del recorrido (la salida), no se encuentra en el genoma y es fijo.
*
* @param Task $task El objeto Task asociado al problema.
* @param Individual $ind_a El primer individuo a cruzar.
* @param Individual $ind_b El segundo individuo a cruzar.
* @param array $args Los parámetros propios del método.
*
* @return array Un arreglo con dos individuos descendientes.
**/

function crossover_pseudoscx(&$task, &$ind_a, &$ind_b, &$args)
{
    // Iniciamos el proceso de cruza
    $gen_a = $ind_a->get_raw_genome();
    $gen_b = $ind_b->get_raw_genome();

    // Tamaño de los genomas (todos miden lo mismo)
    $size = \count($gen_a);
    $size_1 = $size - 1;

    // Creamos arreglos con elementos de $gen_a y $gen_b como sus llaves, y
    // sus llaves como valores, a fin de encontrarlos rápido.
    $map_a = \array_flip($gen_a);
    $map_b = \array_flip($gen_b);

    // Conjuntos que nos indican los elementos legítimos de cada hijo.
    $legal_nodes_l = $map_a;
    $legal_nodes_r = $legal_nodes_l;

    // Inicio de hijo izquierdo (son_l) ----------------------------------------

    // Se inicializa el genoma del futuro hijo
    $last_added_l = $gen_a[0];
    $son_l_gen = [$last_added_l];

    // Se elimina de los elementos legales el elemento agregado al hijo
    unset($legal_nodes_l[$last_added_l]);

    // Inicio de hijo derecho (son_r) ------------------------------------------

    // Se inicializa el genoma del futuro hijo
    $last_added_r = end($gen_a);
    $son_r_gen = \array_fill(0, $size, null);
    $son_r_gen[$size_1] = $last_added_r;

    // Inicializamos posición
    $current_index = $size_1 - 1;

    // Se elimina de los elementos legales el elemento agregado al hijo
    unset($legal_nodes_r[$last_added_r]);

    // Ciclo principal. Se agrega hasta que no haya que agregar
    $next_source = 'a';
    while ($current_index >= 0) {
        if ($next_source == 'a') {
            $next_source = 'b';
        }
        else {
            $next_source = 'a';
        }

        // Sección de hijo izquierdo --------

        if ($next_source == 'a') {
            // Buscamos el nodo legal en 'a' ---
            $not_found = true;
            for ($i = $map_a[$last_added_l] + 1; $i < $size; $i++) {
                if (\array_key_exists($gen_a[$i], $legal_nodes_l)) {
                    $last_added_l = $gen_a[$i];
                    $not_found = false;
                    break;
                }
            }

            // No se encontró nodo legal en 'a' después del último añadido
            if ($not_found) {
                foreach ($gen_a as $i) {
                    if (\array_key_exists($i, $legal_nodes_l)) {
                        $last_added_l = $i;
                        break;
                    }
                }
            }
        }
        else {
            // Buscamos el nodo legal en 'b' ---
            $not_found = true;
            for ($i = $map_b[$last_added_l] + 1; $i < $size; $i++) {
                if (\array_key_exists($gen_b[$i], $legal_nodes_l)) {
                    $last_added_l = $gen_b[$i];
                    $not_found = false;
                    break;
                }
            }

            // No se encontró nodo legal en 'b' después del último añadido
            if ($not_found) {
                foreach ($gen_b as $i) {
                    if (\array_key_exists($i, $legal_nodes_l)) {
                        $last_added_l = $i;
                        break;
                    }
                }
            }
        }

        $son_l_gen[] = $last_added_l;

        // Se elimina de los elementos legales el elemento agregado al hijo
        unset($legal_nodes_l[$last_added_l]);

        // Sección de hijo derecho --------

        if ($next_source == 'a') {
            // Buscamos el nodo legal en 'a' ---
            $not_found = true;
            for ($i = $map_a[$last_added_r] - 1; $i >= 0; $i--) {
                if (\array_key_exists($gen_a[$i], $legal_nodes_r)) {
                    $last_added_r = $gen_a[$i];
                    $not_found = false;
                    break;
                }
            }

            // No se encontró nodo legal en 'a' después del último añadido
            if ($not_found) {
                foreach ($gen_a as $i) {
                    if (\array_key_exists($i, $legal_nodes_r)) {
                        $last_added_r = $i;
                        break;
                    }
                }
            }
        }
        else {
            // Buscamos el nodo legal en 'b' ---
            $not_found = true;
            for ($i = $map_b[$last_added_r] - 1; $i >= 0; $i--) {
                if (\array_key_exists($gen_b[$i], $legal_nodes_r)) {
                    $last_added_r = $gen_b[$i];
                    $not_found = false;
                    break;
                }
            }

            // No se encontró nodo legal en 'b' después del último añadido
            if ($not_found) {
                foreach ($gen_b as $i) {
                    if (\array_key_exists($i, $legal_nodes_r)) {
                        $last_added_r = $i;
                        break;
                    }
                }
            }
        }

        $son_r_gen[$current_index] = $last_added_r;

        // Se elimina de los elementos legales el elemento agregado al hijo
        unset($legal_nodes_r[$last_added_r]);
        $current_index--;
    }

    // Se instancias los hijos
    $a = clone($ind_a);
    $a->set_genome_from_raw($son_l_gen);
    $a->set_fitness(null);
    $b = clone($ind_b);
    $b->set_genome_from_raw($son_r_gen);
    $b->set_fitness(null);

    return [$a, $b];
}

/**
* Realiza un cruzamiento de dos individuos, mezclando los genomas aplicando un
* corte.
*
* @param Task $task El objeto Task asociado al problema.
* @param Individual $ind_a El primer individuo a cruzar.
* @param Individual $ind_b El segundo individuo a cruzar.
*
* @return array Un arreglo con dos Individuos descendentes
**/
function crossover_one_point(&$task, &$ind_a, &$ind_b, &$args)
{
    // Elegimos al azar el punto de corte
    $gen_a = $ind_a->get_raw_genome();
    $gen_b = $ind_b->get_raw_genome();

    $cut_point = \mt_rand(1, \count($gen_a) - 1);
    $left_a = \array_slice($gen_a, 0, $cut_point);
    $right_a = \array_slice($gen_a, $cut_point);

    $left_b = \array_slice($gen_b, 0, $cut_point);
    $right_b = \array_slice($gen_b, $cut_point);

    $son_l_gen = \array_merge($left_a, $right_b);
    $son_r_gen = \array_merge($left_b, $right_a);

    // Se instancian los hijos
    $a = clone($ind_a);
    $a->set_genome_from_raw($son_l_gen);
    $a->set_fitness(null);
    $b = clone($ind_b);
    $b->set_genome_from_raw($son_r_gen);
    $b->set_fitness(null);

    return [$a, $b];
}

/**
* Realiza un cruzamiento de dos individuos, mezclando los genomas aplicando dos
* cortes.
*
* @param Task $task El objeto Task asociado al problema.
* @param Individual $ind_a El primer individuo a cruzar.
* @param Individual $ind_b El segundo individuo a cruzar.
*
* @return array Un arreglo con dos Individuos descendentes
**/
function crossover_two_points(&$task, &$ind_a, &$ind_b, &$args)
{
    // Elegimos al azar el punto de corte
    $gen_a = $ind_a->get_raw_genome();
    $gen_b = $ind_b->get_raw_genome();

    $cut_a = \mt_rand(0, \count($gen_a) - 1);
    $cut_b = \mt_rand(0, \count($gen_a) - 1);

    if ($cut_a > $cut_b) {
        $temp = $cut_a;
        $cut_a = $cut_b;
        $cut_b = $temp;
    }

    $left_a = \array_slice($gen_a, 0, $cut_a);
    $center_a = \array_slice($gen_a, $cut_a, $cut_b);
    $right_a = \array_slice($gen_a, $cut_b);

    $left_b = \array_slice($gen_b, 0, $cut_a);
    $center_b = \array_slice($gen_b, $cut_a, $cut_b);
    $right_b = \array_slice($gen_b, $cut_b);

    $son_l_gen = \array_merge($left_a, $center_b, $right_a);
    $son_r_gen = \array_merge($left_b, $center_a, $right_b);

    // Se instancian los hijos
    $a = clone($ind_a);
    $a->set_genome_from_raw($son_l_gen);
    $a->set_fitness(null);
    $b = clone($ind_b);
    $b->set_genome_from_raw($son_r_gen);
    $b->set_fitness(null);

    return [$a, $b];
}
