<?php
/** Un script de ejemplo
**/

include_once __DIR__ . '/../genesphp/task.php';
include_once __DIR__ . '/../genesphp/utils.php';
include_once __DIR__ . '/../genesphp/initiators.php';
include_once __DIR__ . '/../genesphp/algorithms.php';
include_once __DIR__ . '/../genesphp/mutators.php';
include_once __DIR__ . '/../genesphp/crossovers.php';
include_once __DIR__ . '/../genesphp/selectors.php';

function beale(&$task, &$genome) {
    return pow(1.5 - $genome[0] + ($genome[0] * $genome[1]), 2) +
           pow(2.25 - $genome[0] + ($genome[0] * pow($genome[1], 2)) , 2) +
           pow(2.625 - $genome[0] + ($genome[0] * pow($genome[1], 3)), 2);
}

function opti_func()
{
    // Se crean valores de configuración
    $n = 500;  // Individuos
    $vars = 2;
    $gen = 1000;  // Generaciones máximas
    $cp = 0.3;  // Probabilidad de cruza
    $max_mp = 1.0; // Máxima probabilidad de mutación
    $cycle_mp = 100; // Generaciónes por ciclo de mutación
    $elitism = 0.5;  // Porcentaje de elitismo
    $verbose = 50;

    // Calculamos una duración adecuada de ejecución (MAX 30 segundos):
    $duration = 5.0;

    // Se crea la tarea
    $task = new \genesphp\Task();

    // Se establece población;
    $struct = [[True, 5, 5],  # x
               [True, 5, 5]];  # y
    $the_pop = \genesphp\init_binary_pop($n, $struct);
    $task->set_population($the_pop);

    // Se establecen funciones de cruza, mutacion y selección
    $task->set_evals(['beale'], [-1.0]);
    $task->set_mutator('\\genesphp\\mutate_normal', ['mp' => 1.0, 'sd' => 5.0, 'integer' => false]);
    $task->set_crossover('\\genesphp\\crossover_one_point');
    $task->set_selector('\\genesphp\\select_tournament', ['cp' => $cp, 'k' => 50, 'matchs' => 100]);

    // Inicia el algoritmo
    $sol = \genesphp\cos_mutation_ga(
        $task,
        $max_mp,
        $cycle_mp,
        $elitism,
        $duration,
        $gen,
        $verbose
    );

    echo $sol;
}

opti_func();
