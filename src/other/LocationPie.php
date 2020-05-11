<?php

namespace ItakenPHPie\other;

include('lib/Distance.class.php');

use ItakenPHPie\other\lib\Distance;

/**
 * 地理位置
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-11
 */
final class LocationPie
{
    /**
     * 计算实际搜索的四边形的四个边界范围
     *
     * @param float $lng 经度 例如：121.606546
     * @param float $lat 纬度 例如：29.918017
     * @return array
     */
    public static function getLbsMap($lng, $lat)
    {
        if (!is_numeric($lat) || !is_numeric($lng)) {
            return [];
        }
        $distance = 0.5;   // 半径 单位 10 KM
        $radius = '6371.393'; // 代为是KM
        $dlng = rad2deg(2 * asin(sin($distance / (2 * $radius)) / cos($lat)));
        $dlat = rad2deg($distance * 10 / $radius);
        return [
            'left' => round($lng - $dlng, 6),
            'right' => round($lng + $dlng, 6),
            'top' => round($lat + $dlat, 6),
            'bottom' => round($lat - $dlat, 6),
        ];
    }

    /**
     * 计算 两点距离
     *
     * @param float $lng1 经度1 例如：121.606546
     * @param float $lat1 纬度1 例如：29.918017
     * @param float $lng2 经度2
     * @param float $lat2 纬度2
     * @return int
     */
    public static function calDistance($lng1, $lat1, $lng2, $lat2)
    {
        if (!is_numeric($lat1) || !is_numeric($lng1) || !is_numeric($lat2) || !is_numeric($lng2)) {
            return -1;
        }
        return (new Distance)->calculate([$lat1, $lng1], [$lat2, $lng2]);
    }
}
