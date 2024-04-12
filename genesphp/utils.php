<?php
namespace genesphp;

/**
* Genera una variable aleatoria con distribución geométrica con
* probabilidad *$p*.
*
* @var float $p Probabilidad del ensayo binomial correspondiente al proceso
* geométrico.
*
* @return int Un valor que corresponde a la variable aleatoria.
**/
function geometric_dist($p)
{
    if ($p == 1.0 || $p <= 0.0) {
        return $p === 1.0 ? 1 : INF;
    }
    return \ceil(\log(1.0 - \mt_rand() / (\mt_getrandmax() + 1.0)) / \log(1.0 - $p));
}

/**
* Regresa una variable aleatoria distribuida normalmente. Si *$integer* está
* establecido como verdadero, el valor se redondea al entero más próximo.
*
* @param float $mean La media de la distribución.
* @param float $sd La desviación estándar de la distribución.
* @param bool $integer Una bandera que indica si debe regresarse un valor
* entero.
*
* @return float|int Un valor distribuido normalmente.
**/
function gauss_dist($mean, $sd, $integer)
{
    $max_int = \mt_getrandmax();
    $x = (float) \mt_rand(1, $max_int) / (float) $max_int;
    $y = (float) \mt_rand(1, $max_int) / (float) $max_int;
    $v = \sqrt(-2.0 * \log($x)) * \cos(2.0 * M_PI * $y) * $sd + $mean;

    if ($integer) {
        return \round($v);
    }
    else {
        return $v;
    }
}

/**
* Convierte un número flotante a una expresión binaria de punto fijo.
* Se buscará encontrar la expresión binaria más cercana posible al número,
* aún si está fuera de rango.
*
* @param float $num El número a convertir.
* @param bool $sign Indica si debe usarse bit de signo.
* @param int $i_dig Cantidad de digitos para la parte entera.
* @param int $d_dig Cantidad de digitos para la mantisa.
*
* @return array Un arreglo con el número codificado en binario.
**/
function dec_to_bin($num, $sign, $i_dig, $d_dig)
{
    $max_abs_value =
        (\pow(2, $i_dig) - 1) +
        (\pow(2, $d_dig) - 1) / \pow(2, $d_dig);

    // Se establece bit se signo (si existe)
    if ($sign) {
        if ($num < 0.0){
            $binary = ['1'];
        } else{
            $binary = ['0'];
        }
    } else {
        $binary = [];
    }

    if ($num < 0.0 && !$sign) {  // Debe ser cero
        $tmp_binary = \array_fill(0, $i_dig + $d_dig, '0');
        $binary = \array_merge($binary, $tmp_binary);
    } else if (\abs($num) >= $max_abs_value){
        $tmp_binary = \array_fill(0, $i_dig + $d_dig, '1');
        $binary = \array_merge($binary, $tmp_binary);
    } else{
        $abs_num = \abs($num);
        $numint = \floor($abs_num);
        $numdec = $abs_num - $numint;

        // Se crea la parte entera
        $tmp_str = \sprintf( '%0' . \strval($i_dig) . 'd', \decbin($numint));
        $binint = \str_split($tmp_str);

        // Se crea la parte decimal
        $numdec = \floor($numdec * \pow(2, $d_dig));
        $tmp_str = \sprintf( '%0' . \strval($d_dig) . 'd', \decbin($numdec));
        $bindec = \str_split($tmp_str);

        # Se arma la cadena final
        $binary = \array_merge($binary, $binint);
        $binary = \array_merge($binary, $bindec);
    }

    return $binary;
}

/**
* Crea una permutación al azar in-situ de *$elements* con el algoritmo
* Fisher-Yates.
*
* Fisher, R. A., & Yates, F. (1943). Statistical tables for biological,
* agricultural and medical research. Oliver and Boyd Ltd, London.
*
* *Asunciones:*
* Se asume que el arreglo posee indices númerados de *0* a
* *count($elements - 1)*.
*
* @param array @elements Un arreglo con los elementos a permutar.
**/
function fisher_yates(&$elements)
{
    $max = \count($elements) - 1;

    while ($max > 0) {
        $r = \mt_rand(0, $max);
        $temp = $elements[$r];
        $elements[$r] = $elements[$max];
        $elements[$max] = $temp;
        $max--;
    }
}

/**
* Elige n elementos al azar sin reemplazo de un arreglo, utilizando una variante
* del algoritmo Fisher-Yates. Los elementos son escogidos solo en el rango
* especificado.
*
* Fisher, R. A., & Yates, F. (1943). Statistical tables for biological,
* agricultural and medical research. Oliver and Boyd Ltd, London.
*
* *Asunciones:*
* Se asume que el arreglo posee indices númerados de 0 a count($elements - 1).
*
* @param int $min Parámetro minimo para el range.
* @param int $max Parámetro máximo para el range.
* @param int $n La cantidad de elementos a elegir. Debe estar entre 1 y la
* la diferencia $max - $min.
*
* @return array Un arreglo con los índices de los elementos seleccionados.
**/
function sample_for_range($min, $max, $n)
{
    $elements = \range($min, $max);
    $max_i = $max - $min;
    $i = 0;
    while ($i <= $n) {
        $r = \mt_rand($i, $max_i);
        $temp = $elements[$r];
        $elements[$r] = $elements[$i];
        $elements[$i] = $temp;
        $i++;
    }

    return array_slice($elements, 0, $n);
}

/**
* Calcula la distancia entre dos puntos de la Tierra, con la fórmula de
* Haversine.
*
* @param float $lat_from Latitud del punto A en grados.
* @param float $long_from Longitud del punto A en grados.
* @param float $lat_to Latitud del punto B en grados.
* @param float $long_to Longitud del punto B en grados.
* @param float $sphere_radius Radio de la esfera.
*
* @return float Distancia entre los puntos en la unidad de *$sphere_radius*.
**/
function haversine_distance(
    $lat_from,
    $long_from,
    $lat_to,
    $long_to,
    $sphere_radius = 6371000.0
) {
    // Convertimos de grados a radianes
    $lat_from = \deg2rad($lat_from);
    $long_from = \deg2rad($long_from);
    $lat_to = \deg2rad($lat_to);
    $long_to = \deg2rad($long_to);

    $lat_delta = $lat_to - $lat_from;
    $long_delta = $long_to - $long_from;

    $angle = 2.0 * \asin(\sqrt(\pow(\sin($lat_delta / 2.0), 2) +
    \cos($lat_from) * \cos($lat_to) * \pow(\sin($long_delta / 2.0), 2)));

    return $angle * $sphere_radius;
}

/**
 * Calcula la distancia euclidea entre dos puntos.
 * 
 * @param array $a Coordenadas del primer punto.
 * @param array $b Coordenadas del segundo punto.
 * 
 * @return float La distancia euclidea entre ambos puntos.
 */
function cuclidean_distance($a, $b) {
    $distance = 0;
    $dimensions = count($a);

    for ($i = 0; $i < $dimensions; $i++) {
        $distance += pow($b[$i] - $a[$i], 2);
    }

    $distance = sqrt($distance);

    return $distance;
}

/**
* Crea la matriz de distancias entre un conjunto de puntos, dados de la forma:
* ['id' => id, 'latitude' => lat, 'longitude' => long]
*
* @param array $points Un arreglo con los puntos a calcular sus distancias.
* @param boolean $euclidean Indica si las distancias son euclideas. En ese caso, los puntos
* son n-dimensionles, y se encuentran almacenados en un arreglo.
*
* @return array Una matriz cuadrada con las distancias entre los puntos.
**/
function create_distance_matrix($points, $euclidean = false)
{
    // Creamos lista de destinos
    $points_map = [];
    $i = 0;
    foreach ($points as $value) {
        $points_map[$value['id']] = $i;
        $i++;
    }

    $matrix = [];

    // Inicializamos la primer dimensión del arreglo
    foreach ($points as $value) {
        $matrix[$value['id']] = [];
    }

    // Calculamos los costos
    foreach ($points as $value_from) {
        $key_from = $value_from['id'];

        foreach ($points as $value_to) {
            $key_to = $value_to['id'];

            if ($key_from == $key_to) {
                $matrix[$key_from][$key_to] = 0.0;
                break;
            }

            if ($euclidean) {
                $matrix[$key_from][$key_to] = euclidean_distance(
                    $points[$points_map[$key_from]]['coords'],
                    $points[$points_map[$key_to]]['coords']
                );
            } else {
                $matrix[$key_from][$key_to] =
                haversine_distance(
                    $points[$points_map[$key_from]]['latitude'],
                    $points[$points_map[$key_from]]['longitude'],
                    $points[$points_map[$key_to]]['latitude'],
                    $points[$points_map[$key_to]]['longitude']
                );
            }

            
            $matrix[$key_to][$key_from] = $matrix[$key_from][$key_to];
        }
    }

    return $matrix;
}

/**
* Costo de un viaje del tipo *agente viajero*.
*
* @param array $genome El genoma a evaluar.
* @param mixed $data Un objeto arbitrario.
*
* @return float La evaluación del individuo.
**/
function travel_cost(&$genome, &$data)
{
    $start = $data['start'];
    $max_i = \count($genome) - 2;
    $matrix = $data['cost'];
    $cost = $matrix[$start][$genome[0]];

    for ($i = 0; $i <= $max_i; $i++) {
        $cost += $matrix[$genome[$i]][$genome[$i + 1]];
    }

    if ($data['circuit']) {
        $cost += $matrix[\end($genome)][$start];
    }

    return $cost;
}
