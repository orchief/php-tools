<?php

namespace Utility\Db;

trait DataList
{
    public function parseParam($Model)
    {
        //模糊查询
        if ($this->fuzzyConditions) {
            foreach ($this->fuzzyConditions as $k => $v) {
                if (array_key_exists($v, $this->param) && $this->param[$v]) {
                    $Model = $Model->where($this->name . '.' . $v, 'like', '%' . $this->param[$v] . '%');
                }
            }
        }

        // 精确查询
        if ($this->clearConditions) {

            foreach ($this->clearConditions as $k => $v) {
                if (array_key_exists($v, $this->param) && $this->param[$v]) {
                    $Model = $Model->where([$this->name . '.' . $v => $this->param[$v]]);
                }
            }
        }

        // 范围
        if ($this->rangeConditions) {
            foreach ($this->rangeConditions as $k => $v) {
                if (array_key_exists($v, $this->param) && $this->param[$v]) {
                    $temp_arr = explode('--', $this->param[$v]);
                    $temp_min = $temp_arr[0];
                    $temp_max = $temp_arr[1];
                    unset($temp_arr);
                    $Model = $Model->where('' . $this->name . '.' . $v, '>=', $temp_min)->where('' . $this->name . '.' . $v, '<=', $temp_max);
                }
            }
        }

        // 排序
        if ($this->orderByString) {
            $orderArr = explode('.', $this->orderByString);
            if (!isset($this->union_fuzzyConditions)) {
                if ($orderArr[1] == 'descending') {
                    $Model = $Model->order('' . $this->name . '.' . $orderArr[0] . ' DESC');
                } else {
                    $Model = $Model->order('' . $this->name . '.' . $orderArr[0]);
                }
            } else {
                $union = $this->union_fuzzyConditions;
                foreach ($union as $k => $v) {
                    if ($v[1] == $orderArr[0]) {
                        if ($orderArr[1] == 'descending') {
                            $Model = $Model->order($v[0] . '.' . $orderArr[0] . ' DESC');
                        } else {
                            $Model = $Model->order($v[0] . '.' . $orderArr[0]);
                        }
                    } else {
                        if ($orderArr[1] == 'descending') {
                            $Model = $Model->order('' . $this->name . '.' . $orderArr[0] . ' DESC');
                        } else {
                            $Model = $Model->order('' . $this->name . '.' . $orderArr[0]);
                        }
                    }
                }
            }
        }

        // 联合查询
        if ($this->foreignConditions) {
            foreach ($this->foreignConditions as $k => $v) {
                if (isset($v[2])) {
                    $Model = $Model->join($k, $k . '.' . $v[0] . '=' . $v[2] . '.' . $v[1], 'LEFT');
                } else {
                    $arr = explode('.', $v[1]);
                    if (count($arr) == 2) {
                        $Model = $Model->join($k, $k . '.' . $v[0] . '=' . $v[1], 'LEFT');
                    } else if (count($arr) == 1) {
                        $Model = $Model->join($k, $k . '.' . $v[0] . '=' . '' . $this->name . '.' . $v[1], 'LEFT');
                    }
                }
            }
        }

        // 联合模糊字段查询
        if ($this->union_fuzzyConditions) {
            $union_fuzzys = $this->union_fuzzyConditions;
            foreach ($union_fuzzys as $k => $v) {
                if (isset($this->param[$v[1]])) {
                    $Model = $Model->where($v[0] . '.' . $v[1], 'like', '%' . $this->param[$v[1]] . '%');
                }

            }
        }

        return $Model;
    }

    /**
     * 将下划线命名转换为驼峰式命名
     */
    public function convertUnderline($str, $ucfirst = true)
    {
        $str = explode('_', $str);
        foreach ($str as $key => $val) {
            $str[$key] = ucfirst($val);
        }

        if (!$ucfirst) {
            $str[0] = strtolower($str[0]);
        }

        return implode('', $str);
    }
}
