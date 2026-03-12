<?php

use yii\helpers\Html;
use yii\helpers\Url as HelpersUrl;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $url string */

$this->title = 'Short Link Service';
?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent">
        <h1 class="display-4">Short Link Service</h1>
        <p class="lead">Создайте короткую ссылку с QR-кодом</p>
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-12">
                <?php $form = ActiveForm::begin(['id' => 'create-form']); ?>
 
                    <?= $form->field($model, 'original_url')->textInput(['maxlength' => true, 'placeholder' => 'Введите URL', 'id' => 'link-url'])->label('URL') ?>
                 
                    <?= Html::submitButton('ОК', ['class' => 'btn btn-primary mt-3', 'id' => 'submit-btn', 'style' => 'width: 100%;']) ?>
 

                <?php ActiveForm::end(); ?>

                <div id="result" class="mt-3"></div>
            </div>
        </div>

    </div>
</div>

<?php

$url = HelpersUrl::to(['site/create']);
$qrRoute = HelpersUrl::to(['qr/index']);

$js = <<< JS
$('#create-form').on('submit', function(e) {
    e.preventDefault();
    
    var url = $('#link-url').val();
    var submitBtn = $('#submit-btn');
    var resultDiv = $('#result');
    
    submitBtn.prop('disabled', true).text('Обработка...');
    
    $.ajax({
        url: '{$url}',
        method: 'POST',
        data: {url: url, _csrf: yii.getCsrfToken()},
        success: function(response) {
            if (response.success) {
                var sep = '{$qrRoute}'.indexOf('?') === -1 ? '?' : '&';
                var qrUrl = '{$qrRoute}' + sep + 'code=' + encodeURIComponent(response.short_code);
                var safeOriginal = $('<span>').text(response.original_url).html();
                resultDiv.html('\
                    <div class="alert alert-success">\
                        <strong>Ссылка создана!</strong><br>\
                        <strong>Короткая ссылка:</strong> <a href="' + response.short_url + '" target="_blank">' + response.short_url + '</a><br>\
                        <strong>Оригинал:</strong> ' + safeOriginal + '<br>\
                        <img src="' + qrUrl + '" alt="QR Code" style="width: 200px; height: 200px;">\
                    </div>\
                ');
            } else {
                var errorText = '';
                if (response.errors) {
                    $.each(response.errors, function(field, messages) {
                        errorText += messages.join('<br>') + '<br>';
                    });
                }
                if (!errorText) {
                    errorText = response.message;
                }
                resultDiv.html('<div class="alert alert-danger">' + errorText + '</div>');
            }
        },
        error: function() {
            resultDiv.html('<div class="alert alert-danger"><strong>Ошибка:</strong> Не удалось обработать запрос</div>');
        },
        complete: function() {
            submitBtn.prop('disabled', false).text('ОК');
        }
    });
});
JS;

$this->registerJs($js);
?>