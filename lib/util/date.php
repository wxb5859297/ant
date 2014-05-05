<?php
/**
 * 日期处理类
 * @author wuxiabing
 *
 */
class lib_util_date
{
    /**
     * 获取日期天数
     */
    static function getDates($date, $format = 'Y-m-d', $days = 1, $step = 1)
    {
        $dates = array();
        $current_date = date($format, strtotime($date));
        $days = ($days >= 1 && is_int($days)) ? $days : 1;
        $step = ($step >= 1 && is_int($step)) ? $step : 1;
        if ($days == 1) {
            return $current_date;
        } else {
            $dates[] = $current_date;
            while ($days > 1) {
                $current_date = date($format, strtotime($current_date) - $step * 86400);
                $dates[] = $current_date;
                $days--;

            }
            return $dates;
        }
    }

    /**
     * 获取工作日期
     * 隔天获取的时间，如果是工作日，照样获取
     * 限制7天内的步长
     */
    static function getBusinessDates($date, $format = 'Y-m-d', $days = 1, $step = 1)
    {
        $dates = array();
        $date = empty($date) ? date($format) : $date;
        $days = ($days >= 1 && is_int($days)) ? $days : 1;
        $step = ($step >= 1 && is_int($step)) ? $step : 1;
        if ($step > 7) {
            return $dates;
        }
        $current_date = date($format, strtotime($date));
        if (!self::checkBusinessDate($current_date)) {
            if ($step % 7 == 0) {
                return $dates;
            } else {
                $current_date = date($format, strtotime($current_date) - (7 - date('N', strtotime($current_date))) * 86400);
            }
        }
        $dates[] = $current_date;
        while ($days > 1) {
            $current_date = date($format, strtotime($current_date) - $step * 86400);
            if (self::checkBusinessDate($current_date)) {
                $dates[] = $current_date;
                $days--;
            } else {
                if ($step >= 3) {
                    $n = date('N', strtotime($current_date));
                    if ($n == 6) {
                        $current_date = date($format, strtotime($current_date) + 86400);
                    }
                    if ($n == 7) {
                        $current_date = date($format, strtotime($current_date) + 2 * 86400);
                    }
                }
            }
        }
        return $dates;
    }

    /**
     * 检查是否是工作日
     */
    static function checkBusinessDate($date)
    {
        if (!empty($date)) {
            $n = date('N', strtotime($date));
            return ($n <= 5);
        } else {
            return false;
        }
    }

    /**
     * 获取某年的自然月季度
     * 默认取自然月第一天
     */
    static function getSeasonMonth($year = null, $day = 1)
    {
        $season = array();
        $months = self::getMonths($year, $day);
        $season[] = date('Y-m-d', strtotime($months[0]));
        $season[] = date('Y-m-d', strtotime($months[3]));
        $season[] = date('Y-m-d', strtotime($months[6]));
        $season[] = date('Y-m-d', strtotime($months[9]));
        return $season;
    }

    /**
     * 获取月份
     * 默认去当年已过月份
     * day可以控制时间在月份中的第几天
     */
    static function getMonths($year = null, $day = null)
    {
        $num = 1;
        $months = array();
        $year = empty($year) ? date('Y') : $year;
        while ($num <= 12) {
            if (isset($day)) {
                $day = (is_numeric($day) && ($day >= 0) && ($day <= 31)) ? $day : 1;
                $month = ($day == 0) ? $num + 1 : $num;
                $months[] = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
            } else {
                $month = ($num < 10) ? '0' . $num : $num;
                $months[] = $year . '-' . $month;
            }
            $num++;
        }
        return $months;
    }

    /**
     * 获取股市报告期月份
     * 股市默认Ymd报告期
     */
    static function getReportSeasonMonth($year = null, $format = 'Ymd')
    {
        $report = array();
        $months = self::getMonths($year, 0);
        $report[] = date($format, strtotime($months[2]));
        $report[] = date($format, strtotime($months[5]));
        $report[] = date($format, strtotime($months[8]));
        $report[] = date($format, strtotime($months[11]));
        return $report;
    }

    /**
     * 获取股市去年同期报告期
     */
    static function getLastYearSeason($report)
    {
        return self::getSeasonByStep($report, 4);
    }

    /**
     * 获取股市上一个报告期
     */
    static function getLastSeason($report)
    {
        return self::getSeasonByStep($report);
    }

    /**
     * 根据步长获取季度
     */
    static function getSeasonByStep($report, $step = 1)
    {
        if ($report) {
            $step = is_numeric($step) ? (int)$step : 1;
            $report_time = strtotime($report);
            $year = date('Y', $report_time);
            $month = date('m', $report_time);
            $date = date('d', $report_time);
            return date('Ymd', mktime(0, 0, 0, $month - 3 * $step, $date, $year));
        } else {
            return false;
        }
    }
}
