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
* Esta función prepara la tarea y ejecuta el algoritmo genético.
**/
function my_example()
{
    $locations = [
    ['id' => 'start point', 'latitude' => 19.384271, 'longitude' => -99.167227],
    ['id' => 'valle', 'latitude' => 19.3714, 'longitude' => -99.168191],
    ['id' => 'luz', 'latitude' => 19.388440, 'longitude' => -99.220657],
    ['id' => 'villada', 'latitude' => 19.388132, 'longitude' => -99.011080],
    ['id' => 'triunfo', 'latitude' => 19.37575146, 'longitude' => -99.117958],
    ['id' => 'lomas', 'latitude' => 19.422109, 'longitude' => -99.213864],
    ['id' => 'arigola', 'latitude' => 19.422109, 'longitude' => -99.213864],
    ['id' => 'indios', 'latitude' => 19.489285, 'longitude' => -99.110390],
    ['id' => 'loma linda', 'latitude' => 19.459531, 'longitude' => -99.244397],
    ['id' => 'juarez', 'latitude' => 19.429314, 'longitude' => -99.153360],
    ['id' => 'merced', 'latitude' => 19.425649, 'longitude' => -99.127991],
    ['id' => 'sixflags', 'latitude' => 19.300642, 'longitude' => -99.206603],
    ['id' => 'doctores', 'latitude' => 19.415314, 'longitude' => -99.145035],
    ['id' => 'teotongo', 'latitude' => 19.350216, 'longitude' => -99.991517],
    ['id' => 'satelite', 'latitude' => 19.505974, 'longitude' => -99.244042],
    ['id' => 'campestre', 'latitude' => 19.355233, 'longitude' => -99.192806],
    ['id' => 'ecatepec', 'latitude' => 19.554727, 'longitude' => -99.032422],
    ['id' => 'abdías', 'latitude' => 19.351077, 'longitude' => -99.294937],
    ['id' => 'popular', 'latitude' => 19.367837, 'longitude' => -99.119803],
    ['id' => 'mixcoac', 'latitude' => 19.375162, 'longitude' => -99.183480],
    ['id' => 'hipódromo', 'latitude' => 19.410012, 'longitude' => -99.173479],
    ['id' => 'escolar', 'latitude' => 19.540970, 'longitude' => -99.150340],
    ['id' => 'dorado', 'latitude' => 19.546474, 'longitude' => -99.216605],
    ['id' => 'nativitas', 'latitude' => 19.381869, 'longitude' => -99.135589],
    ['id' => 'ajusco', 'latitude' => 19.317729, 'longitude' => -99.159960],
    ['id' => 'portales', 'latitude' => 19.364219, 'longitude' => -99.144475],
    ['id' => 'irrigación', 'latitude' => 19.439995, 'longitude' => -99.208331],
    ['id' => 'militar', 'latitude' => 19.435463, 'longitude' => -99.216742],
    ['id' => 'abastos', 'latitude' => 19.417994, 'longitude' => -99.066741]];

    $start_id = 'start point';

    // Se crean valores de configuración
    $data = ['start' => $start_id,  // Punto de partida del viaje
             'circuit' => False,  // Indica si el viaje será un circuito
             'cost' => \genesphp\create_distance_matrix($locations)];  // Costos

    $n = 500;  // Individuos
    $gen = 300;  // Generaciones máximas
    $cp = 0.3;  // Probabilidad de cruza
    $max_mp = 0.1; // Máxima probabilidad de mutación
    $cycle_mp = 100.0; // Generaciónes por ciclo de mutación
    $elitism = 1.0;  // Porcentaje de elitismo
    $duration = INF;  // Duración máxima en segundos
    $verbose = 10;  // Frecuencia de reporte

    // Se crea la tarea
    $task = new \genesphp\Task();

    // Se establecen datos
    $task->set_data($data);

    // Se establecen locaciones que aparecen en genomas (todos menos el inicial)
    $genome_points = [];
    foreach ($locations as $key => $value) {
        if ($value['id'] != $start_id) {
            $genome_points[] = $value['id'];
        }
    }

    // Se crea y asigna la población inicial
    $the_pop = \genesphp\init_permutation_pop($n, $genome_points);
    $task->set_population($the_pop);

    // Se establecen funciones de cruza, mutacion y selección
    $task->set_evals(['\\genesphp\\travel_cost'], [-1.0]);
    $task->set_mutator('\\genesphp\\mutate_multiple',
         ['operators' => ['\\genesphp\\mutate_swap',
                          '\\genesphp\\mutate_insert']]);
    $task->set_crossover('\\genesphp\\crossover_scx');
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

    echo $sol;
    echo "Genome only:\n" . print_r($sol->get_genome(), true);
}

my_example();
