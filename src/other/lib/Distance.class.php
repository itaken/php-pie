<?php

namespace ItakenPHPie\other\lib;

/**
 * 计算 两个地理位置 之间的距离
 * @doc http://www.movable-type.co.uk/scripts/latlong-vincenty.html
 * 
 * @editor itaken<regelhh@gmail.com>
 * @since 2015-3-24
 */
class Distance 
{

    /**
     * @var array 地心坐标系
     */
    private $axes = array(
        'WGS-84' => array(
            'name' => 'WGS-84',
            'a' => 6378137.0, // 长半径
            'f' => 298.257223563, // 扁率倒数
        ),
    );

    /**
     * 计算 两点距离
     * 
     * @param array $point1  array(经度,纬度) ( 例如: array(19.820664, -155.468066) )
     * @param array $point2
     *
     * @return float ( 单位: m )
     */
    public function calculate(array $point1, array $point2) {
        if (count($point1) != 2 || count($point2) != 2) {
            // 参数错误
            return 0;
        }
        $lat1 = doubleval($point1[0]);
        $lng1 = doubleval($point1[1]);
        $lat2 = doubleval($point2[0]);
        $lng2 = doubleval($point2[1]);
        if (!$this->is_valid_latitude($lat1) || !$this->is_valid_latitude($lat2) ||
            !$this->is_valid_longitude($lng1) || !$this->is_valid_longitude($lng2)
        ) {
            return 0;
        }
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);
        $lng1 = deg2rad($lng1);
        $lng2 = deg2rad($lng2);
        $a = $this->axes['WGS-84']['a'];
        $b = $this->calculate_semiminor_axis($a, $this->axes['WGS-84']['f']);
        $f = 1 / $this->axes['WGS-84']['f'];
        $L = $lng2 - $lng1;
        $U1 = atan((1 - $f) * tan($lat1));
        $U2 = atan((1 - $f) * tan($lat2));
        $iterationLimit = 100;
        $lambda = $L;
        $sinU1 = sin($U1);
        $sinU2 = sin($U2);
        $cosU1 = cos($U1);
        $cosU2 = cos($U2);
        do {
            $sinLambda = sin($lambda);
            $cosLambda = cos($lambda);
            $sinSigma = sqrt(
                    ($cosU2 * $sinLambda) * ($cosU2 * $sinLambda) +
                    ($cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda) * ($cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda)
            );
            if ($sinSigma == 0) {
                return 0.0;
            }
            $cosSigma = $sinU1 * $sinU2 + $cosU1 * $cosU2 * $cosLambda;
            $sigma = atan2($sinSigma, $cosSigma);
            $sinAlpha = $cosU1 * $cosU2 * $sinLambda / $sinSigma;
            $cosSqAlpha = 1 - $sinAlpha * $sinAlpha;
            if ($cosSqAlpha == 0) {
                $cos2SigmaM = 0;
            } else {
                $cos2SigmaM = $cosSigma - 2 * $sinU1 * $sinU2 / $cosSqAlpha;
            }
            $C = $f / 16 * $cosSqAlpha * (4 + $f * (4 - 3 * $cosSqAlpha));
            $lambdaP = $lambda;
            $lambda = $L + (1 - $C) * $f * $sinAlpha * ($sigma + $C * $sinSigma * ($cos2SigmaM + $C * $cosSigma * (- 1 + 2 * $cos2SigmaM * $cos2SigmaM)));
        } while (abs($lambda - $lambdaP) > 1e-12 && --$iterationLimit > 0);
        if ($iterationLimit == 0) {
            return 0;
        }
        $uSq = $cosSqAlpha * ($a * $a - $b * $b) / ($b * $b);
        $A = 1 + $uSq / 16384 * (4096 + $uSq * (- 768 + $uSq * (320 - 175 * $uSq)));
        $B = $uSq / 1024 * (256 + $uSq * (- 128 + $uSq * (74 - 47 * $uSq)));
        $deltaSigma = $B * $sinSigma * ($cos2SigmaM + $B / 4 * ($cosSigma * (- 1 + 2 * $cos2SigmaM * $cos2SigmaM) - $B / 6 * $cos2SigmaM * (- 3 + 4 * $sinSigma * $sinSigma) * (- 3 + 4 * $cos2SigmaM * $cos2SigmaM)));
        $s = $b * $A * ($sigma - $deltaSigma);
        return (round($s, 3));
    }

    /**
     * Validates latitude
     *
     * @param mixed $latitude
     *
     * @return bool
     */
    protected function is_valid_latitude($latitude) {
        return $this->is_in_bounds($latitude, - 90.0, 90.0);
    }

    /**
     * Validates longitude
     *
     * @param mixed $longitude
     *
     * @return bool
     */
    protected function is_valid_longitude($longitude) {
        return $this->is_in_bounds($longitude, -180.0, 180.0);
    }

    /**
     * 判断某个值 是否在给定的范围内
     *
     * @param float $value
     * @param float $lower
     * @param float $upper
     *
     * @return bool
     */
    protected function is_in_bounds($value, $lower, $upper) {
        if (!is_numeric($value)) {
            return FALSE;
        }
        if ($value < $lower || $value > $upper) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 短半径 ( semi-minor axis ) 计算
     *
     * @return float
     */
    protected function calculate_semiminor_axis($a, $f) {
        return $a * (1 - 1 / $f);
    }

}
