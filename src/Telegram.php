<?php
/**
 * PHPCI - Continuous Integration for PHP
 *
 * @copyright Copyright 2014, Block 8 Limited.
 * @license   https://github.com/Block8/PHPCI/blob/master/LICENSE.md
 * @link      https://www.phptesting.org/
 */

namespace PHPCensor\Plugin;

use PHPCensor\Builder;
use PHPCensor\Model\Build;
use b8\HttpClient;

/**
 * Telegram Plugin
 * @author     LEXASOFT <lexasoft83@gmail.com>
 * @package    PHPCensor
 * @subpackage Plugins
 */
class Telegram extends \PHPCensor\Plugin
{
    protected $apiKey;
    protected $message;
    protected $buildMsg;
    protected $recipients;
    protected $sendLog;

    /**
     * @return string
     */
    public static function pluginName()
    {
        return 'telegram';
    }

    /**
     * Standard Constructor
     *
     * @param Builder $builder
     * @param Build   $build
     * @param array   $options
     * @throws \Exception
     */
    public function __construct(Builder $builder, Build $build, array $options = [])
    {
        parent::__construct($builder, $build, $options);

        if (empty($options['api_key'])) {
            throw new \Exception("Not setting telegram api_key");
        }

        if (empty($options['recipients'])) {
            throw new \Exception("Not setting recipients");
        }

        $this->apiKey = $options['api_key'];
        $this->message = '[%ICON_BUILD%] [%PROJECT_TITLE%](%PROJECT_URI%)' .
            ' - [Build #%BUILD%](%BUILD_URI%) has finished ' .
            'for commit [%SHORT_COMMIT% (%COMMIT_EMAIL%)](%COMMIT_URI%) ' .
            'on branch [%BRANCH%](%BRANCH_URI%)';
        
        if (isset($options['message'])) {
            $this->message = $options['message'];
        }

        $this->recipients = [];
        if (is_string($options['recipients'])) {
            $this->recipients = [$options['recipients']];
        } elseif (is_array($options['recipients'])) {
            $this->recipients = $options['recipients'];
        }
        
        $this->sendLog = isset($options['send_log']) && ((bool) $options['send_log'] !== false);
    }

    /**
     * Run Telegram plugin.
     * @return bool
     */
    public function execute()
    {
        
        $message = $this->buildMessage();

        $http = new HttpClient('https://api.telegram.org');
        $http->setHeaders(['Content-Type: application/json']);
        $uri = '/bot'. $this->apiKey . '/sendMessage';

        foreach ($this->recipients as $chatId) {
            $params = [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ];

            $http->post($uri, json_encode($params));

            if ($this->sendLog) {
                $params = [
                    'chat_id' => $chatId,
                    'text' => $this->buildMsg,
                    'parse_mode' => 'Markdown',
                ];

                $http->post($uri, json_encode($params));
            }
        }

        return true;
    }

    /**
     * Build message.
     * @return string
     */
    private function buildMessage()
    {
        $this->buildMsg = '';
        $buildIcon = $this->build->isSuccessful() ? '✅' : '❎';
        $buildLog = $this->build->getLog();
        $buildLog = str_replace(['[0;32m', '[0;31m', '[0m', '/[0m'], '', $buildLog);
        $buildmessages = explode('RUNNING PLUGIN: ', $buildLog);

        foreach ($buildmessages as $bm) {
            $pos = mb_strpos($bm, "\n");
            $firstRow = mb_substr($bm, 0, $pos);

            //skip long outputs
            if (in_array($firstRow, ['slack_notify', 'php_loc', 'telegram'])) {
                continue;
            }

            $this->buildMsg .= '*RUNNING PLUGIN: ' . $firstRow . "*\n";
            $this->buildMsg .= $firstRow == 'composer' ? '' : ('```' . mb_substr($bm, $pos) . '```');
        }

        return $this->builder->interpolate(str_replace(['%ICON_BUILD%'], [$buildIcon], $this->message));
    }
}
