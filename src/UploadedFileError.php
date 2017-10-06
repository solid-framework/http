<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use Solid\Collection\Enum;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 */
class UploadedFileError extends Enum
{
    const UPLOAD_ERR_OK = UPLOAD_ERR_OK;
    const UPLOAD_ERR_INI_SIZE = UPLOAD_ERR_INI_SIZE;
    const UPLOAD_ERR_FORM_SIZE = UPLOAD_ERR_FORM_SIZE;
    const UPLOAD_ERR_PARTIAL = UPLOAD_ERR_PARTIAL;
    const UPLOAD_ERR_NO_FILE = UPLOAD_ERR_NO_FILE;
    const UPLOAD_ERR_NO_TMP_DIR = UPLOAD_ERR_NO_TMP_DIR;
    const UPLOAD_ERR_CANT_WRITE = UPLOAD_ERR_CANT_WRITE;
    const UPLOAD_ERR_EXTENSION = UPLOAD_ERR_EXTENSION;
}
