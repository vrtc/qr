<?php

namespace app\models;

use Yii;
use yii\db\Exception;
use yii\helpers\Url;

/**
 * This is the model class for table "links".
 *
 * @property int $id
 * @property string $short_code
 * @property string $original_url
 * @property int $click_count
 * @property string|null $ip_last
 * @property string $created_at
 */
class Link extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%links}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[  'original_url'], 'required'],
            [['original_url'], 'url', 'defaultScheme' => 'http'],
            [['original_url'], 'validateUrlAccessibility'],
            [['short_code'], 'string', 'max' => 10],
            [['short_code'], 'unique'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * Validates URL accessibility
     */
    public function validateUrlAccessibility($attribute, $params)
    {
        $url = $this->$attribute;
        $parsed = parse_url($url);

        if (!isset($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'])) {
            $this->addError($attribute, 'URL должен использовать http или https');
            return;
        }

        if (!isset($parsed['host'])) {
            $this->addError($attribute, 'URL не содержит домен');
            return;
        }

        $ip = $this->resolveHost($parsed['host']);
        if ($ip === null) {
            $this->addError($attribute, 'Не удалось разрешить домен: ' . $parsed['host']);
            return;
        }

        if (!$this->isPublicIp($ip)) {
            $this->addError($attribute, 'Данный URL не доступен');
            return;
        }

        $port = ($parsed['scheme'] === 'https') ? 443 : 80;
        if (!$this->isTcpReachable($ip, $port)) {
            $this->addError($attribute, 'Данный URL не доступен');
            return;
        }

        if ($this->getHttpStatus($url) === 404) {
            $this->addError($attribute, 'URL возвращает ошибку 404 (страница не найдена)');
        }
    }

    private function resolveHost(string $host): ?string
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $host;
        }
        $ip = gethostbyname($host);
        return ($ip !== $host) ? $ip : null;
    }

    private function isPublicIp(string $ip): bool
    {
        return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    private function isTcpReachable(string $ip, int $port): bool
    {
        $connection = @fsockopen($ip, $port, $errno, $errstr, 5);
        if ($connection === false) {
            return false;
        }
        fclose($connection);
        return true;
    }

    private function getHttpStatus(string $url): int
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY        => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS     => 5,
            CURLOPT_TIMEOUT       => 10,
            CURLOPT_USERAGENT     => 'Mozilla/5.0',
        ]);
        curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $status;
    }

    /**
     * Generate unique short code
     */
    public static function generateShortCode()
    {
        do {
            // Generate 5-character random string from base58 alphabet
            $characters = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
            $code = '';
            for ($i = 0; $i < 5; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
            
            // Check if code exists
            $exists = self::find()->where(['short_code' => $code])->exists();
        } while ($exists);
        
        return $code;
    }

    /**
     * Increment click count and record IP
     */
    public function recordClick($ip, $userAgent = null)
    {
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Update link
            $this->click_count++;
            $this->ip_last = $ip;
            $this->save(false);
            
            // Log click
            $log = new ClickLog();
            $log->link_id = $this->id;
            $log->ip = $ip;
            $log->user_agent = $userAgent ? mb_substr($userAgent, 0, 255) : null;
            $log->save(false);
            
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * Get full short URL
     */
    public function getShortUrl()
    {
        return Url::to(['site/redirect', 'code' => $this->short_code], true);
    }
}
