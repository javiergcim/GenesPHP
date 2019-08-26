<?php
namespace genesphp;

class Individual
{
    /**
    * Clase base para los individuos.
    *
    * @var array $genome El genoma del individuo.
    * @var array $fitness Un arreglo de valores fitness.
    * @var mixed $data Una variable arbitraria que contiene datos adjuntos al
    * individuo
    **/

    protected $genome = null;
    protected $fitness = null;
    protected $data = null;

    /**
    * Constructor de la clase  *Individual*.
    *
    * @param array|string $genome Un arreglo con el genoma en su forma bruta.
    * @param mixed $data Una variable arbitraria.
    * @param array $fitness Un arreglo con el fitness.
    **/
    public function __construct($genome, $data = null, $fitness = null)
    {
        $this->genome = $genome;
        $this->fitness = $fitness;
        $this->data = $data;
    }

    /**
    * Regresa una representación en cadena del individuo.
    *
    * @return string Una representación en cadena del objeto.
    **/
    public function __toString()
    {
        if ($this->genome === null) {
            $gen = '(none)';
        } else {
            $gen = '[' . \implode(', ', $this->genome) . ']';
        }

        if ($this->fitness === null) {
            $fit = '(none)';
        } else {
            $fit = '[' . \implode(', ', $this->fitness) . ']';
        }

        if ($this->data === null) {
            $data = '(none)';
        } else {
            $data = \print_r($this->data, true);
        }

        $str = 'Genome: ' . $gen . "\n" .
               "Fitness: " . $fit . "\n" .
               'Data: ' . $data . "\n";

        return $str;
    }

    /**
    * Regresa el genoma del individuo en forma amigable.
    *
    * @return array Una secuencia con el genoma.
    **/
    public function get_genome()
    {
        return $this->genome;
    }

    /**
    * Regresa el genoma del individuo en forma bruta.
    *
    * @return array Una secuencia con el genoma.
    **/
    public function get_raw_genome()
    {
        return $this->genome;
    }

    /**
    * Establece el genoma desde una versión amigable del mismo.
    *
    * @param array $genome Un arreglo con el genoma en forma decodificada o
    * amigable.
    **/
    public function set_genome(&$genome)
    {
        $this->genome = $genome;
    }

    /**
    * Establece el genoma desde una versión en bruto del mismo.
    *
    * @param array $genome Un arreglo con el genoma en bruto, tal cual es
    * almacenado en el individuo.
    **/
    public function set_genome_from_raw(&$genome)
    {
        $this->genome = $genome;
    }

    /**
    * Regresa el fitness del individuo.
    *
    * Regresa el arreglo de *fitness*.
    *
    * @return array Una secuencia con el fitness elegido.
    **/
    public function get_fitness($i = null)
    {
        if ($i === NULL) {
            return $this->fitness;
        }
        else {
            return $this->fitness[$i];
        }
    }

    /**
    * Establece el fitness del individuo.
    *
    * @param array $i Un arreglo con el fitness del individuo.
    *
    **/
    public function set_fitness($fit)
    {
        $this->fitness = $fit;
    }

    /**
    * Regresa los datos arbitrarios asociados al individuo.
    *
    * @return mixed Devuelve los datos arbitrarios asociados al individuo.
    **/
    public function get_data()
    {
        return $this->data;
    }

    /**
    * Establece los datos arbitrarios asociados al individuo.
    *
    * @param mixed $data Una variable arbitraria que se asignará al individuo.
    **/
    public function set_data(&$data)
    {
        $this->data = $data;
    }

    /**
    * Regresa el la longitud del genoma.
    *
    * @return int La longitud del genoma.
    **/
    public function get_size()
    {
        return count($this->genome);
    }
}
