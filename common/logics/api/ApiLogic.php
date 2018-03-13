<?php

namespace common\logics\api;

use Yii;
use yii\base\Object;
use common\models\PnlQrCode;
use common\models\PnlCodeUsed;
use common\models\music\UserInit;

class ApiLogic extends Object implements IApi
{
    public function getQrCodeNum()
    {
        return PnlQrCode::find()->count();
    }

    public function getQrCodeNumByType($type = 0)
    {
        return PnlQrCode::find()
                ->where('type = 0')
                ->count();
    }

    public function assignQrcode($type, $channelId)
    {
        if (!$type || !$channelId) {
            return ['error' => '参数错误'];
        }
        if ($this->getQrCodeNumByType()) {
            $arr =$this->getPnlQrCode();
            $pnlQrCode = $arr[0];
            $original_id = $arr[1];
            //使用记录
            $model = new PnlCodeUsed;
            $model->qr_id = $pnlQrCode->id;
            $model->original_id = $original_id;
            $model->mapped_id = $channelId;
            $model->created_time = time();
            $model->status = 1;

            $pnlQrCode->type = $type;

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $pnlQrCode->save();
                $model->save();
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                return ['error' => '分配失败，请刷新后再尝试'];
            }
            return ['error' => '', 'data' => $pnlQrCode->weicode_path];
        } else {
            return ['error' => '分配失败,闲置二维码未找到'];
        }
    }

    private function getPnlQrCode($id = 0)
    {
        $pnlQrCode = PnlQrCode::find()
                ->where("type = 0 AND id<> 834 AND id> $id")
                ->orderBy('id ASC')
                ->one();
        $exp = explode('_', $pnlQrCode->event_key);
        $original_id = end($exp);
        if ($pnlQrCode) {
            //此处做双重判断 分配记录和使用记录
            $isused = PnlCodeUsed::find()
                        ->where("original_id =:original_id AND status = 1", [
                            ':original_id'=>$original_id
                            ])
                        ->one();
            $userInit = UserInit::find()
                        ->where('sales_id=:sales_id', [':sales_id'=>$original_id])
                        ->one();
            if ($isused || $userInit) {
                $this->getPnlQrCode($pnlQrCode->id);
            } else {
                return [$pnlQrCode, $original_id];
            }
        }
    }
}
