[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2b29555d-8b58-4987-bc10-f06f80835a92/big.png)](https://insight.sensiolabs.com/projects/2b29555d-8b58-4987-bc10-f06f80835a92)

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/a58c62e4bde1485f95b52fa56a2e4320)](https://www.codacy.com/app/LEXASOFT/PHP-Censor-Telegram-Plugin)
# PHP-Censor-Telegram-Plugin
Telegram plugin for [PHP Censor](https://github.com/php-censor/php-censor)
# Installation
First of all - `composer require lexasoft/php-censor-telegram-plugin`

# Add to project
In the PHP Censor Project config section add the Telegram trigger
```
complete:
    telegram:
        api_key: "<YOUR_BOT_TOKEN_HERE>"
        message: "[%ICON_BUILD%] [%PROJECT_TITLE%](%PROJECT_URI%) - [Build #%BUILD%](%BUILD_URI%) has finished for commit [%SHORT_COMMIT% (%COMMIT_EMAIL%)](%COMMIT_URI%) on branch [%BRANCH%](%BRANCH_URI%)"
        recipients:
            - <user id>
            - "-<group id>"
            - "@<channel id>"
        send_log: true
```
