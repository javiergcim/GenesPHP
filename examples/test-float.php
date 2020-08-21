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
    $gen = 400;  // Generaciones máximas
    $cp = 0.3;  // Probabilidad de cruza
    $max_mp = 0.5; // Máxima probabilidad de mutación
    $cycle_mp = 100; // Generaciónes por ciclo de mutación
    $elitism = 0.5;  // Porcentaje de elitismo
    $duration = INF;  // Duración máxima en segundos
    $verbose = 25;  // Frecuencia de reporte

    // Calculamos una duración adecuada de ejecución (MAX 30 segundos):

    // Se crea la tarea
    $task = new \genesphp\Task();

    // Se crea y asigna la población inicial
    # (individuals, number of genes, min random value, max random value)
    $the_pop = \genesphp\init_float_pop($n, 2, -5.0, 5);
    $task->set_population($the_pop);

    // Se establecen funciones de cruza, mutacion y selección
    $task->set_evals(['example_evaluation_function'], [-1.0]);
    $task->set_mutator('\\genesphp\\mutate_normal',
                       ['mp' => $max_mp, 'sd' => 5.0, 'integer' => false]);
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
    echo "Genome only:\n" . print_r($sol->get_genome(), true);
}

my_example();
