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
* Intenta encontrar el mejor camino entre varios destinos (para Pegalinas).
*
* @param array $destinations Arrego de destinos con la forma:
* [id, latitude, longitude]. Se asume que las llaves de este arreglo son
* numéricas en orden ascendente.
* @param string $start El identificador del punto de partida.
*
* @return array Un arreglo con los destinos ordenados que no incluye el punto
* inicial.
**/
function peg_optimize_travel($destinations, $start = 'pegalinas')
{
    // Creamos lista de destinos
    $points_map = [];
    $genome_points = [];
    foreach ($destinations as $key => $value) {
        $points_map[$key] = $value['id'];
        if ($value['id'] != $start) {
            $genome_points[] = $value['id'];
        }
    }
    $points_map = array_flip($points_map);

    // Se crean valores de configuración
    $data = [];
    $data['start'] = $start;
    $data['circuit'] = false;
    $data['cost'] = \genesphp\create_distance_matrix($destinations);
    $n = 500;  // Individuos
    $gen = 10000;  // Generaciones máximas
    $cp = 0.3;  // Probabilidad de cruza
    $max_mp = 0.5; // Máxima probabilidad de mutación
    $cycle_mp = 100.0; // Generaciónes por ciclo de mutación
    $elitism = 1.0;  // Porcentaje de elitismo
    $verbose = 10;

    // Calculamos una duración adecuada de ejecución (MAX 30 segundos):
    $duration = pow(count($destinations), 3) * .0009;
    if ($duration <= .05) {
        $duration = .05;
    } elseif ($duration > 30.0) {
        $duration = 30.0;
    }

    // Se crea la tarea
    $task = new \genesphp\Task();

    // Se establecen datos
    $task->set_data($data);

    // Se establece población
    $the_pop = \genesphp\init_permutation_pop($n, $genome_points);
    $task->set_population($the_pop);

    // Se establecen funciones de cruza, mutacion y selección
    $task->set_evals(['\\genesphp\\travel_cost'], [-1.0]);
    $task->set_mutator('\\genesphp\\mutate_swap');
    // $task->set_mutator('\\genesphp\\mutate_multiple',
    //     ['operators' => ['\\genesphp\\mutate_swap',
    //                      '\\genesphp\\mutate_insert']]);
    $task->set_crossover('\\genesphp\\crossover_pseudoscx');
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

    // Reordenamos el arreglo original según resultados
    $final = [];
    foreach ($sol->get_genome() as $point_name) {
        $final[] = $destinations[$points_map[$point_name]];
    }

    echo $sol;

    return $final;
}

$destinos = [['id' => 'pegalinas', 'latitude' => 19.384271, 'longitude' => -99.167227],
             ['id' => 'valle', 'latitude' => 19.3714, 'longitude' => -99.168191],
             ['id' => 'luz', 'latitude' => 19.388440, 'longitude' => -99.220657],
             ['id' => 'villada', 'latitude' => 19.388132, 'longitude' => -99.011080],
             ['id' => 'triunfo', 'latitude' => 19.37575146, 'longitude' => -99.117958],
             ['id' => 'lomas', 'latitude' => 19.422109, 'longitude' => -99.213864],
             ['id' => 'arigola', 'latitude' => 19.422109, 'longitude' => -99.213864],
             ['id' => 'indios verdes', 'latitude' => 19.489285, 'longitude' => -99.110390],
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
             ['id' => 'zona escolar', 'latitude' => 19.540970, 'longitude' => -99.150340],
             ['id' => 'valle dorado', 'latitude' => 19.546474, 'longitude' => -99.216605],
             ['id' => 'nativitas', 'latitude' => 19.381869, 'longitude' => -99.135589],
             ['id' => 'ajusco', 'latitude' => 19.317729, 'longitude' => -99.159960],
             ['id' => 'portales', 'latitude' => 19.364219, 'longitude' => -99.144475],
             ['id' => 'irrigación', 'latitude' => 19.439995, 'longitude' => -99.208331],
             ['id' => 'militar', 'latitude' => 19.435463, 'longitude' => -99.216742],
             ['id' => 'abastos', 'latitude' => 19.417994, 'longitude' => -99.066741]];

$solucion = peg_optimize_travel($destinos, $start = 'pegalinas');
//print_r($solucion);
