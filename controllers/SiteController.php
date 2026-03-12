<?php

namespace app\controllers;

use Yii;
use app\models\Link;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\helpers\Url;
use yii\web\ErrorAction;

/**
 * SiteController handles the main site actions.
 */
class SiteController extends Controller
{
    public function actions()
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
        ];
    }

    /**
     * Displays the home page with the URL input form.
     * @return string
     */
    public function actionIndex()
    {
        $model = new Link();
        return $this->render('index', ['model' => $model]);
    }

    /**
     * Validates and creates a short link via AJAX.
     * @return string
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Неверный запрос.'];
        }

        $model = new Link();
        $model->original_url = Yii::$app->request->post('url');
        $model->short_code = Link::generateShortCode();

        if ($model->save()) {
            return [
                'success'      => true,
                'short_url'    => $model->getShortUrl(),
                'short_code'   => $model->short_code,
                'original_url' => $model->original_url,
            ];
        }

        return [
            'success' => false,
            'errors'  => $model->getErrors(),
        ];
    }

    /**
     * Redirects from short URL to original URL.
     * @param string $code
     * @return Response
     */
    public function actionRedirect($code)
    {
        $link = Link::find()->where(['short_code' => $code])->one();

        if (!$link) {
            throw new NotFoundHttpException('Короткая ссылка не найдена.');
        }

        // Record click
        $ip = Yii::$app->request->userIP;
        $userAgent = Yii::$app->request->userAgent;
        $link->recordClick($ip, $userAgent);

        // Safety check: only redirect to http/https URLs
        $scheme = parse_url($link->original_url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new NotFoundHttpException('Некорректный URL.');
        }

        // Redirect to original URL
        return $this->redirect($link->original_url, 301);
    }
}
