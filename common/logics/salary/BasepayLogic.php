<?php
/**
 * Created by PhpStorm.
 * User: huangjun
 * Date: 2017/1/3
 * Time: 下午3:18
 */
namespace common\logics\salary;

use common\widgets\BinaryDecimal;
use Yii;
use yii\base\Object;

class BasepayLogic extends Object implements IBasepay
{
    /** @var  \common\sources\read\salary\BasepayAccess  $RBasepayAccess */
    private $RBasepayAccess;
    /** @var  \common\sources\write\salary\BasepayAccess  $WBasepayAccess */
    private $WBasepayAccess;
    /** @var  \common\compute\SalaryCompute $salaryCompute */
    private $salaryCompute;
    /** @var  \common\sources\read\teacher\TeacherAccess $RTeacherAccess */
    private $RTeacherAccess;

    public function init()
    {
        $this->RBasepayAccess = Yii::$container->get('RBasepayAccess');
        $this->WBasepayAccess = Yii::$container->get('WBasepayAccess');
        $this->salaryCompute = Yii::$container->get('salaryCompute');
        $this->RTeacherAccess = Yii::$container->get('RTeacherAccess');

        parent::init();
    }

    public function getTeacherDaySalaryByWeek($request)
    {
        $teacher_id = $request['teacher_id'];

        $time_list = $request['time_list'];

        $fix_bit = 562949953421311;

        $time_bit = BinaryDecimal::decimalToBinary($fix_bit, $time_list);

        if ($time_bit == false) {
            $data['error'] = '时间格式错误';
            $data['salary'] = '';

            return array('error' => '时间格式错误', 'data' => '');
        } else {

            $long = BinaryDecimal::getFixLong($time_bit);

            $time_day = strtotime(date('Y-m-d', time()));

            $hour_fee = $this->salaryCompute->computeHourFee($teacher_id, $time_day)['data'];

            $money = round(($long / 2) * $hour_fee, 2);

            return array('error' => 0, 'data' => $money);
        }
    }

    public function getTeacherBasePay($teacher_id)
    {
        $teacher_info = $this->RBasepayAccess->getTeacherBasePay($teacher_id);

        return $teacher_info;
    }

    public function getInstrumentBaseSalary()
    {
        $time_day = strtotime(date('Y-m-d', time()));
        $instrument = $this->RTeacherAccess->getInstrument();
        foreach ($instrument as $key => $item) {
            $instrument[$key]['check'] = 0;
            $instrument[$key]['grade'] = 1;
            $instrument[$key]['level'] = 1;
            $salary = $this->RBasepayAccess->getBasicSalaryByGrade(1, 0, 1, 1, $time_day);
            if (empty($salary)) {
                $instrument[$key]['25'] = number_format(0, 2, '.', '');
                $instrument[$key]['45'] = number_format(0, 2, '.', '');
                $instrument[$key]['50'] = number_format(0, 2, '.', '');
                $instrument[$key]['salary_after'] = number_format(0, 2, '.', '');
            } else {
                $instrument[$key]['25'] = $salary['class_hour_first'];
                $instrument[$key]['45'] = $salary['class_hour_second'];
                $instrument[$key]['50'] = $salary['class_hour_third'];
                $instrument[$key]['salary_after'] = $salary['salary_after'];
            }

            $instrument[$key]['25_we'] = number_format(0, 2, '.', '');
            $instrument[$key]['45_we'] = number_format(0, 2, '.', '');
            $instrument[$key]['50_we'] = number_format(0, 2, '.', '');
            $instrument[$key]['salary_we'] = number_format(0, 2, '.', '');
        }
        return $instrument;
    }

    public function addSalaryByInstrument($request)
    {
        $teacher_id = $request['teacher_id'];

        $instrument = $request['instrument'];

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $this->WBasepayAccess->deleteInstrumentSalary($teacher_id);

            foreach ($instrument as $item) {
                $this->WBasepayAccess->addInstrumentSalary($teacher_id, $item['instrument_id'], $item['grade'], $item['level'], $item['hour_first'], $item['hour_second'], $item['hour_third'], $item['salary']);

                $this->WBasepayAccess->addInstrumentSalaryLog($teacher_id, $item['instrument_id'], $item['grade'], $item['level'], $item['hour_first'], $item['hour_second'], $item['hour_third'], $item['salary']);
            }

            $transaction->commit();

            return array('error' => 0, 'data' => '');

        } catch (Exception $e) {

            $transaction->rollBack();

            return array('error' => '添加失败！', 'data' => '');
        }
    }

    public function getTeacherInstrumentLog($teacher_id)
    {
        $list = $this->RBasepayAccess->getTeacherInstrumentLog($teacher_id);

        return array('error' => 0, 'data' => $list);
    }

    public function getSalaryByInstrument($teacher_id, $grade, $level)
    {
        $time_day = strtotime(date('Y-m-d', time()));

        $teacher_info = $this->RTeacherAccess->getTeacherInfoById($teacher_id);

        $info = $this->RBasepayAccess->getBasicSalaryByGrade($teacher_info['teacher_type'], $teacher_info['school_id'], $grade, $level, $time_day);

        if (empty($info)) {
            $info['salary_after'] = 0;
            $info['class_hour_first'] = 0;
            $info['class_hour_second'] = 0;
            $info['class_hour_third'] = 0;
        }

        return array('error' => 0, 'data' => $info);
    }

    public function getSalaryByInstrumentType($teacher_type, $school_id, $grade, $level)
    {
        $time_day = strtotime(date('Y-m-d', time()));

        if ($teacher_type == 1) {
            $school_id = 0;
        }
        $info = $this->RBasepayAccess->getBasicSalaryByGrade($teacher_type, $school_id, $grade, $level, $time_day);

        if (empty($info)) {
            $info['salary_after'] = number_format(0, 2, '.', '');
            $info['class_hour_first'] = number_format(0, 2, '.', '');
            $info['class_hour_second'] = number_format(0, 2, '.', '');
            $info['class_hour_third'] = number_format(0, 2, '.', '');
        }

        return array('error' => 0, 'data' => $info);
    }

}