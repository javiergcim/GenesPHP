<?php
/** An example script
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
    $gen = 200;  // Generaciones máximas
    $duration = 30.0;  // Duración en segundos
    $cp = 0.3;  // Probabilidad de cruza
    $max_mp = 0.05; // Máxima probabilidad de mutación
    $cycle_mp = 100.0; // Generaciónes por ciclo de mutación
    $elitism = 1.0;  // Porcentaje de elitismo
    $verbose = 10;

    // Se crea la tarea
    $task = new \genesphp\Task();

    // Se establece población;
    $struct = [[true, 5, 5],
               [true, 5, 5]];
    $the_pop = \genesphp\init_binary_pop($n, $struct);

    $task->set_population($the_pop);

    // Se establecen funciones de cruza, mutacion y selección
    $task->set_evals(['beale'], [-1.0]);
    $task->set_mutator('\\genesphp\\mutate_flip', ['mp' => $max_mp]);
    $task->set_crossover('\\genesphp\\crossover_one_point');
    $task->set_selector('\\genesphp\\select_vasconcelos', ['cp' => $cp]);

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

    echo $sol . "\n";
    echo 'Friendly genome: ' . print_r($sol->get_genome(), true);
}

opti_func();
