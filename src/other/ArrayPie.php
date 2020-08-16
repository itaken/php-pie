<?php

namespace ItakenPHPie\other;

/**
 * 数组工具类
 *
 * @modify itaken<regelhh@gmail.com>
 * @since 2019-3-10
 */
final class ArrayPie
{
    /**
	 * 根据Key获取值，如果不存在则返回默认值
     *
	 * @param array        $array
	 * @param string|array $key      如果键是数组类型，则表示多维度地寻找
	 * @param string|null  $default  默认值
	 * @param string|null  $callback 如果不为空，则应用到返回值
	 * @return mixed
	 */
	public static function get($array, $key, $default = null, $callback = null)
	{
		if(is_array($key)) {
			$value = $array;
			foreach($key as $k) {
				if(isset($value[$k])) {
					$value = $value[$k];
				} else {
					$value = $default;
					break;
				}
			}
		} else {
			if(isset($array[$key])) {
				$value = $array[$key];
			} else {
				$value = $default;
			}
		}
		return is_null($callback) ? $value : $callback($value);
	}

    /**
     * 将数组转化为以 $key 值为键的 hashmap
     *
     * @param array    $array
     * @param string   $key
     * @param callable $fn
     * @param callable $val_fn
     * @return array
     */
    public static function assocByKey(array $array, string $key, callable $fn = null, callable $val_fn = null)
    {
        $newArray = [];
        foreach ($array as $row) {
            if ($fn === null) {
                $newArray[$row[$key]] = ($val_fn === null) ? $row : $val_fn($row);
            } else {
                $newArray[$fn($row[$key])] = ($val_fn === null) ? $row : $val_fn($row);
            }
        }
        return $newArray;
    }

    /**
     * 将数组转成关联数组（hashmap）
     * ```
     * $array = array(array('name' => 'xxx', 'age' => 123), array('name' => 'yyy', 'age' => 456));
     * $array = array_util_class::assoc_by_callable($array, function($row) {
     *    return $row['name'] . $row['age'];
     * }, function($row) {
     *    $row['name'] = strtoupper($row['name']);
     *    return $row;
     * });
     * // Output:
     * // $array = array('xxx123' => array('name' => 'xxx', 'age' => 123),  'yyy456' => array('name' => 'yyy', 'age' => 456));
     * ```
     * @param array $array
     * @param callable $key_fn
     * @param callable $val_fn
     * @return array
     */
    public static function assocByCallable(array $array, callable $key_fn, callable $val_fn = null): array
    {
        $newArray = [];
        foreach ($array as $row) {
            $newArray[call_user_func($key_fn, $row)] = ($val_fn === null) ? $row : call_user_func($val_fn, $row);
        }
        return $newArray ?: [];
    }


    /**
     * 通过键名筛选hashmap，值为null的将被忽略
     *
     * @param array    $array
     * @param array    $keys
     * @param callable $fn
     * @return array
     */
    public static function filterByKeys(array $array, $keys, $fn = null): array
    {
        $newArray = [];
        foreach ($keys as $key) {
            if (!isset($array[$key])) {
                continue;
            }
            $value = ($fn === null) ? $array[$key] : $fn($array[$key]);
            if ($value === null) {
                continue;
            }
            $newArray[$key] = $value;
        }
        return $newArray ?: [];
    }

    /**
     * 根据array[][$key]的值对数组进行分组
     *
     * @param array    $array
     * @param string     $key
     * @param callable $fn
     * @return array
     */
    public static function groupByKey(array $array, string $key, callable $fn = null): array
    {
        $newArray = [];
        foreach ($array as $row) {
            $newArray[$row[$key]][] = ($fn === null) ? $row : $fn($row);
        }
        return $newArray ?: [];
    }

    /**
     * 根据$map过滤&映射$array
     * ```
     * map_by_map(array('a' => 'A', 'b' => 'B', 'c' => 'C'), array('_a' => 'a', '_c' => 'c'));
     *
     * // 结果： array('_a' => 'A', '_c' => 'C')
     * ```
     * @param array    $array
     * @param array    $map
     * @param callable $value_fn
     * @param callable $key_fn
     * @return array
     */
    public static function mapByMap(array $array, array  $map, $value_fn = null, $key_fn = null):array
    {
        $newArray = [];
        foreach ($map as $left => $right) {
            $key = ($key_fn === null) ? $left : $key_fn($left);

            $value = static::get($array, $right);
            if ($value_fn !== null) {
                $value = $value_fn($array[$right]);
            }

            $newArray[$key] = $value;
        }
        return $newArray ?: [];
    }

    /**
     * 函数式：array中任意值为真即为真
     *
     * @param array $array
     * @param callable $fn
     * @return bool
     */
    public static function any(array $array, $fn = null):bool
    {
        foreach ($array as $val) {
            if ($fn !== null) {
                $val = call_user_func($fn, $val);
            }
            if ($val) {
                return true;
            }
        }
        return false;
    }

    /**
     * 函数式：array中所有值为真才为真
     *
     * @param array $array
     * @param callable $fn
     * @return bool
     */
    public static function all($array, callable $fn = null):bool
    {
        foreach ($array as $val) {
            if ($fn !== null) {
                $val = call_user_func($fn, $val);
            }
            if (!$val) {
                return false;
            }
        }
        return true;
    }

    /**
     * 函数式：array中所有值都相同
     *
     * @param array $array
     * @param callable $val_fn 值
     * @param callable $compare_fn 比较
     * @return bool
     */
    public static function same(array $array, $val_fn = null, $compare_fn = null):bool
    {
        $fst_val = null;
        foreach ($array as $key => $val) {
            if ($val_fn !== null) {
                $val = call_user_func($val_fn, $val, $key);
            }
            if ($fst_val !== null) {
                if (($compare_fn === null) ? ($fst_val != $val) : !call_user_func($compare_fn, $fst_val, $val)) {
                    return false;
                }
            } else {
                $fst_val = $val;
            }
        }
        return true;
    }

    /**
     * 函数式：map一个array（包括键和值
     *
     * @param array $array
     * @param callable $fn 比如function($key, $value) { return array($key, $value); }
     * @return array
     */
    public static function map(array $array, $fn):array
    {
        $newArray = [];
        foreach ($array as $key => $val) {
            list($new_key, $new_value) = call_user_func($fn, $key, $val);
            $newArray[$new_key] = $new_value;
        }
        return $newArray ?: [];
    }

    /**
     * 函数式：filter一个array (包括键和值)
     *
     * @param array $array
     * @param callable $fn 比如function($key, $value) { return array($key, $value); }
     * @return array
     */
    public static function filter(array $array, callable $fn):array
    {
        $newArray = [];
        foreach ($array as $key => $val) {
            if (call_user_func($fn, $key, $val)) {
                $newArray[$key] = $val;
            }
        }
        return $newArray ?: [];
    }

    /**
     * 找一个合符的规则的元素的键
     *
     * @param $array
     * @param callable $fn
     * @return string|null
     */
    public static function find(array $array, callable $fn)
    {
        foreach ($array as $key => $val) {
            if (call_user_func($fn, $key, $val)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Find的更好的形式
     *
     * @param array $array
     * @param callable $fn
     * @param mixed $index 返回匹配到的键，null表示没有找到
     * @return mixed 返回匹配到的值，null表示没有找到
     */
    public static function first(array $array, callable $fn, &$index = null)
    {
        $index = null;
        foreach ($array as $key => $val) {
            if (call_user_func($fn, $val, $key)) { // 通常惯例闭包参数都是先值后键
                $index = $key;
                return $val;
            }
        }
        return null;
    }

    /**
     * 计算数组/结果集的总和
     *
     * @param $array
     * @param $field 如果为null，直接将数组的值相加，
     *               如果是一个字符串，则将结果集的某一列相加，
     *               如果是回调，则将数组/结果集中的元素应用该回调后再相加
     * @return float
     */
    public static function sum(array $array, $field = null)
    {
        if (empty($array)) {
            return 0;
        }
        $sum = 0;
        foreach ($array as $val) {
            if ($field === null) {
                $sum += $val;
            } elseif (is_string($field)) {
                $sum += $val[$field];
            } elseif (is_callable($field)) {
                $sum += call_user_func($field, $val);
            }
        }
        return $sum;
    }

    /**
     * 将一个键值数组转换为一个列表数据，将键名和键值分别设置为不同的字段值
     *
     * @param array  $map 键值数组
     * @param string $key_name
     * @param string $value_name
     * @return array
     */
    public static function kv2list(array $map, string $key_name = 'name', $value_name = 'value'):array
    {
        if (!is_array($map) && !is_object($map)) {
            return $map;
        }
        $rt = [];
        foreach ($map as $k => $v) {
            $rt[] = array(
                $key_name   => $k,
                $value_name => $v,
            );
        }
        return $rt ?: [];
    }

    /**
     * 从一个列表中提取一个KV数组
     *
     * @param array  $list
     * @param string $key_name     键名字段
     * @param string $value_name   值字段
     * @param bool   $filter_empty 是否过滤掉空值
     * @return array
     */
    public static function list2kv(array $list, string $key_name, $value_name, $filter_empty = false):array
    {
        $rt = [];
        if (!is_array($list)) {
            return $rt;
        }
        foreach ($list as $v) {
            if ($filter_empty && empty($v[$value_name])) {
                continue;
            }
            $rt[$v[$key_name]] = $v[$value_name];
        }
        return $rt;
    }


    /**
     * 从一个列表中提取一个KV数组，并且值的字段为数组，同时链接多个字段
     *
     * @param array  $list
     * @param string $key_name    键名字段
     * @param array  $value_names 值字段，这里是一个数组
     * @param string $con_str     要链接的字符串
     * @return array
     */
    public static function list2kvCon(array $list, $key_name, $value_names, $con_str = ''): array
    {
        $rt = [];
        if (!is_array($list)) {
            return $rt;
        }
        foreach ($list as $v) {
            $item = [];
            foreach ($value_names as $name) {
                $item[] = $v[$name];
            }
            $rt[$v[$key_name]] = implode($con_str, $item);
        }
        return $rt ?: [];
    }

    /**
     * 从列表中提取某一键名的单独数组
     *
     * @param array  $list       列表
     * @param string $value_name 键名
     * @return array
     */
    public static function list2v(array $list, $value_name):array
    {
        $rt = [];
        if (!is_array($list)) {
            return $rt;
        }
        foreach ($list as $v) {
            if (array_key_exists($value_name, $v)) {
                $rt[] = $v[$value_name];
            }
        }
        return $rt ?: [];
    }


    /**
     * 找到一个键值数组，通过值在values中的数据
     *
     * @param array $kv_list
     * @param array $values
     * @return array
     */
    public static function findKvByValues(array $kv_list, array $values):array
    {
        $rt = [];
        foreach ($kv_list as $k => $v) {
            if (in_array($v, $values)) {
                $rt[$k] = $v;
            }
        }
        return $rt ?: [];
    }

    /**
     * 将一组数组从值中设置一个唯一主键
     *
     * @param array  $list    列表
     * @param string $uni_key 唯一主键
     * @return array
     */
    public static function list2MapList(array $list, string $uni_key): array
    {
        $rt = [];
        foreach ($list as $v) {
            $rt[$v[$uni_key]] = $v;
        }
        return $rt ?: [];
    }

    /**
     * 提取一个数组中的几个值，并将对应对应的键值返回
     *
     * @param array $map     一个数组对象，非列表
     * @param array $key_map 键名列表，如果非数字序列键名，将转为别名方式,这里只要判断是int类型就当做键值索引
     * @return array
     */
    public static function getMapKv(array $map, array $key_map):array
    {
        $rt = [];
        foreach ($key_map as $k => $v) {
            if (is_int($k)) {
                $rt[$v] = array_key_exists($v, $map) ? $map[$v] : null;
            } else {
                $rt[$v] = array_key_exists($k, $map) ? $map[$k] : null;
            }
        }
        return $rt;
    }

    /**
     * 提取一个数组中的几个值，并将对设置为对应的键值，并返回
     *
     * @param array $list    一组对象列表
     * @param array $key_map 键名列表，如果非数字序列键名，将转为别名方式,这里只要判断是int类型就当做键值索引
     * @return array
     */
    public static function rebuildListMapKv(array $list, $key_map): array
    {
        $rt = [];
        foreach ($list as $_k => $_v) {
            if (!isset($rt[$_k])) {
                $rt[$_k] = [];
            }
            foreach ($key_map as $k => $v) {
                if (is_int($k)) {
                    $rt[$_k][$v] = array_key_exists($v, $_v) ? $_v[$v] : null;
                } else {
                    $rt[$_k][$v] = array_key_exists($k, $_v) ? $_v[$k] : null;
                }
            }
        }
        return $rt;
    }

    /**
     * 将列表转换为一个键名关联的二维数组，提取field的公共键名，值一致的作为一组
     *
     * @param array  $list
     * @param string $filed
     * @return array
     */
    public static function list2vArray(array $list, $filed): array
    {
        $rt = [];
        if (!is_array($list)) {
            return $list;
        }
        foreach ($list as $v) {
            $key = $v[$filed];
            if (!isset($rt[$key])) {
                $rt[$key] = [];
            }
            $rt[$key][] = $v;
        }
        return $rt;
    }

    /**
     * 将一个数组列表对象中的几个字段转换为数值类型
     *
     * @param array           $list        列表
     * @param array           $field_list  字段列表
     * @param string|callable $numberCall 自定义的转义函数
     */
    public static function listFiled2number(array &$list, $field_list, $numberCall = 'doubleval')
    {
        if (!is_array($list)) {
            return;
        }
        foreach ($list as &$item) {
            self::mapFiled2Number($item, $field_list, $numberCall);
        }
    }

    /**
     * 将一个对象中的几个字段的值转为数字类型
     *
     * @param array           $map         对象
     * @param array           $field_list  字段列表
     * @param string|callable $numberCall 自定义转换函数
     */
    public static function mapFiled2Number(array &$map, array $field_list, $numberCall = 'doubleval')
    {
        if (!is_callable($numberCall) || !is_array($map)) {
            return;
        }
        foreach ($field_list as $k) {
            if (array_key_exists($k, $map)) {
                $map[$k] = call_user_func($numberCall, $map[$k]);
            }
        }
    }

    /**
     * 对一个数组进行键名排序，排序方式保持和sort_arr的一致，如果不再sort_arr中的放在后面
     *
     * @param array $map      集合列表
     * @param array $sort_arr 值列表
     * @return array
     */
    public static function sortKeyByCustomArray(array $map, array $sort_arr): array
    {
        $rt = [];
        foreach ($sort_arr as $k) {
            if (array_key_exists($k, $map)) {
                $rt[$k] = $map[$k];
                unset($map[$k]);
            }
        }
        return array_merge($rt, $map);
    }

    /**
     * 对一个二维数组排序，根据多个key和升降序排序
     * ```
     * sort_by_keys([
     *       ['a' => '1', 'b' => '2'],
     *       ['a' => '2', 'b' => '1']
     *   ], ['a' => SORT_ASC, 'b' => SORT_DESC]);
     * ```
     * @param array $array
     * @param array $keys
     * @return array
     */
    public static function sortByKeys(array $array, array $keys): array
    {
        if (empty($array) || empty($keys)) {
            return $array;
        }
        usort($array, function ($a, $b) use ($keys) {
            foreach ($keys as $key => $sort) {
                $a[$key] = $a[$key] ?: 0;
                $b[$key] = $b[$key] ?: 0;

                if ($a[$key] != $b[$key]) {
                    if ($sort == SORT_DESC) {
                        return $a[$key] < $b[$key] ? 1 : -1;
                    }
                    return $a[$key] > $b[$key] ? 1 : -1;
                }
            }
            return 0;
        });
        return $array ?: [];
    }

    /**
     * 根据键合并多个数组
     * ```
     * merge_arrays([['a' => 1, 'b' => 2], ['a' => 2, 'b' => 3]], ['x', 'y']);
     * =>
     * ['a' => ['x' => 1, 'y' => 2], 'b' => ['x' => '2', 'y' => '3']]
     * ```
     * 
     * @param array $arrays
     * @param array $inline_keys
     * @return array
     */
    public static function mergeArrays(array $arrays, array $inline_keys): array
    {
        if (empty($arrays)) {
            return [];
        }
        $newArray = [];
        foreach ($arrays[0] as $key => $_) {
            foreach ($inline_keys as $index => $inline_key) {
                if (empty($newArray[$key])) {
                    $newArray[$key] = [];
                }
                $newArray[$key][$inline_key] = $arrays[$index][$key];
            }
        }
        return $newArray;
    }

    /**
     * 函数式常用函数flatten
     *
     * @param array $array
     * @return array
     */
    public static function flatten(array $array): array
    {
        $newArray = [];
        foreach ($array as $sub_array) {
            $newArray = array_merge($newArray, $sub_array);
        }
        return $newArray;
    }

    /**
     * 排序多维数组
     *
     * @param array $itemArr
     * @param array $sortRule
     * @return void
     */
    public static function aAsort(array &$itemArr, array $sortRule)
    {
        $num = count($sortRule);
        if ($num == 1) {
            $op_1 = substr($sortRule[0], 0, 1) == '+' ? SORT_ASC : SORT_DESC;
            $key_1 = substr($sortRule[0], 1);
            $val_1 = [];
            foreach ($itemArr as $value) {
                $val_1[] = $value[$key_1];
            }
            \array_multisort($val_1, $op_1, $itemArr);
        } else {
            if ($num == 2) {
                $op_1 = substr($sortRule[0], 0, 1) == '+' ? SORT_ASC : SORT_DESC;
                $key_1 = substr($sortRule[0], 1);
                $op_2 = substr($sortRule[1], 0, 1) == '+' ? SORT_ASC : SORT_DESC;
                $key_2 = substr($sortRule[1], 1);
                $val_1 = [];
                $val_2 = [];
                foreach ($itemArr as $value) {
                    $val_1[] = $value[$key_1];
                    $val_2[] = $value[$key_2];
                }
                \array_multisort($val_1, $op_1, $val_2, $op_2, $itemArr);
            } else {
                if ($num == 3) {
                    $op_1 = substr($sortRule[0], 0, 1) == '+' ? SORT_ASC : SORT_DESC;
                    $key_1 = substr($sortRule[0], 1);
                    $op_2 = substr($sortRule[1], 0, 1) == '+' ? SORT_ASC : SORT_DESC;
                    $key_2 = substr($sortRule[1], 1);
                    $op_3 = substr($sortRule[2], 0, 1) == '+' ? SORT_ASC : SORT_DESC;
                    $key_3 = substr($sortRule[2], 1);
                    $val_1 = [];
                    $val_2 = [];
                    $val_3 = [];
                    foreach ($itemArr as $value) {
                        $val_1[] = $value[$key_1];
                        $val_2[] = $value[$key_2];
                        $val_3[] = $value[$key_3];
                    }
                    \array_multisort($val_1, $op_1, $val_2, $op_2, $val_3, $op_3, $itemArr);
                }
            }
        }
    }

    /**
     * 对某个二维数组按key排序
     *
     * @param array $array 需要排序的数组
     * @param string $key  排序字段
     * @param int $type 排序类型,例如: SORT_DESC,SORT_ASC
     * @return void
     */
    public static function sortArray(&$array, $key, $type=SORT_ASC)
    {
        if (!is_array($array)) {
            return 0;
        }
        $sort = [];
        foreach ($array as $data) {
            $sort[] = isset($data[$key]) ? $data[$key] : '';
        }
        \array_multisort($sort, $type, $array);
    }

    /**
     * 将数组的某个属性作为键值
     *
     * @param array $array
     * @param string $key 该key的值作为key
     * @param bool $group 分组
     * @return array
     */
    public static function arrayKeyFill($array, $key, $group = false)
    {
        $newArray = [];
        if (is_array($array) && $array) {
            if ($group) {
                foreach ($array as $_v) {
                    $newArray[$_v[$key]][] = $_v;
                }
            } else {
                foreach ($array as $_v) {
                    if (!isset($newArray[$_v[$key]])) {
                        $newArray[$_v[$key]] = $_v;
                    }
                }
            }
        }
        return $newArray;
    }

    /**
     * 删除数组中某个值
     *
     * @param array $array
     * @param mixed $val
     * @return void
     */
    public static function arrayRemoveVal(&$array, $val)
    {
        foreach ($array as $key=>$_v) {
            if ($val===$_v) {
                unset($array[$key]);
                break;
            }
        }
    }

    /**
     * PHP随机合并数组并保持原排序
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function shuffleMergeArray($array1, $array2)
    {
        if (empty($array1)) {
            return $array2;
        }
        if (empty($array2)) {
            return $array1;
        }
        $mergeArray = [];
        $sum = count($array1) + count($array2);
        for ($k = $sum; $k > 0; $k--) {
            $number = mt_rand(0, 100) % 2;
            if ($number == 1) {
                $mergeArray[] = $array2 ? array_shift($array2) : array_shift($array1);
            } else {
                $mergeArray[] = $array1 ? array_shift($array1) : array_shift($array2);
            }
        }
        return $mergeArray;
    }

    /**
     * 根据key来去除二维数组的重复值
     *
     * @param array $array
     * @param string $key_name
     * @return array
     */
    public static function arrayUniqueMulti($array, $key_name)
    {
        if (empty($array) || empty($key_name)) {
            return [];
        }
        $temp_arr = [];
        foreach ($array as $v) {
            $temp_arr[$v[$key_name]]=$v;
        }
        return  array_values($temp_arr);
    }

    /**
     * 返回数组第一个key
     * @doc https://www.php.net/manual/zh/function.array-key-first.php
     *
     * @param array $array
     * @return string
     */
    public static function arrayKeyFirst(array $array)
    {
        if (function_exists('array_key_first')) {
            return \array_key_first($array);
        }
        foreach ($array as $key => $unused) {
            return $key;
        }
        return null;
    }

    /**
     * 返回数组最后一个key
     * @doc https://www.php.net/manual/zh/function.array-key-last.php
     *
     * @param array $array
     * @return string
     */
    public static function arrayKeyLast(array $array)
    {
        if (function_exists('array_key_last')) {
            return \array_key_last($array);
        }
        if (!is_array($array) || empty($array)) {
            return null;
        }
        return array_keys($array)[count($array)-1];
    }

    /**
     * 去除数组array里面，以key为健的值的重复项，并保留其中一个
     *
     * @param array $array
     * @param string $key
     * @return string
     */
    public static function arrayUnique(array $array, $key)
    {
        $temp=[];
        foreach ($array as $k=>$v) {
            $temp[strtolower($v[$key])]=$k;
        }
        $temp2=[];
        foreach ($temp as $v) {
            $temp2[]=$array[$v];
        }
        return $temp2;
    }

    /**
     * 循环替换数组的值
     *
     * @param array $array
     * @param array $replaceArr
     * @param array $replaceKeyArr
     * @param array $replaceValArr
     * @return array
     */
    public static function loopReplace(array $array, array $replaceArr, array $replaceKeyArr=[], array $replaceValArr=[])
    {
        if(empty($replaceArr)){
            return $array;
        }
        $replaceKeyArr = $replaceKeyArr ?: array_keys($replaceArr);
        $replaceValArr = $replaceValArr ?: array_values($replaceArr);
        $newArray = [];
        foreach($array as $key=>$val){
            if(is_array($val)){
                $val = self::loopReplace($val, $replaceArr, $replaceKeyArr, $replaceValArr);
            }
            if(!is_numeric($key)){
                $key = str_replace($replaceKeyArr, $replaceValArr, $key);
            }
            if(!is_string($val)){
                $newArray[$key] = $val;
            }elseif(isset($replaceArr[$val])){ // 如果结果值与替换key一致，则直接赋值
                $newArray[$key] = $replaceArr[$val];
            }else{
                $newArray[$key] = str_replace($replaceKeyArr, $replaceValArr, $val);
            }
        }
        return $newArray;
    }

    /**
     * 删除多维数组中某个键
     *
     * @param array $array
     * @param string $keys 键值，多个用逗号分割
     * @return array
     */
    public static function arrayRemoveMulKey(&$array, $keyStr){
        if(empty($array) || empty($keys)){
            return $array;
        }
        $keys = explode(',',$keyStr);
        foreach($array as $_key => $_v){
            foreach($keys as $_remove_key){
                unset($array[$_key][$_remove_key]);
            }
        }
    }

    /**
     * 根据键值在数组获取数据
     * 
     * @param array $arr
     * @param string $keyField 多个使用逗号分割
     * @return array
     */
    public static function arrayFilterByKeys($arr,$keyField='')
    {
        if(empty($arr) || empty($keyField)){
            return $arr;
        }
        $keys = explode(',',$keyField);
        foreach($arr as $key => $val){
            if(!in_array($key,$keys)){
                unset($arr[$key]);
            }
        }
        return $arr;
    }

}
