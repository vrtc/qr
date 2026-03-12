<?php

namespace app\controllers;

use app\models\Link;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * QrController handles QR code generation.
 */
class QrController extends Controller
{
    /**
     * Generates QR code for a known short link by its code.
     * @param string $code
     * @return Response
     */
    public function actionIndex($code)
    {
        $link = Link::find()->where(['short_code' => $code])->one();

        if (!$link) {
            throw new NotFoundHttpException('Короткая ссылка не найдена.');
        }

        $shortUrl = $link->getShortUrl();

        \Yii::$app->response->format = Response::FORMAT_RAW;
        \Yii::$app->response->headers->add('Content-Type', 'image/png');

        $qrCode = new QrCode(
            data: $shortUrl,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return $result->getString();
    }
}
