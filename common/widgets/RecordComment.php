<?php
/**
 * Created by PhpStorm.
 * User: Jhu
 * Date: 16/12/20
 * Time: 下午4:49
 */
namespace common\widgets;

class RecordComment {

    public static function getRecordStudentDes($process)
    {
        switch ($process['performance'])
        {
            case 0 :
                $performance = '暂无';
                break;
            case 1 :
                $performance = '很差';
                break;
            case 2 :
                $performance = '一般';
                break;
            case 3 :
                $performance = '尚可';
                break;
            case 4 :
                $performance = '较好';
                break;
            case 5 :
                $performance = '很好';
                break;
            default :
                $performance = '很好';
                break;
        }

        switch ($process['note_accuracy'])
        {
            case 0 :
                $note_accuracy = '暂无';
                break;
            case 1 :
                $note_accuracy = '很差';
                break;
            case 2 :
                $note_accuracy = '一般';
                break;
            case 3 :
                $note_accuracy = '尚可';
                break;
            case 4 :
                $note_accuracy = '较好';
                break;
            case 5 :
                $note_accuracy = '很好';
                break;
            default :
                $note_accuracy = '很好';
                break;
        }

        switch ($process['rhythm_accuracy'])
        {
            case 0 :
                $rhythm_accuracy = '暂无';
                break;
            case 1 :
                $rhythm_accuracy = '很差';
                break;
            case 2 :
                $rhythm_accuracy = '一般';
                break;
            case 3 :
                $rhythm_accuracy = '尚可';
                break;
            case 4 :
                $rhythm_accuracy = '较好';
                break;
            case 5 :
                $rhythm_accuracy = '很好';
                break;
            default :
                $rhythm_accuracy = '很好';
                break;
        }

        switch ($process['coherence'])
        {
            case 0 :
                $coherence = '暂无';
                break;
            case 1 :
                $coherence = '很差';
                break;
            case 2 :
                $coherence = '一般';
                break;
            case 3 :
                $coherence = '尚可';
                break;
            case 4 :
                $coherence = '较好';
                break;
            case 5 :
                $coherence = '很好';
                break;
            default :
                $coherence = '很好';
                break;
        }

        return [$performance, $note_accuracy, $rhythm_accuracy, $coherence];
    }
}
