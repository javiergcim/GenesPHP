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

/**
* Función de evaluación de ejemplo.
*
* @param Task $task La tarea asociada al problema.
* @param array $args Un arreglo con los argumentos codificados en el genoma.
*
* @return float El fitness asociado a los argumentos proporcionados.
**/
function example_evaluation_function(&$genome, &$data) {
    return pow(1.5 - $genome[0] + ($genome[0] * $genome[1]), 2) +
           pow(2.25 - $genome[0] + ($genome[0] * pow($genome[1], 2)) , 2) +
           pow(2.625 - $genome[0] + ($genome[0] * pow($genome[1], 3)), 2);
}

/**
* Esta función prepara la tarea y ejecuta el algoritmo genético.
**/
function my_example()
{
    // Se crean valores de configuración
    $n = 500;  // Individuos
    $gen = 50;  // Generaciones máximas
    $cp = 0.3;  // Probabilidad de cruza
    $max_mp = 0.05; // Máxima probabilidad de mutación
    $cycle_mp = 100.0; // Generaciónes por ciclo de mutación
    $elitism = 1.0;  // Porcentaje de elitismo
    $duration = INF;  // Duración máxima en segundos
    $verbose = 10;  // Frecuencia de reporte

    // Se crea la tarea
    $task = new \genesphp\Task();

    // Se crea y asigna la población inicial
    // Two individuals [sign bit (bool), integer bits (int), matissa bits (int)]
    $struct = [[true, 5, 5],   // x
               [true, 5, 5]];  // y
    $the_pop = \genesphp\init_binary_pop($n, $struct);
    $task->set_population($the_pop);

    // Se establecen funciones de cruza, mutacion y selección
    $task->set_evals(['example_evaluation_function'], [-1.0]);
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
    echo "Friendly genome:\n" . print_r($sol->get_genome(), true);
}

my_example();
