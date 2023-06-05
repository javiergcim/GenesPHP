<?php
namespace genesphp;

class Task
{
    /**
    * Clase base para las tareas.
    *
    * @var array @population La coleción de individuos de la población.
    * @var int $current_gen Indica la generacion actual cuando se está
    * aplicando un algoritmo genético sobre la tarea.
    * @var int @desired_size La cantidad de individuos deseada para la población
    * (la población puede tener una cantidad diferente de elementos en algunos
    * momentos).
    * @var array @target_obj Es un arreglo de índices de objetivos, que se
    * usará para las comparaciones y ordenamiento de los individuos de la
    * población.
    * @var array @constraints Un arreglo con los nombres de las funciones que
    * harán las veces de restricciones. Para cada función una restricción. Las
    * restricciones se evalúan siempre antes que los objetivos. Sólo si se
    * cumplen todas las restricciones, se evalúan posteriormente por las
    * funciones objetivo. Las funciones de restricción deben regtresar 0 si
    * se cumple la condición, 1 en caso de fallo.
    * @var array $penalties Arreglo de penalizaciones máximas aplicadas a un
    * individuo que no cumple las restricciones establecidas po el usuario.
    * Debe tener tantos elementos como funciones objetivo tenga la tarea.
    * @var string @mutator El nombre de la función que hará las veces de
    * mutador.
    * @var array @mutator_args Parámetros para la función de mutación.
    * @var string @ crossover El nombre de la función que hará las veces de
    * cruzamiento.
    * @var array @crossover_args Parámetros para la función de cruzamiento.
    * @var string @selector El nombre de la función que hará las veces de
    * selector. Ésta función aplicará la función de cruzamiento a las parejas
    * de individuos seleccionadas, y alojará los resultados en la población.
    * @var array @selector_args Parámetros para la función de selección.
    * @var array $objectives Arreglo de cadenas con los nombres de las
    * funciones de evaluación. Una para cada objetivo.
    * @var array $obj_factors Arreglo con la ponderación de cada función
    * objetivo. Un elemento para cada una.
    * @var array $func_args Arreglo de parámetros para las funciones de mutación
    * cruza, seleccion, etc, que serán utilizadas al momento de correr el
    * algoritmo genético.
    * @var mixed $data Un objeto arbitrario asociado a la Tarea, con datos
    * proclives a ser usados por allgún algoritmo de cruzamiento, selección o
    * mutación.
    **/

    protected $population = null;
    protected $current_gen = null;
    protected $desired_size = null;
    protected $target_obj = null;
    protected $constraints = null;
    protected $mutator = null;
    protected $mutator_args = [];
    protected $crossover = null;
    protected $crossover_args = [];
    protected $selector = null;
    protected $selector_args = [];
    protected $objectives = null;
    protected $obj_factors = null;
    protected $func_args = [];
    protected $data = null;

    /**
    * Constructor de la clase  *Task*.
    **/
    public function __construct()
    {
    }

    /**
    * Regresa la población actual de la tarea.
    *
    * @return array Un arreglo con la población actual.
    **/
    public function get_population()
    {
        return $this->population;
    }

    /**
    * Regresa una copia de un corte de la población.
    *
    * @param int $offset El índice inicial del corte.
    * @param int $n La cantidad de elementos regresados a partir del offset.
    *
    * @return array Un arreglo con la copia del corte especificado.
    **/
    public function get_subpopulation_copy($offset, $n)
    {
        $copy = [];
        $max = $offset + $n - 1;
        for ($i = $offset; $i <= $max; $i++) {
            $copy[] = clone $this->population[$i];
        }

        return $copy;
    }

    /**
    * Reemplaza la población actual. Ésta función no ajusta el tamaño
    * deseable de la población. Para ello, usar set_population.
    *
    * @param array $pop Un arreglo con la población que reemplaza la anterior.
    **/
    public function replace_population(&$pop)
    {
        $this->population = $pop;
    }

    /**
    * Establece la población actual. En el proceso se actualiza el tamaño
    * deseable de la población.
    *
    * @param array $pop Un arreglo con la población que reemplaza la anterior.
    **/
    public function set_population(&$pop)
    {
        $this->population = $pop;
        $this->desired_size = \count($pop);
    }

    /**
    * Agrega individuos a la población actual. Ésta función no actualiza la
    * cantidad de individuos deseada de la población.
    *
    * @param array $pop Un arreglo con la población que se añadirá a la
    * actual.
    * @param bool $first Indica si los individuos que se añaden van al inicio
    * del arreglo.
    **/
    public function append_population(&$pop, $first = false)
    {
        if ($first) {
            $this->population = \array_merge($pop, $this->population);
        } else {
            $this->population = \array_merge($this->population, $pop);
        }
    }

    /** Ajusta la población para que contenga la cantidad de elementos
    * especificado en el atributo *$desired_size* de la instancia, o bien, la
    * indicada explícitamente como argumento. Si se indica explícitamente,
    * el nuevo tamaño será establecido como el tamaño *deseado* de la
    * población.
    *
    * Modifica la población in-situ.
    *
    * La población crecerá o será truncada según corresponda.
    *
    * Si es truncada, los últimos *$n - $desired_size* elementos del arreglo son
    * eliminados.
    *
    * Si es expandida, los nuevos individuos serán generados mutando con una
    * probabilidad aleatoria elementos elegidos al azar en la población.
    * Se anexan al final del arreglo.
    *
    * Se asume que hay una función de mutación establecida, que acepta al menos
    * un parámetro de probabilidad.
    *
    * @param int $n Cantidad de elementos que debe poseer la población.
    *
    * @return bool Regresa verdadero si la población creció. Falso si se redujo
    * o quedó igual.
    */
    public function adjust_population_size($n = null)
    {
        $current_size = \count($this->population);
        $max_index = $current_size - 1;

        // Se ajusta o no el atributo $desired_size, y se establece $n final
        if ($n === null) {
            $n = $this->desired_size;
        } else {
            $this->desired_size = $n;
        }

        $diff = $n - $current_size;  // + Si le faltan, - si le sobran

        // Se debe truncar o quedó igual
        if ($diff <= 0) {
            $re_evaluate = false;
            $this->population = \array_slice($this->population, 0, $n);
        } // Se deben añadir elementos
        else {
            $re_evaluate = true;
            while ($diff > 0) {
                // Se elige un elemento al azar de la población, y se clona
                $index = \mt_rand(0, $max_index);
                $born = clone $this->population[$index];

                // Se crea nuevo genoma de mutación
                ($this->mutator)($this, $born, $this->mutator_args);

                // Se añade a la población
                $this->population[] = $born;

                $diff--;
            }
        }

        return $re_evaluate;
    }

    /**
    * Elimina los elementos con fitness duplicado. Está función no ajusta la
    * cantidad de individuos deseados en la población.
    **/
    public function remove_duplicate_fitness()
    {
        // Ordenamos la población
        $objectives = \range(0, \count($this->obj_factors) - 1);
        $this->order_population($objectives);
        $real_size = \count($this->population);

        $new_population = [$this->population[0]];
        $current_fit = $new_population[0]->get_fitness();
        for ($i = 1; $i < $real_size; $i++) {
            $new_fit = $this->population[$i]->get_fitness();
            if ($current_fit != $new_fit) {
                   $new_population[] = $this->population[$i];
                   $current_fit = $new_fit;
            }
        }

        $this->population = $new_population;
    }

    /** Regresa el número de individuos que realmente hay en la población.
    *
    * @return int El número de elementos en la población.
    **/
    public function get_size()
    {
        if ($this->population) {
            return \count($this->population);
        } else {
            return 0;
        }
    }

    /** Regresa el número de individuos que se desean en la población.
    *
    * @return int El número de elementos deseados en la población.
    **/
    public function get_desired_size()
    {
        return $this->desired_size;
    }

    /** Establece la generación actual en la que se encuentra la tarea, si se
    * está ejecuando un algoritmo genético.
    *
    * @return int|null La generacion actual. *null* si no hay algoritmo activo.
    **/
    public function set_generation($g)
    {
        $this->current_gen = $g;
    }

    /** Regrese la generación actual en la que se encuentra la tarea, si se está
    * ejecuando un algoritmo genético.
    *
    * @return int|null La generacion actual (de existir).
    **/
    public function get_generation()
    {
        return $this->$current_gen;
    }

    /**
    * Función que indica que individuo es superior a otro. Pensado para usarse
    * por una función de ordenación personalizada en orden ascendente.
    *
    * Se utiliza el atibuto *$target_obj* como el indicador de qué objetivos, y
    * en que orden, emplearán para la comparación.
    *
    *  Si el individuo no posee fittnes, se considerará como 'INF'.
    *
    * @param Individual El primer individuo de la comparación.
    * @param Individual El segundo individuo de la comparación.
    *
    * @return int 1 si el primer elemento es mayor, 0 si son iguales, -1 si
    * es menor.
    **/
    protected function order_individual($a, $b)
    {
        $a_fit = $a->get_fitness();
        $b_fit = $b->get_fitness();
        foreach ($this->target_obj as $objective) {
            if ($a_fit === null) {
                $x = INF;
            } else {
                $x = $a_fit[$objective];
            }

            if ($b_fit === null) {
                $y = INF;
            } else {
                $y = $b_fit[$objective];
            }

            if ($x > $y) {
                if ($this->obj_factors[$objective] > 0) {  // Es maximización
                    return -1;
                } else {  // Es minimización
                    return 1;
                }
            } elseif ($x < $y) {
                if ($this->obj_factors[$objective] > 0) {  // Es maximización
                    return 1;
                } else {  // Es minimización
                    return -1;
                }
            }
        }

        return 0;
    }

    /**
    * Ordena una población con base del fitness del objetivo seleccionado. La
    * función siempre colocará los elementos más favorables según el orden
    * establecido en el arreglo dado como parámetro, que será una lista de los
    * índices de las funciones objetivo a considerar.
    *
    * Si no se provee de objetivos, se priorizarán los mismos tal y como
    * fueron registrados en la tarea. La tarea debe tener definidas, al menos,
    * las funciones objetivo.
    *
    * EL cambio de la poblacion es in-situ.
    *
    * @param array|null $objectives El arreglo de indices de objetivos sobre los
    * cuales se hará la comparación, en orden de prioridad.
    **/
    public function order_population($objectives = null)
    {
        if ($objectives === null) {
            $objectives = range(0, \count($this->obj_factors) - 1);
        }
        $this->target_obj = $objectives;
        \usort($this->population, [$this, 'order_individual']);
    }

    /**
    * Regresa el individuo de la población especificado.
    *
    * @param int $i El índice del individuo deseado.
    *
    * @return Individual El individuo seleccionado.
    **/
    public function get_individual($i)
    {
        return $this->population[$i];
    }

    /**
    * Función que permite asociar las funciones de evaluación de problema, que
    * se provee con *$evals*.
    *
    * Para cada objetivo se utiliza un valor de ponderación, que se provee
    * con *$factors*. Útil para los problemas multiobjetivo.
    *
    * Si el valor en *$factors* es positivo, se considera un problema de
    * maximización, si es negativo se considera de minimización.
    *
    * @param array $evals Arreglo con los nombres completamente cualificados de
    * las funciones que se utilizarán para evaluar un individuo.
    * @param array $factors Arreglo con los valores de ponderación asociados a
    * cada función de evaluación. Si el valor es positivo, se considera un
    * problema de maximización, si es negativo se considera de minimización.
    * Debe ser del mismo tamaño que *$evals*.
    **/
    public function set_evals($evals, $factors)
    {
        if (count($evals) == \count($factors)) {
            $this->objectives = $evals;
            $this->obj_factors = $factors;
        } else {
            throw new Exception('Both arguments must be the same size');
        }
    }

    /**
    * Regresa el arreglo con las ponderaciones de las funciones de evaluación,
    * o bien, la ponderación especificada.
    *
    * @param int|null $i El índice de la ponderación deseada.
    *
    * @return float|array El arreglo con las ponderaciones.
    **/
    public function get_obj_factors($i = null)
    {
        if ($i === null) {
            return $this->obj_factors;
        } else {
            return $this->obj_factors[$i];
        }
    }

    /**
    * Función que permite asociar funciones que evalúan si un individuo cumple
    * restricciones especificadas por el usuario.
    *
    * Así mismo, permite establecer un arreglo de valores de penalización
    * máximos. Uno para cada función objetivo.
    *
    * @param array $constraints Arreglo con los nombres completamente
    * cualificados de las funciones que harán las veces de restricciones. Las
    * funciones deben regresar 0 en caso de cumplirse la condición, y 1 en caso
    * contrario.
    * @param array $max_penalties Arreglo con los valores de penalización
    * máxima. Uno para cada función objetivo. Deben existir tantos valores de
    * penalización como funciones objetivo en la tarea.
    **/
    public function set_constraints($constraints, $max_penalties)
    {
        $n_penalties = \count($max_penalties);
        $n_constraints = \count($constraints);

        if ($n_penalties == \count($this->objectives)) {
            $this->constraints = $constraints;
            $this->penalties = [];
            for ($i = 0; $i < $n_penalties; $i++) {
                $this->penalties[] = $max_penalties[$i] / $n_constraints;
            }
        } else {
            throw new Exception(
                '$max_penalities and $constraints must have as many elements ' .
                'as objectives have the task'
            );
        }
    }

    /**
    * Establece la función de mutación que se aplicará a los individuos.
    *
    * La función debe ser capaz de recibir tres parámetros: una referencia al
    * objeto Task asociado, una referencia al individuo a mutar, y un arreglo
    * de argumentos. Debe modificar el genoma del individuo
    * proporcionado in-situ.
    *
    * @param string $mutator El nombre completamente cualificado de la función
    * de mutación.
    * @param array $args Un arreglo con los argumentos de la función de
    * mutación.
    **/
    public function set_mutator($mutator, $args = [])
    {
        $this->mutator = $mutator;
        $this->mutator_args = $args;
    }

    /** Establece el argumento indicado para la función de mutación.
    *
    * @param mixed $key La llave o nombre del argumento.
    * @param mixed $value El valor del argumento.
    **/
    public function set_mutator_arg($key, $value)
    {
        $this->mutator_args[$key] = $value;
    }

    /**
    * Establece la función de cruza que se aplicará a los individuos.
    *
    * La función debe ser capaz de recibir tres parámetros: una referencia al
    * objeto Task asociado, una referencia para cada individuo a cruzar (2).
    * Debe generar un arreglo con los individuos nuevos como descendencia.
    *
    * El número de descendientes se asume como dos.
    *
    * @param string $crossover El nombre completamente cualificado de la
    * función de cruzamiento.
    * @param array $args Un arreglo con los argumentos de la función de
    * cruza.
    **/
    public function set_crossover($crossover, $args = [])
    {
        $this->crossover = $crossover;
        $this->crossover_args = $args;
    }

    /** Establece el argumento indicado para la función de cruzamiento.
    *
    * @param mixed $key La llave o nombre del argumento.
    * @param mixed $value El valor del argumento.
    **/
    public function set_crossover_arg($key, $value)
    {
        $this->crossover_args[$key] = $value;
    }

    /**
    * Establece la función de selección que se aplicará a la población.
    *
    * La función debe ser capaz de recibir dos parámetros: una referencia al
    * objeto Task asociado, y una probabilidad de cruzamiento. Debe ejecutar
    * la función de cruzamiento asignada a la tarea, e incorporar la prole
    * generada a la población.
    *
    * Debe modificar la población in-situ.
    *
    * @param string $selector El nombre de la función de selección.
    * @param array $args Un arreglo con los argumentos de la función de
    * selección.
    **/
    public function set_selector($selector, $args = [])
    {
        $this->selector = $selector;
        $this->selector_args = $args;
    }

    /** Establece el argumento indicado para la función de selección.
    *
    * @param mixed $key La llave o nombre del argumento.
    * @param mixed $value El valor del argumento.
    **/
    public function set_selector_arg($key, $value)
    {
        $this->selector_args[$key] = $value;
    }

    /**
    * Establece los datos arbitrarios asociados a la tarea.
    *
    * @param mixed $data Datos arbitrarios asociados a la tarea.
    **/
    public function set_data($data)
    {
        $this->data = $data;
    }

    /**
    * Obtiene los datos arbitrarios asociados a la tarea.
    *
    * @return mixed Los datos arbitrarios asociados a la tarea.
    **/
    public function get_data()
    {
        return $this->data;
    }

    /**
    * Evalua los individuos de la población que no posean un fitness. La
    * evaluación se efectúa para todas las funciones de evaluación asociadas a
    * la tarea.
    **/
    public function evaluate()
    {
        $data = $this->data;
        $n_evals = \count($this->objectives);
        if ($this->constraints) {
            $n_constraints = \count($this->constraints);
        } else {
            $n_constraints = 0;
        }
        foreach ($this->population as $son) {  // Para cada individuo
            if ($son->get_fitness() === null) {  // Si no tiene fitness
                $fit = [];
                $son_genome = $son->get_genome();

                // Calculamos las restricciones
                $failed = 0;
                for ($j = 0; $j < $n_constraints; $j++) {
                    $failed += $this->constraints[$j]($son_genome, $data);
                }

                if ($failed == 0) {  // Calculamos objetivos si cumple restric.
                    for ($i = 0; $i < $n_evals; $i++) {
                        $fit[] = $this->objectives[$i]($son_genome, $data);
                    }
                } else {  // Aplicamos penalización si no cumple restricciones
                    for ($i = 0; $i < $n_evals; $i++) {
                        $fit[] = $this->penalties[$i] * $failed;
                    }
                }

                $son->set_fitness($fit);
            }
        }
    }

    /** Aplica la función de mutación a todos los individuos de la población.
    **/
    public function mutate()
    {
        $pop_size = count($this->population);
        for ($i = 0; $i < $pop_size; $i++) {
            ($this->mutator)($this, $this->population[$i], $this->mutator_args);
        }
    }

    /**
    * Aplica la selección y cruza especificados.
    *
    * @param float $cp Probabilidad de cruzamiento.
    **/
    public function apply_selection()
    {
        ($this->selector)($this, $this->selector_args);
    }

    /**
    * Aplica el cruzamiento.
    *
    * @param Individual $ind_a Primer individuo.
    * @param Individual $ind_b Segundo individuo.
    *
    * @return array Un arreglo con los dos descendientes.
    **/
    public function apply_crossover(&$ind_a, &$ind_b)
    {
        return ($this->crossover)($this, $ind_a, $ind_b, $this->crossover_args);
    }
}
