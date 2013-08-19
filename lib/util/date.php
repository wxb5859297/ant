<?php
/**
 * 日期处理类
 * @author wuxiabing
 * @date 13-8-6 晚上11:00
 */
class lib_util_date
{

    static function getMonth()
    {

    }

    /**获取日期天数
     * @param $date
     * @param string $format
     * @param int $days
     * @param int $step
     * @return array|string
     */
    static function getDates($date, $format = 'Y-m-d', $days = 1, $step = 1)
    {
        $dates = array();
        $currentDate = date($format, strtotime($date));
        $days = ($days >= 1 && is_int($days)) ? $days : 1;
        $step = ($step >= 1 && is_int($step)) ? $step : 1;
        if ($days == 1) {
            return $currentDate;
        } else {
            $dates[] = $currentDate;
            while ($days > 1) {
                $currentDate = date($format, strtotime($currentDate) - $step * 86400);
                $dates[] = $currentDate;
                $days--;

            }
            return $dates;
        }
    }

    static function getSeasonMonth($year)
    {
        $year = empty($year) ? date('Y') : $year;
        $currentYear = date('Y');

    }

    static function getMonths()
    {

    }

    static function getReportSeasonMonth()
    {
    }
}