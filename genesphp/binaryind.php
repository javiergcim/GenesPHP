<?php
namespace genesphp;

include_once 'individual.php';
include_once 'utils.php';

class BinaryInd extends Individual
{
    /**
    * Clase para individuos con genoma binario.
    *
    * @var array $var_bits Arreglo que indica cuantos bits usa cada variable
    * en el genoma.
    * @var array $sign_bits Arreglo que indica que variables poseen bit de
    * signo.
    * @var array $precalc Un arreglo que almacena factores que ajustan el
    * valor entero codificado en cada variable a su valor flotante correcto.
    * @var array $struct Un arreglo de arreglos que indican la forma en que se
    * codifica cada variable almacenado en el genoma.
    **/

    protected $var_bits = null;
    protected $sign_bits = null;
    protected $precalc = null;
    protected $struct = null;

    /**
    * Constructor de la clase *BinaryInd*.
    *
    * @param array $genome Una lista con el genoma en su forma bruta.
    * @param array $var_bits Arreglo que indica cuantos bits usa cada variable
    * en el genoma.
    * @param array $sign_bits Arreglo que indica que variables poseen bit de
    * signo.
    * @param array $precalc Un arreglo que almacena factores que ajustan el
    * valor entero codificado en cada variable a su valor flotante correcto.
    * @param mixed $data Un objeto arbitrario.
    * @param array $fitness Un arreglo con el fitness.
    **/
    public function __construct($genome,
                                $var_bits,
                                $sign_bits,
                                $precalc,
                                $struct,
                                $data = null,
                                $fitness = null)
    {
        $this->var_bits = $var_bits;
        $this->sign_bits = $sign_bits;
        $this->precalc = $precalc;
        $this->struct = $struct;

        parent::__construct($genome, $data, $fitness);
    }

    /**
    * Regresa el genoma del individuo en forma amigable.
    *
    * @return array Una secuencia con el genoma.
    **/
    public function get_genome()
    {
        $nice_genome = [];
        $genome = implode('', $this->genome);
        $n_vars = count($this->var_bits);
        $left = 0;
        for ($i = 0; $i < $n_vars; $i++) {
            $v = $this->var_bits[$i];
            $s = $this->sign_bits[$i];
            $p = $this->precalc[$i];

            if ($s) {  // Si tiene bit de signo
                if ($genome[$left] == '0') {  // Positivo
                    $nice_genome[] =
                        bindec(substr($genome, $left + 1, $v - 1)) * $p;
                }
                else {  // Negativo
                    $nice_genome[] =
                        -bindec(substr($genome, $left + 1, $v - 1)) * $p;
                }
            }
            else {
                $nice_genome[] = bindec(substr($genome, $left, $v)) * $p;
            }

            $left += $v;
        }

        return $nice_genome;
    }


    /**
    * Recibe un genoma en su forma amigable y la transforma en la forma
    * cruda para ser almacenada en el individuo.
    *
    * El genoma debe ser compatible con las especificaciones establecidas en
    * los atributos del indivuduo, como *var_bits* y *sign_bits*
    *
    * @param array $genome El genome en su forma amigable.
    **/
    public function set_genome($genome)
    {
        $n_vars = count($this->var_bits);
        $struct = $this->struct;
        $new_raw = [];

        for ($i = 0; $i < $n_vars; $i++) {
            $new_raw = \array_merge($new_raw, dec_to_bin($genome[$i],
                                                         $struct[$i][0],
                                                         $struct[$i][1],
                                                         $struct[$i][2]));
        }

        $this->genome = $new_raw;
    }
}
