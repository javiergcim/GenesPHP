<?php
namespace genesphp;

/**
* Ejecuta una algoritmo genético genérico, con posibilidad de elitismo.
*
* @param Task $task Un objeto Task con los parámetros y la población requerida
* para la ejecución del algoritmo.
* @param float $elitismo Porcentaje de individuos que se guardarán como elite
* para la siguiente generación.
* @param int $sec Segundos que aproximadamente correrá el algoritmo.
* @param int $gen Generaciones que se ejecutara el algoritmo genético.
* @param int $verbose Indica cada cuantas generaciones se reportan avances.
*
* @return Individual El individuo con mejor aptitud al momento de finalizar la
* corrida.
**/
function general_ga(
    &$task,
    $elitism,
    $sec,
    $gen = INF,
    $verbose = INF
) {
    // Se inicia la toma de tiempo
    $start_time = \microtime(true);

    // Se precalcula el número de individuos elite
    $n_elite = \floor($task->get_size() * $elitism);

    $task->evaluate();
    $task->order_population();

    for ($g = 0; $g < $gen; $g++) {
        $task->set_generation($g);

        $elite_pop = $task->get_subpopulation_copy(0, $n_elite);
        $task->apply_selection();
        $task->mutate();
        $task->evaluate();
        $task->append_population($elite_pop, true);
        $task->remove_duplicate_fitness();
        if ($task->adjust_population_size()) {
            $task->evaluate();
            $task->order_population();
        }

        // Se verifica si se debe imprimir
        if ($verbose != INF) {
            if ($g % $verbose == 0) {
                echo 'Generation: ' . \strval($g) . "\n";
                echo 'Best fitness: ' .
                \implode(', ', $task->get_individual(0)->get_fitness()) . "\n\n";
            }
        }

        // Verificamos si se ha cumplido el tiempo
        $current_time = \microtime(true) - $start_time;
        if ($current_time > $sec) {
            break;
        }
    }

    $task->set_generation(null);

    // Se regresa la solución (el mejor es el primer elemento)
    return $task->get_individual(0);
}

/**
* Ejecuta una algoritmo genético genérico, con posibilidad de elitismo, que
* genera una probabilidad de mutación *$mp* variable a lo largo de las
* generaciones, de acuerdo una función coseno.
*
* @param Task $task Un objeto Task con los parámetros y la población requerida
* para la ejecución del algoritmo.
* @param float $max_mp Probabilidad máxima de mutación.
* @param float $cycle_mp Indica cuantas generaciones dura un ciclo en el
* cambio de valor de la propabilidad de mutación.
* @param float $elitismo Porcentaje de individuos que se guardarán como elite
* para la siguiente generación.
* @param int $sec Segundos que aproximadamente correrá el algoritmo.
* @param int $gen Generaciones que se ejecutará el algoritmo genético.
* @param int $verbose Indica cada cuantas generaciones se reportan avances.
* @param string $report Función de reporte. Recibirá la generación y mejor
*  fitness de la iteración actual, cada tantas generaciones como se especifique
*  según *verbose*.
*
* @return Individual El individuo con mejor aptitud al momento de finalizar la
* corrida.
**/
function cos_mutation_ga(
    &$task,
    $max_mp,
    $cycle_mp,
    $elitism,
    $sec,
    $gen = INF,
    $verbose = INF,
    $report = ''
) {
    // Se inicia la toma de tiempo
    $start_time = \microtime(true);

    // Ajustamos factores alusivos a mp variable
    $cycle_mp = (2.0 * M_PI) / $cycle_mp;
    $half_max_mp = $max_mp / 2.0;

    // Calculamos la probabilidad de mutación para ésta generación
    $task->set_mutator_arg('mp', $max_mp);

    // Se precalcula el número de individuos elite
    $n_elite = \floor($task->get_size() * $elitism);

    $task->evaluate();
    $task->order_population();

    for ($g = 0; $g < $gen; $g++) {
        $task->set_generation($g);

        // Calculamos la probabilidad de mutación para ésta generación
        $task->set_mutator_arg(
            'mp',
            (\cos($g * $cycle_mp) * $half_max_mp) + $half_max_mp);

        $elite_pop = $task->get_subpopulation_copy(0, $n_elite);

        $task->apply_selection();
        $task->mutate();
        $task->evaluate();
        $task->append_population($elite_pop, true);
        $task->remove_duplicate_fitness();
        if ($task->adjust_population_size()) {
            $task->evaluate();
            $task->order_population();
        }

        // Se verifica si se debe imprimir
        if ($verbose != INF) {
            if ($g % $verbose == 0) {
                if ($report != '') {
                    ($report)(
                        $g,
                        $task->get_individual(0)->get_fitness(),
                        $task->get_individual(0)->get_genome()
                    );
                } else {
                    echo 'Generation: ' . strval($g) . "\n";
                    echo 'Best fitness: ' .
                    \implode(
                        ', ',
                        $task->get_individual(0)->get_fitness()
                        ) . "\n\n";
                }
            }
        }

        // Verificamos si se ha cumplido el tiempo
        $current_time = \microtime(true) - $start_time;
        if ($current_time >= $sec) {
            break;
        }
    }

    // Se regresa la solución (el mejor es el primer elemento)
    return $task->get_individual(0);
}
