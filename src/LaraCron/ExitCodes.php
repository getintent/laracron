<?php

namespace Trig\LaraCron;

class ExitCodes
{
    const OK = 0;
    const ERROR_GENERAL = 1;
    const ERROR_CONFIG_NOT_FOUND = 200;
    const ERROR_CONFIG_NOT_READABLE = 201;
    const ERROR_CONFIG_JSON_ERROR = 202;
    const ERROR_CMD_DEFINITION = 203;
    const ERROR_PHAR_READONLY = 204;
    const ERROR_CACHE_DIRECTORY_NOT_DEFINED = 205;
    const ERROR_REDIS_EXTENSION_NOT_INSTALLED = 206;
}
